from __future__ import annotations

import json
import random
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SRC_DIR = ROOT / "ai" / "src"
if str(SRC_DIR) not in sys.path:
    sys.path.insert(0, str(SRC_DIR))

from chatbot_ml.data import dump_jsonl, load_jsonl


random.seed(42)

AI_DIR = ROOT / "ai"
INTENT_DIR = AI_DIR / "data" / "intents"
DIALOGUE_DIR = AI_DIR / "data" / "dialogue"
FAQ_DIR = AI_DIR / "data" / "faq"
MENU_PATH = AI_DIR / "data" / "menu" / "menu_catalog.json"
DIALOGUE_TARGET = 10_000


def load_menu() -> dict:
    return json.loads(MENU_PATH.read_text(encoding="utf-8"))


def unique_rows(rows: list[dict], key: str) -> list[dict]:
    seen: set[str] = set()
    output: list[dict] = []
    for row in rows:
        value = row[key]
        if value in seen:
            continue
        seen.add(value)
        output.append(row)
    return output


def dialogue_signature(messages: list[dict]) -> str:
    return "||".join(f"{message['role']}::{message['text']}" for message in messages)


def unique_dialogues(rows: list[dict]) -> list[dict]:
    seen: set[str] = set()
    output: list[dict] = []
    for row in rows:
        signature = dialogue_signature(row["messages"])
        if signature in seen:
            continue
        seen.add(signature)
        output.append(row)
    return output


def assign_dialogue_ids(rows: list[dict]) -> list[dict]:
    output: list[dict] = []
    for index, row in enumerate(rows, start=1):
        output.append(
            {
                **row,
                "conversation_id": f"dlg-auto-{index:05d}",
            }
        )
    return output


def category_display_map(menu: dict) -> dict[str, str]:
    return {item["slug"]: item["name"] for item in menu["categories"]}


def all_products(menu: dict) -> list[dict]:
    rows: list[dict] = []
    for category in menu["categories"]:
        for product in category["products"]:
            rows.append({**product, "category_slug": category["slug"], "category_name": category["name"]})
    return rows


