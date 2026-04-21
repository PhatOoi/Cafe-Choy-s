# AI Dataset Layout

Thu muc nay chua cac dataset nguon cho module AI, duoc chia theo 4 nhom phu hop voi chatbot ho tro khach hang.

## Nhom dataset

- `dialogue/`: du lieu hoi thoai va ghi chu nguon GitHub de tham khao
- `menu/`: menu duoc chuan hoa tu du lieu quan
- `recommendation/`: lich su order mau de xay dung logic goi y mon
- `faq/`: bo FAQ mau cho cac cau hoi van hanh cua quan

## Nguyen tac su dung

1. `dialogue` dung de train intent va lam mau hoi thoai.
2. `menu` la nguon tri thuc domain, uu tien giu dong bo voi `database/seeders/ProductSeeder.php`.
3. `recommendation` dung de thu nghiem logic goi y dua tren lich su khach hang.
4. `faq` la tap mau, can duoc bo sung bang thong tin that cua quan truoc khi demo.

## Ghi chu

- Cac nguon GitHub trong `dialogue/github_sources.md` la nguon tham khao. Ban can kiem tra license truoc khi copy truc tiep.
- Cac file `menu` va `recommendation` duoc viet theo domain cua Choy's Cafe de chatbot tra loi dung ngu canh hon.