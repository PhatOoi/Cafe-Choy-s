# AI Chatbox Module

Module nay duoc tach rieng khoi Laravel de nhom co the huan luyen, danh gia va cai tien chatbot ma khong anh huong truc tiep den web dang phat trien.

## Muc tieu

- Huan luyen mo hinh phan loai y dinh tieng Viet cho chatbox ho tro khach hang.
- Trich xuat tri thuc menu tu chinh repo nay thay vi hard-code ben ngoai.
- Tao dau vao on dinh de sau nay web Laravel chi can goi module AI qua API hoac CLI.

## Cau truc

```text
ai/
  artifacts/                 # File sinh ra khi train/xuat tri thuc
  data/
    intents/
      train.jsonl           # Dataset train tu tao
      test.jsonl            # Dataset test tu tao
      labels.json           # Mo ta nhan va entity can trich xuat
    dialogue/
      github_sources.md     # Nguon GitHub tham khao cho du lieu hoi thoai
      dialogue_seed.jsonl   # Hoi thoai mau theo domain quan cafe
    menu/
      menu_catalog.json     # Menu chuan hoa tu du lieu quan
    recommendation/
      order_history_seed.jsonl # Lich su order mau phuc vu goi y mon
    faq/
      faq_seed.jsonl        # FAQ mau de ban tu viet them
  scripts/
    build_repo_knowledge.py # Doc seeders trong repo de tao menu snapshot
    export_menu_dataset.py  # Xuat dataset menu tu tri thuc da trich xuat
    generate_synthetic_datasets.py # Sinh dataset mo rong tu menu va FAQ
    train_intent_model.py   # Train model phan loai intent
    predict_intent.py       # Thu nghiem du doan tu command line
  src/chatbot_ml/
    data.py                 # Doc/ghi dataset JSONL
    model.py                # Build pipeline va train/evaluate
  requirements.txt          # Hien tai khong can thu vien ngoai
```

## Cac intent ban dau

- `greeting`: chao hoi, mo dau hoi thoai
- `ask_menu`: hoi menu, danh muc, mon dang co
- `ask_recommendation`: nho goi y mon theo nhu cau
- `ask_price_filter`: tim mon theo ngan sach
- `ask_product_info`: hoi thong tin ve mot nhom mon cu the
- `repeat_order`: goi lai mon cu, dua tren lich su mua
- `add_to_cart`: chot mon, them vao gio
- `customize_order`: chon size, duong, da, topping

## Cach dung

Tao virtual environment rieng trong thu muc `ai`:

```powershell
cd ai
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

Neu `requirements.txt` dang de trong hoac chi chua comment thi ban co the bo qua buoc cai thu vien.

Sinh menu snapshot tu repo:

```powershell
python scripts/build_repo_knowledge.py
python scripts/export_menu_dataset.py
python scripts/generate_synthetic_datasets.py
```

Sau khi da co file `train_expanded.jsonl` va `test_expanded.jsonl`, script train se uu tien dung bo du lieu mo rong nay.

Train model intent:

```powershell
python scripts/train_intent_model.py
```

Thu nghiem nhanh:

```powershell
python scripts/predict_intent.py "hom nay troi nang qua toi muon uong gi do mat mat"
```

## Ket qua sau khi train

- `artifacts/menu_knowledge.json`: menu va option trich xuat tu `database/seeders/ProductSeeder.php`
- `artifacts/order_seed_snapshot.json`: mau lich su don tu `database/seeders/OrderSeeder.php`
- `artifacts/intent_model.json`: model da train
- `artifacts/intent_metrics.json`: tong hop chi so danh gia

## Cach tich hop ve sau

Giai doan hien tai module AI chay doc lap. Khi can tich hop vao web:

1. Laravel goi script Python qua process hoac API rieng.
2. Web gui cau chat cua khach va thong tin user hien tai.
3. Module AI tra ve `intent`, `confidence`, `entities` de web xu ly.
4. Laravel tiep tuc dung logic san co de lay menu, lich su don va them vao gio hang.

Huong nay giup nhom web co the sua Blade, controller, route ma khong lam vo pipeline train.