def generate_intent_rows(menu: dict) -> list[dict]:
    categories = category_display_map(menu)
    products = all_products(menu)
    sizes = [size["name"] for size in menu["sizes"]]
    toppings = [extra["name"] for extra in menu["extras"] if extra["type"] == "topping"]
    sugars = [extra["name"] for extra in menu["extras"] if extra["type"] == "sugar"]
    ices = [extra["name"] for extra in menu["extras"] if extra["type"] == "ice"]

    rows: list[dict] = []

    greeting_templates = [
        "xin chào shop",
        "hello quán ơi",
        "chào bạn nhé",
        "trợ lý ơi tư vấn giúp mình với",
        "ad ơi hỏi tí",
        "shop ơi mình cần hỗ trợ đặt nước",
        "hi choys cafe",
        "cho mình hỏi chút được không",
    ]
    rows.extend({"text": text, "intent": "greeting"} for text in greeting_templates)

    menu_templates = [
        "cho mình xem menu {category}",
        "quán có những món {category} nào",
        "gửi mình danh sách {category}",
        "menu {category} của quán gồm gì",
        "cho xem các món bên nhóm {category}",
        "có bán {category} không",
    ]
    for category_name in categories.values():
        for template in menu_templates:
            rows.append({"text": template.format(category=category_name.lower()), "intent": "ask_menu"})

    recommend_templates = [
        "hôm nay trời nóng quá gợi ý mình món {style}",
        "mình muốn uống gì đó {style}",
        "có món nào {style} dễ uống không",
        "gợi ý đồ uống {style} cho mình với",
        "nếu mình thích {style} thì nên chọn món nào",
    ]
    recommend_styles = [
        "mát mát",
        "thanh mát",
        "ít ngọt",
        "dễ uống",
        "không có cà phê",
        "giải nhiệt",
        "nhạt cho buổi chiều",
    ]
    for template in recommend_templates:
        for style in recommend_styles:
            rows.append({"text": template.format(style=style), "intent": "ask_recommendation"})
    for category_name in categories.values():
        rows.append({"text": f"gợi ý cho mình một món ngon bên nhóm {category_name.lower()}", "intent": "ask_recommendation"})

    price_templates = [
        "có món nào dưới {price} không",
        "tìm giúp mình đồ uống tầm {price}",
        "mình có {price} thì nên chọn gì",
        "quán có món nào giá khoảng {price} không",
        "cho mình món ngon trong tầm giá {price}",
    ]
    budgets = ["25k", "30k", "35k", "40k", "45k", "50k"]
    for template in price_templates:
        for budget in budgets:
            rows.append({"text": template.format(price=budget), "intent": "ask_price_filter"})

    product_info_templates = [
        "{product} có dễ uống không",
        "món {product} vị như thế nào",
        "{product} có ngọt quá không",
        "{product} có hợp người sợ béo không",
        "{product} có phải món bán chạy không",
    ]
    for product in products:
        for template in product_info_templates:
            rows.append({"text": template.format(product=product["name"].lower()), "intent": "ask_product_info"})

    repeat_templates = [
        "đặt lại món cũ cho mình",
        "gọi lại order hôm trước đi",
        "lặp lại món mình hay mua nhé",
        "đặt lại đồ uống thường xuyên của tôi",
        "mình muốn mua lại món lần trước",
    ]
    rows.extend({"text": text, "intent": "repeat_order"} for text in repeat_templates)

    add_templates = [
        "thêm {qty} {product} vào giỏ hàng",
        "cho mình {qty} ly {product}",
        "đặt {qty} phần {product} nhé",
        "chốt {qty} món {product}",
        "lấy giúp mình {qty} {product}",
    ]
    quantities = ["1", "2", "3", "một", "hai"]
    for product in products:
        for template in add_templates:
            for quantity in quantities:
                rows.append({"text": template.format(qty=quantity, product=product["name"].lower()), "intent": "add_to_cart"})

    customize_templates = [
        "cho size {size} {sugar}",
        "size {size} và {ice} nhé",
        "thêm {topping} cho món này",
        "mình muốn {sugar} với {ice}",
        "ly này chọn size {size} và thêm {topping}",
    ]
    for size in sizes:
        for sugar in sugars[:4]:
            rows.append({"text": f"cho size {size.lower()} {sugar.lower()}", "intent": "customize_order"})
    for size in sizes:
        for ice in ices:
            rows.append({"text": f"size {size.lower()} và {ice.lower()} nhé", "intent": "customize_order"})
    for topping in toppings:
        rows.append({"text": f"thêm {topping.lower()} cho mình", "intent": "customize_order"})
    for template in customize_templates:
        for size in sizes:
            for topping in toppings[:3]:
                rows.append({
                    "text": template.format(
                        size=size.lower(),
                        sugar=random.choice(sugars).lower(),
                        ice=random.choice(ices).lower(),
                        topping=topping.lower(),
                    ),
                    "intent": "customize_order",
                })

    seed_rows = load_jsonl(INTENT_DIR / "train.jsonl")
    rows.extend(seed_rows)
    rows = unique_rows(rows, "text")
    random.shuffle(rows)
    return rows


