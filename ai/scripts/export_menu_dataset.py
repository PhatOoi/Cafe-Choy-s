from __future__ import annotations

import json
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
ARTIFACTS_DIR = ROOT / "ai" / "artifacts"
OUTPUT_PATH = ROOT / "ai" / "data" / "menu" / "menu_catalog.json"


def option_rules(category_slug: str, product_name: str) -> dict:
    normalized_name = product_name.lower()
    rules = {
        "size": True,
        "topping": True,
        "sugar": True,
        "ice": True,
    }

    if category_slug == "tra-sua":
        rules["sugar"] = False
    elif category_slug == "da-xay":
        rules["topping"] = False
        rules["sugar"] = False
        rules["ice"] = False
    elif category_slug == "nuoc-ep":
        rules["topping"] = False
        rules["sugar"] = False
        if "sinh tố" in normalized_name or "sinh to" in normalized_name:
            rules["ice"] = False
    elif category_slug == "ca-phe":
        rules["topping"] = False
    elif category_slug == "tra-va-thuc-uong-theo-mua":
        rules["topping"] = False
        rules["sugar"] = False
    elif category_slug == "banh-snack":
        rules = {"size": False, "topping": False, "sugar": False, "ice": False}

    return rules


def tags_for_product(category_slug: str, product_name: str, description: str) -> list[str]:
    name = product_name.lower()
    desc = description.lower()
    tags: list[str] = []

    if category_slug in {"nuoc-ep", "tra-va-thuc-uong-theo-mua"}:
        tags.extend(["refreshing", "cooling"])
    if category_slug == "ca-phe":
        tags.extend(["caffeine", "morning"])
    if category_slug == "tra-sua":
        tags.extend(["sweet", "milk-tea"])
    if category_slug == "da-xay":
        tags.extend(["blended", "dessert-drink"])
    if category_slug == "banh-snack":
        tags.extend(["snack", "food"])
    if "thanh mát" in desc or "mát" in desc or "tươi" in desc:
        tags.append("refreshing")
    if "béo" in desc or "sữa" in desc:
        tags.append("creamy")
    if "cà phê" in desc or "ca phe" in name:
        tags.append("strong")
    return sorted(set(tags))


def main() -> None:
    source_path = ARTIFACTS_DIR / "menu_knowledge.json"
    if not source_path.exists():
        raise SystemExit("Missing ai/artifacts/menu_knowledge.json. Run python ai/scripts/build_repo_knowledge.py first.")

    payload = json.loads(source_path.read_text(encoding="utf-8"))
    categories = {item["slug"]: {**item, "products": []} for item in payload["categories"]}
    extras = payload.get("extras", [])
    sizes = [
        {"name": "S", "extra_price": 0},
        {"name": "M", "extra_price": 10000},
        {"name": "L", "extra_price": 15000},
    ]

    for product in payload["products"]:
        category_slug = product["category_slug"]
        categories[category_slug]["products"].append(
            {
                "name": product["name"],
                "description": product["description"],
                "price": product["price"],
                "status": product["status"],
                "image_url": product["image_url"],
                "option_rules": option_rules(category_slug, product["name"]),
                "ai_tags": tags_for_product(category_slug, product["name"], product["description"]),
            }
        )

    output = {
        "source": payload["source"],
        "sizes": sizes,
        "extras": extras,
        "categories": sorted(categories.values(), key=lambda item: item["sort_order"]),
    }

    OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_PATH.write_text(json.dumps(output, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Exported menu dataset to {OUTPUT_PATH.as_posix()}")


if __name__ == "__main__":
    main()