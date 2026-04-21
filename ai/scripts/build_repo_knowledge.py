from __future__ import annotations

import re
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SRC_DIR = ROOT / "ai" / "src"
if str(SRC_DIR) not in sys.path:
    sys.path.insert(0, str(SRC_DIR))

from chatbot_ml.data import dump_json

PRODUCT_SEEDER = ROOT / "database" / "seeders" / "ProductSeeder.php"
ORDER_SEEDER = ROOT / "database" / "seeders" / "OrderSeeder.php"
ARTIFACTS_DIR = ROOT / "ai" / "artifacts"


def extract_categories(text: str) -> dict[str, dict]:
    pattern = re.compile(
        r"\['name' => '([^']+)', 'slug' => '([^']+)', 'sort_order' => (\d+)\]"
    )
    categories: dict[str, dict] = {}
    for name, slug, sort_order in pattern.findall(text):
        categories[slug] = {
            "name": name,
            "slug": slug,
            "sort_order": int(sort_order),
        }
    return categories


def extract_products(text: str) -> list[dict]:
    pattern = re.compile(
        r"\['category_id' => \$categoryIds\['([^']+)'\], 'name' => '([^']+)', 'description' => '([^']*)', 'price' => ([0-9]+), 'stock' => ([0-9]+), 'status' => '([^']+)', 'image_url' => '([^']+)', 'created_at' => now\(\)\]"
    )
    products = []
    for slug, name, description, price, stock, status, image_url in pattern.findall(text):
        products.append(
            {
                "category_slug": slug,
                "name": name,
                "description": description,
                "price": int(price),
                "stock": int(stock),
                "status": status,
                "image_url": image_url,
            }
        )
    return products


def extract_extras(text: str) -> list[dict]:
    pattern = re.compile(r"\['name' => '([^']+)', 'price' => ([0-9]+), 'type' => '([^']+)'\]")
    extras = []
    for name, price, extra_type in pattern.findall(text):
        extras.append({"name": name, "price": int(price), "type": extra_type})
    return extras


def extract_order_items(text: str) -> list[dict]:
    pattern = re.compile(
        r"\['order_id' => ([0-9]+),\s*'product_id' => ([0-9]+),\s*'quantity' => ([0-9]+), 'unit_price' => ([0-9]+), 'note' => (null|'[^']*')\]"
    )
    items = []
    for order_id, product_id, quantity, unit_price, note in pattern.findall(text):
        items.append(
            {
                "order_id": int(order_id),
                "product_id": int(product_id),
                "quantity": int(quantity),
                "unit_price": int(unit_price),
                "note": None if note == "null" else note.strip("'"),
            }
        )
    return items


def main() -> None:
    product_text = PRODUCT_SEEDER.read_text(encoding="utf-8")
    order_text = ORDER_SEEDER.read_text(encoding="utf-8")

    categories = extract_categories(product_text)
    products = extract_products(product_text)
    extras = extract_extras(product_text)
    order_items = extract_order_items(order_text)

    menu_payload = {
        "source": str(PRODUCT_SEEDER.relative_to(ROOT)).replace("\\", "/"),
        "categories": list(categories.values()),
        "products": products,
        "extras": extras,
    }
    order_payload = {
        "source": str(ORDER_SEEDER.relative_to(ROOT)).replace("\\", "/"),
        "order_items": order_items,
    }

    dump_json(ARTIFACTS_DIR / "menu_knowledge.json", menu_payload)
    dump_json(ARTIFACTS_DIR / "order_seed_snapshot.json", order_payload)

    print(f"Exported {len(products)} products, {len(extras)} extras and {len(order_items)} order items.")


if __name__ == "__main__":
    main()