def generate_dialogues(menu: dict, intent_rows: list[dict]) -> list[dict]:
    categories = category_display_map(menu)
    products = all_products(menu)
    intent_groups: dict[str, list[str]] = {}
    for row in intent_rows:
        intent_groups.setdefault(row["intent"], []).append(row["text"])

    sizes = [size["name"] for size in menu["sizes"]]
    toppings = [extra["name"] for extra in menu["extras"] if extra["type"] == "topping"]
    sugars = [extra["name"] for extra in menu["extras"] if extra["type"] == "sugar"]
    ices = [extra["name"] for extra in menu["extras"] if extra["type"] == "ice"]
    category_items = list(categories.items())
    product_names = [product["name"] for product in products]

    styles = [
        "mát mát",
        "ít ngọt",
        "dễ uống",
        "giải nhiệt",
        "không có cà phê",
        "thơm trái cây",
        "cho buổi sáng",
        "cho buổi chiều",
    ]
    budgets = [25000, 30000, 35000, 40000, 45000, 50000]
    openings = [
        "xin chào shop",
        "bạn ơi",
        "cho mình hỏi với",
        "helo quán",
        "trợ lý ơi",
    ]
    closings = [
        "nhé",
        "giúp mình với",
        "được không",
        "cảm ơn nha",
        "nha",
    ]
    relation_phrases = [
        "món nào đang được gọi nhiều",
        "món nào dễ uống nhất",
        "món nào hợp người mới",
        "có món nào nên thử",
    ]

    def assistant_product_summary(product: dict) -> str:
        tags = product.get("ai_tags", [])
        tag_text = ", ".join(tags[:2]) if tags else "de uong"
        return (
            f"{product['name']} thuộc nhóm {product['category_name']}, giá {product['price']} đồng, "
            f"phong cách {tag_text}. Nếu bạn muốn, mình có thể gợi ý size và option phù hợp."
        )

    def build_dialogue(messages: list[dict], primary_intent: str, source: str = "synthetic_choys_cafe") -> dict:
        return {
            "source": source,
            "messages": messages,
            "primary_intent": primary_intent,
        }

    responses = {
        "greeting": [
            "Xin chào, mình là trợ lý Choy's Cafe. Mình có thể gợi ý món, xem menu và hỗ trợ đặt hàng cho bạn.",
            "Chào bạn, mình có thể giúp bạn xem menu hoặc chọn món hợp gu ngay bây giờ.",
        ],
        "ask_menu": [
            f"Quán hiện có các nhóm món như {', '.join(categories.values())}. Bạn muốn xem nhóm nào trước?",
            "Mình có thể gửi menu theo từng nhóm như cà phê, trà sữa, nước ép, đá xay hoặc món theo mùa.",
        ],
        "ask_recommendation": [
            "Nếu bạn muốn món dễ uống và hợp thời tiết nóng, mình có thể gợi ý Peach Tea, Nước Ép Dưa Hấu hoặc Trà Trái Cây Nhiệt Đới.",
            "Mình sẽ gợi ý theo nhu cầu của bạn, ví dụ thanh mát, ít ngọt hoặc không có cà phê.",
        ],
        "ask_price_filter": [
            "Mình có thể lọc món theo ngân sách để bạn chọn nhanh hơn.",
            "Bạn cứ cho mình mức giá, mình sẽ gợi ý các món phù hợp trong tầm đó.",
        ],
        "ask_product_info": [
            "Mình có thể mô tả vị, độ ngọt và độ dễ uống của món đó cho bạn.",
            "Nếu bạn muốn, mình sẽ nói thêm món này hợp gu nào và nên chọn size hay topping gì.",
        ],
        "repeat_order": [
            "Mình có thể hỗ trợ đặt lại món cũ khi đã có lịch sử mua hàng của tài khoản bạn.",
            "Nếu bạn đã từng đặt trước đó, mình sẽ ưu tiên gợi ý lại các món bạn hay mua.",
        ],
        "add_to_cart": [
            "Mình đã ghi nhận món bạn chọn. Bạn có muốn chọn thêm size, đường, đá hoặc topping không?",
            "Món này có thể thêm vào giỏ ngay. Nếu cần mình sẽ hỗ trợ tùy chỉnh trước khi chốt.",
        ],
        "customize_order": [
            "Mình đã hiểu phần tùy chỉnh của bạn và sẽ cập nhật vào món đang chọn.",
            "Ok, mình sẽ ghi nhận size và option bạn vừa yêu cầu.",
        ],
    }

    dialogues: list[dict] = []
    for intent, utterances in intent_groups.items():
        for utterance in utterances:
            assistant_text = random.choice(responses[intent])
            dialogues.append(build_dialogue([
                {"role": "user", "text": utterance},
                {"role": "assistant", "text": assistant_text},
            ], intent))

    for product in products:
        dialogues.append(
            build_dialogue(
                [
                    {"role": "user", "text": f"{product['name']} có ngon không"},
                    {"role": "assistant", "text": assistant_product_summary(product)},
                ],
                "ask_product_info",
            )
        )

    for opening in openings:
        for category_slug, category_name in category_items:
            category_products = [product for product in products if product["category_slug"] == category_slug]
            featured = category_products[:3]
            preview_text = ", ".join(product["name"] for product in featured)
            answer = (
                f"Nhóm {category_name} hiện có {preview_text}. "
                f"Bạn muốn mình gợi ý thêm theo vị dễ uống, ít ngọt hay theo ngân sách không?"
            )
            question = f"{opening} cho mình xem menu {category_name.lower()}"
            dialogues.append(
                build_dialogue(
                    [
                        {"role": "user", "text": question},
                        {"role": "assistant", "text": answer},
                    ],
                    "ask_menu",
                )
            )

    for category_slug, category_name in category_items:
        category_products = [product for product in products if product["category_slug"] == category_slug]
        preview = ", ".join(product["name"] for product in category_products[:3])
        for style in styles:
            for phrase in relation_phrases:
                recommended = random.choice(category_products)
                dialogues.append(
                    build_dialogue(
                        [
                            {"role": "user", "text": f"cho mình xem menu {category_name.lower()}"},
                            {"role": "assistant", "text": f"Nhóm {category_name} có các món như {preview}. Bạn đang ưu tiên vị nào?"},
                            {"role": "user", "text": f"mình muốn {style}, {phrase} trong nhóm này"},
                            {"role": "assistant", "text": f"Nếu bạn ưu tiên {style} thì mình gợi ý {recommended['name']}. Món này giá {recommended['price']} đồng và thuộc nhóm {category_name}."},
                        ],
                        "ask_recommendation",
                    )
                )

    for product in products:
        related = [item for item in products if item["category_slug"] == product["category_slug"] and item["name"] != product["name"]]
        fallback = related[0] if related else product
        for budget in budgets:
            result_text = (
                f"{product['name']} giá {product['price']} đồng nên "
                + ("vẫn trong tầm ngân sách của bạn." if product["price"] <= budget else f"hơi vượt mức {budget} đồng. Bạn có thể cân nhắc {fallback['name']} giá {fallback['price']} đồng.")
            )
            dialogues.append(
                build_dialogue(
                    [
                        {"role": "user", "text": f"mình có tầm {budget} đồng, {product['name']} có hợp không"},
                        {"role": "assistant", "text": result_text},
                    ],
                    "ask_price_filter",
                )
            )

    for product in products:
        for qty in ["1", "2"]:
            for size in sizes:
                for sugar in sugars:
                    for ice in ices:
                        for topping in toppings:
                            for closing in closings:
                                dialogues.append(
                                    build_dialogue(
                                        [
                                            {"role": "user", "text": f"cho mình {qty} {product['name'].lower()} {closing}"},
                                            {"role": "assistant", "text": f"Mình đã ghi nhận {qty} {product['name']}. Bạn muốn chọn thêm size, đường, đá hay topping nào?"},
                                            {"role": "user", "text": f"size {size.lower()}, {sugar.lower()}, {ice.lower()} và thêm {topping.lower()}"},
                                            {"role": "assistant", "text": f"Ok, mình đã cập nhật {product['name']} size {size}, {sugar.lower()}, {ice.lower()} và thêm {topping}."},
                                        ],
                                        "add_to_cart",
                                    )
                                )

    for product in products:
        style_tags = product.get("ai_tags", [])
        style_text = style_tags[0] if style_tags else "de uong"
        for opening in openings:
            for closing in closings:
                dialogues.append(
                    build_dialogue(
                        [
                            {"role": "user", "text": f"{opening} {product['name'].lower()} vị như thế nào {closing}"},
                            {"role": "assistant", "text": assistant_product_summary(product)},
                            {"role": "user", "text": f"nếu mình thích món {style_text} thì món này hợp không"},
                            {"role": "assistant", "text": f"Món này khá hợp nếu bạn thích phong cách {style_text}. Nếu cần mình có thể gợi ý thêm 1 món cùng nhóm để bạn so sánh."},
                        ],
                        "ask_product_info",
                    )
                )

    for style in styles:
        for category_slug, category_name in category_items:
            category_products = [product for product in products if product["category_slug"] == category_slug]
            best_fit = random.choice(category_products)
            size = random.choice(sizes)
            sugar = random.choice(sugars)
            ice = random.choice(ices)
            dialogues.append(
                build_dialogue(
                    [
                        {"role": "user", "text": f"hôm nay mình muốn món {style} trong nhóm {category_name.lower()}"},
                        {"role": "assistant", "text": f"Trong nhóm {category_name}, mình gợi ý {best_fit['name']} giá {best_fit['price']} đồng. Bạn có muốn mình thêm vào giỏ luôn không?"},
                        {"role": "user", "text": f"ok thêm 1 ly và chọn size {size.lower()}, {sugar.lower()}, {ice.lower()}"},
                        {"role": "assistant", "text": f"Mình đã ghi nhận 1 {best_fit['name']} size {size}, {sugar.lower()} và {ice.lower()}."},
                    ],
                    "ask_recommendation",
                )
            )

    seed_dialogues = load_jsonl(DIALOGUE_DIR / "dialogue_seed.jsonl")
    for row in seed_dialogues:
        dialogues.append(
            build_dialogue(
                row["messages"],
                row["primary_intent"],
                row.get("source", "adapted_for_choys_cafe"),
            )
        )

    dialogues = unique_dialogues(dialogues)

    if len(dialogues) < DIALOGUE_TARGET:
        raise SystemExit(
            f"Only generated {len(dialogues)} dialogue rows, expected at least {DIALOGUE_TARGET}."
        )

    random.shuffle(dialogues)
    dialogues = dialogues[:DIALOGUE_TARGET]
    return assign_dialogue_ids(dialogues)


