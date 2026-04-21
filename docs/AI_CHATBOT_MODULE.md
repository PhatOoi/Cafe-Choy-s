# AI Chatbot Module

Tai lieu nay mo ta module AI duoc tach rieng khoi web Laravel de tranh anh huong toi qua trinh phat trien cua nhom.

## Pham vi giai doan 1

- Huan luyen mo hinh phan loai intent cho chatbox tieng Viet.
- Trich xuat tri thuc menu tu chinh repo nay de lam nguon du lieu.
- Chua tich hop truc tiep vao route/controller hien co.
- Khong can thu vien AI native, mo hinh train bang Python standard library.
- Co them pipeline PyTorch rieng de train theo epoch/batch va co the dung GPU NVIDIA.

## Nguon du lieu

- Menu va option: `database/seeders/ProductSeeder.php`
- Mau lich su order: `database/seeders/OrderSeeder.php`
- Dataset hoi thoai tu tao: `ai/data/intents/train.jsonl`, `ai/data/intents/test.jsonl`
- Tham khao nguon GitHub cho hoi thoai: `ai/data/dialogue/github_sources.md`
- Menu dataset cho chatbot: `ai/data/menu/menu_catalog.json`
- Recommendation seed dataset: `ai/data/recommendation/order_history_seed.jsonl`
- FAQ seed dataset: `ai/data/faq/faq_seed.jsonl`

## Cach lam viec trong nhom

1. Nhom web tiep tuc sua Blade, controller, route nhu binh thuong.
2. Nhom AI lam viec ben trong thu muc `ai/` va chi doc du lieu tu repo.
3. Khi can tich hop, chi them mot lop giao tiep mong giua Laravel va module AI.

## Huong train GPU

Module AI hien co 2 cach train:

1. `ai/scripts/train_intent_model.py`: mo hinh Naive Bayes thuan Python, de chay o moi truong nao cung duoc.
2. `ai/scripts/train_intent_model_torch.py`: mo hinh BiLSTM PyTorch, train theo `epoch`, chia `batch` bang `DataLoader`, uu tien chay tren `cuda` neu co.

Huong PyTorch duoc tach rieng de khong pha vo pipeline hien tai va de phu hop yeu cau train GPU ve sau.

## Hop dong tich hop de xai sau

Input tu web:

```json
{
  "message": "hom nay troi nang qua toi muon uong gi do mat mat",
  "user_id": 4,
  "context": {
    "page": "menu",
    "cart_count": 0
  }
}
```

Output tu AI:

```json
{
  "intent": "ask_recommendation",
  "confidence": 0.91,
  "entities": {
    "weather": "hot",
    "temperature_preference": "cold"
  },
  "next_action": "recommend_menu"
}
```

## Giai doan tiep theo

1. Mo rong dataset intent len 150-300 cau de tang do on dinh.
2. Them extractor don gian cho entity nhu ngan sach, size, duong, da.
3. Tao mot service trung gian de web goi module AI ma khong phu thuoc vao code train.