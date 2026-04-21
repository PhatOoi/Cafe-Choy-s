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
        "xin chao shop",
        "hello quan oi",
        "chao ban nhe",
        "tro ly oi tu van giup minh voi",
        "ad oi hoi ti",
        "shop oi minh can ho tro dat nuoc",
        "hi choys cafe",
        "cho minh hoi chut duoc khong",
    ]
    rows.extend({"text": text, "intent": "greeting"} for text in greeting_templates)

    menu_templates = [
        "cho minh xem menu {category}",
        "quan co nhung mon {category} nao",
        "gui minh danh sach {category}",
        "menu {category} cua quan gom gi",
        "cho xem cac mon ben nhom {category}",
        "co ban {category} khong",
    ]
    for category_name in categories.values():
        for template in menu_templates:
            rows.append({"text": template.format(category=category_name.lower()), "intent": "ask_menu"})

    recommend_templates = [
        "hom nay troi nong qua goi y minh mon {style}",
        "minh muon uong gi do {style}",
        "co mon nao {style} de uong khong",
        "goi y do uong {style} cho minh voi",
        "neu minh thich {style} thi nen chon mon nao",
    ]
    recommend_styles = [
        "mat mat",
        "thanh mat",
        "it ngot",
        "de uong",
        "khong co ca phe",
        "giai nhiet",
        "nhat cho buoi chieu",
    ]
    for template in recommend_templates:
        for style in recommend_styles:
            rows.append({"text": template.format(style=style), "intent": "ask_recommendation"})
    for category_name in categories.values():
        rows.append({"text": f"goi y cho minh mot mon ngon ben nhom {category_name.lower()}", "intent": "ask_recommendation"})

    price_templates = [
        "co mon nao duoi {price} khong",
        "tim giup minh do uong tam {price}",
        "minh co {price} thi nen chon gi",
        "quan co mon nao gia khoang {price} khong",
        "cho minh mon ngon trong tam gia {price}",
    ]
    budgets = ["25k", "30k", "35k", "40k", "45k", "50k"]
    for template in price_templates:
        for budget in budgets:
            rows.append({"text": template.format(price=budget), "intent": "ask_price_filter"})

    product_info_templates = [
        "{product} co de uong khong",
        "mon {product} vi nhu the nao",
        "{product} co ngot qua khong",
        "{product} co hop nguoi so beo khong",
        "{product} co phai mon ban chay khong",
    ]
    for product in products:
        for template in product_info_templates:
            rows.append({"text": template.format(product=product["name"].lower()), "intent": "ask_product_info"})

    repeat_templates = [
        "dat lai mon cu cho minh",
        "goi lai order hom truoc di",
        "lap lai mon minh hay mua nhe",
        "dat lai do uong thuong xuyen cua toi",
        "minh muon mua lai mon lan truoc",
    ]
    rows.extend({"text": text, "intent": "repeat_order"} for text in repeat_templates)

    add_templates = [
        "them {qty} {product} vao gio hang",
        "cho minh {qty} ly {product}",
        "dat {qty} phan {product} nhe",
        "chot {qty} mon {product}",
        "lay giup minh {qty} {product}",
    ]
    quantities = ["1", "2", "3", "một", "hai"]
    for product in products:
        for template in add_templates:
            for quantity in quantities:
                rows.append({"text": template.format(qty=quantity, product=product["name"].lower()), "intent": "add_to_cart"})

    customize_templates = [
        "cho size {size} {sugar}",
        "size {size} va {ice} nhe",
        "them {topping} cho mon nay",
        "minh muon {sugar} voi {ice}",
        "ly nay chon size {size} va them {topping}",
    ]
    for size in sizes:
        for sugar in sugars[:4]:
            rows.append({"text": f"cho size {size.lower()} {sugar.lower()}", "intent": "customize_order"})
    for size in sizes:
        for ice in ices:
            rows.append({"text": f"size {size.lower()} va {ice.lower()} nhe", "intent": "customize_order"})
    for topping in toppings:
        rows.append({"text": f"them {topping.lower()} cho minh", "intent": "customize_order"})
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
    index = 1
    for intent, utterances in intent_groups.items():
        sample_size = min(80, len(utterances))
        for utterance in utterances[:sample_size]:
            assistant_text = random.choice(responses[intent])
            dialogues.append(
                {
                    "conversation_id": f"dlg-auto-{index:04d}",
                    "source": "synthetic_choys_cafe",
                    "messages": [
                        {"role": "user", "text": utterance},
                        {"role": "assistant", "text": assistant_text},
                    ],
                    "primary_intent": intent,
                }
            )
            index += 1

    for product in random.sample(products, min(20, len(products))):
        dialogues.append(
            {
                "conversation_id": f"dlg-auto-{index:04d}",
                "source": "synthetic_choys_cafe",
                "messages": [
                    {"role": "user", "text": f"{product['name']} có ngon không"},
                    {"role": "assistant", "text": f"{product['name']} thuộc nhóm {product['category_name']}, giá hiện tại là {product['price']} đồng. Nếu bạn muốn, mình có thể gợi ý thêm món tương tự."},
                ],
                "primary_intent": "ask_product_info",
            }
        )
        index += 1

    seed_dialogues = load_jsonl(DIALOGUE_DIR / "dialogue_seed.jsonl")
    dialogues.extend(seed_dialogues)
    return unique_rows(dialogues, "conversation_id")


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