def generate_faq_paraphrases() -> list[dict]:
    faq_rows = load_jsonl(FAQ_DIR / "faq_seed.jsonl")
    paraphrase_templates = {
        "store_location": ["địa chỉ quán ở đâu", "quán nằm chỗ nào vậy", "mình muốn biết vị trí quán"],
        "opening_hours": ["quán bắt đầu bán lúc mấy giờ", "mấy giờ quán mở", "sáng mấy giờ quán hoạt động"],
        "closing_hours": ["quán nghỉ lúc mấy giờ", "tối mấy giờ đóng cửa", "mấy giờ hết bán"],
        "contact_phone": ["cho mình xin số điện thoại quán", "quán có hotline không", "liên hệ quán bằng số nào"],
        "parking": ["có chỗ để xe không", "mình gửi xe ở đâu", "đi xe máy tới có chỗ gửi không"],
        "delivery_support": ["quán có giao tận nơi không", "có ship không", "mình đặt giao hàng được không"],
        "payment_methods": ["thanh toán bằng momo được không", "quán nhận chuyển khoản không", "có quét qr không"],
        "online_ordering": ["đặt món trên web được không", "mình order online được chứ", "có thể mua online không"],
        "customization": ["có chọn size được không", "cho thêm topping được không", "món có chỉnh đường đá được không"],
        "best_seller": ["món nào bán chạy nhất", "quán nổi bật món gì", "nên uống món nào của quán"],
        "wifi": ["có wifi không bạn", "xin mật khẩu wifi được không", "quán có mạng cho khách không"],
        "invoice": ["quán có xuất hóa đơn không", "mình cần hóa đơn thì sao", "thanh toán xong có hóa đơn không"],
    }

    rows: list[dict] = []
    for row in faq_rows:
        rows.append(row)
        for variant in paraphrase_templates.get(row["intent"], []):
            rows.append(
                {
                    "faq_id": row["faq_id"],
                    "intent": row["intent"],
                    "question": variant,
                    "answer": row["answer"],
                    "needs_manual_review": row["needs_manual_review"],
                }
            )
    return rows


def main() -> None:
    if not MENU_PATH.exists():
        raise SystemExit("Missing ai/data/menu/menu_catalog.json. Run export_menu_dataset.py first.")

    menu = load_menu()
    intent_rows = generate_intent_rows(menu)
    dialogue_rows = generate_dialogues(menu, intent_rows)
    faq_rows = generate_faq_paraphrases()

    train_split = int(len(intent_rows) * 0.9)
    train_rows = intent_rows[:train_split]
    test_rows = intent_rows[train_split:]

    dump_jsonl(INTENT_DIR / "train_expanded.jsonl", train_rows)
    dump_jsonl(INTENT_DIR / "test_expanded.jsonl", test_rows)
    dump_jsonl(DIALOGUE_DIR / "dialogue_expanded.jsonl", dialogue_rows)
    dump_jsonl(FAQ_DIR / "faq_expanded.jsonl", faq_rows)

    print(
        f"Generated {len(train_rows)} train intents, {len(test_rows)} test intents, "
        f"{len(dialogue_rows)} dialogues and {len(faq_rows)} FAQ rows."
    )


if __name__ == "__main__":
    main()