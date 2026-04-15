# Ban Do Chuc Nang File

Tai lieu nay mo ta cong dung cua cac file chinh trong du an Cafe Choy's.

Pham vi:
- Bao gom file nguon, file cau hinh, Blade view, migration, seeder, test, asset tuy bien do du an su dung truc tiep.
- Khong liet ke tung file trong `vendor/`, `node_modules/`, `storage/`, `bootstrap/cache/` vi do la dependency hoac file sinh tu dong.
- Cac asset thu vien ben thu ba trong `public/css` va `public/js` duoc gom nhom khi khong can tach tung file de doc code du an.

## Thu muc goc

| File | Cong dung |
| --- | --- |
| `.editorconfig` | Quy dinh style can ban cho editor de giu dinh dang file dong nhat. |
| `.env` | Cau hinh moi truong local: app url, database, session, cache, mail. |
| `.env.example` | Mau file moi truong de tao `.env` moi. |
| `.gitattributes` | Cau hinh git attributes cho line endings va export. |
| `.gitignore` | Khai bao file/thu muc khong dua vao git. |
| `artisan` | Diem vao CLI cua Laravel de chay migrate, seed, cache clear, test. |
| `composer.json` | Dinh nghia package PHP va metadata du an. |
| `composer.lock` | Khoa version package PHP da cai. |
| `package.json` | Dinh nghia package frontend va script build bang npm/Vite. |
| `package-lock.json` | Khoa version package Node.js. |
| `phpunit.xml` | Cau hinh chay test PHPUnit. |
| `vite.config.js` | Cau hinh bundler Vite cho asset trong `resources/`. |
| `README.md` | Tai lieu tong quan cua repo. |
| `cookies.txt` | File cookie dang Netscape do cong cu tao ra, khong phai file logic ung dung. |
| `et --hard HEAD@{1}` | File rac o thu muc goc, noi dung la log reflog git, khong phai file chuc nang cua he thong. |

## `bootstrap/`

| File | Cong dung |
| --- | --- |
| `bootstrap/app.php` | Khoi tao Laravel application, dang ky middleware va exception handling. |
| `bootstrap/providers.php` | Danh sach service provider nap vao ung dung. |

## `config/`

| File | Cong dung |
| --- | --- |
| `config/app.php` | Cau hinh app chung: ten ung dung, timezone, locale, provider. |
| `config/auth.php` | Cau hinh guard, provider va xac thuc. |
| `config/cache.php` | Cau hinh cache store va prefix cache. |
| `config/database.php` | Cau hinh ket noi database va migration repository. |
| `config/filesystems.php` | Cau hinh disk luu file. |
| `config/logging.php` | Cau hinh kenh log. |
| `config/mail.php` | Cau hinh gui mail. |
| `config/queue.php` | Cau hinh queue backend. |
| `config/services.php` | Noi de khai bao key/dich vu thu ba. |
| `config/session.php` | Cau hinh session driver, cookie, lifetime. |

## `routes/`

| File | Cong dung |
| --- | --- |
| `routes/web.php` | Toan bo route web cho khach, staff, admin, profile, cart, auth, revenue. |
| `routes/console.php` | Noi khai bao lenh Artisan dang closure neu can. |

## `app/Http/Controllers/`

| File | Cong dung |
| --- | --- |
| `app/Http/Controllers/Controller.php` | Base controller cha cho cac controller khac. |
| `app/Http/Controllers/HomeController.php` | Xu ly logic trang chu neu route su dung controller nay. |
| `app/Http/Controllers/MenuController.php` | Lay danh sach san pham/menu de hien thi cho khach. |
| `app/Http/Controllers/CartController.php` | Quan ly gio hang, cap nhat so luong, checkout tien mat va QR. |
| `app/Http/Controllers/ProductController.php` | Xu ly CRUD/chi tiet san pham neu duoc route su dung. |
| `app/Http/Controllers/OrderHistoryController.php` | Lich su don cua khach, theo doi trang thai, va tu huy don khi con hop le. |
| `app/Http/Controllers/LoginController.php` | Dang nhap, dang xuat, quan ly session dang nhap. |
| `app/Http/Controllers/RegisterController.php` | Dang ky tai khoan moi. |
| `app/Http/Controllers/ProfileController.php` | Xem va cap nhat thong tin ca nhan. |
| `app/Http/Controllers/ForgotPasswordController.php` | Quy trinh quen mat khau: gui ma, xac minh, dat lai mat khau. |
| `app/Http/Controllers/SearchController.php` | Tim kiem san pham tren giao dien khach. |
| `app/Http/Controllers/NewsletterController.php` | Xu ly dang ky nhan ban tin. |
| `app/Http/Controllers/AdminController.php` | Toan bo logic giao dien admin: dashboard, product, user, order, report. |
| `app/Http/Controllers/StaffController.php` | Toan bo logic giao dien nhan vien: dashboard, xu ly don, tao don tai quan, doanh thu, nhac trang thai. |

## `app/Http/Middleware/`

| File | Cong dung |
| --- | --- |
| `app/Http/Middleware/Authenticate.php` | Buoc user phai dang nhap truoc khi vao route bao ve. |
| `app/Http/Middleware/AdminMiddleware.php` | Chan truy cap neu khong phai admin. |
| `app/Http/Middleware/StaffMiddleware.php` | Chan truy cap neu khong phai nhan vien. |
| `app/Http/Middleware/RoleMiddleware.php` | Middleware phan quyen theo vai tro tong quat. |
| `app/Http/Middleware/CheckActiveUser.php` | Kiem tra tai khoan co dang active hay khong. |
| `app/Http/Middleware/SyncPaidQrOrderMiddleware.php` | Dong bo trang thai thanh toan QR vao don hang. |

## `app/Models/`

| File | Cong dung |
| --- | --- |
| `app/Models/User.php` | Model nguoi dung, auth, quan he role, dia chi, don hang. |
| `app/Models/UserRole.php` | Model vai tro nguoi dung. |
| `app/Models/Address.php` | Model dia chi giao hang cua khach. |
| `app/Models/CartItem.php` | Model item trong gio hang luu o database. |
| `app/Models/Category.php` | Model danh muc san pham. |
| `app/Models/Extra.php` | Model topping/duong/da hoac option bo sung. |
| `app/Models/Order.php` | Model don hang, chua status label/color, next status, va quy tac khach co duoc huy khong. |
| `app/Models/OrderItem.php` | Model tung dong san pham trong don hang. |
| `app/Models/OrderItemExtra.php` | Model lien ket option bo sung cua tung dong san pham. |
| `app/Models/Payment.php` | Model thanh toan cua don hang. |
| `app/Models/Product.php` | Model san pham/menu item. |
| `app/Models/Size.php` | Model kich co va phu thu gia. |
| `app/Models/Topping.php` | Model topping rieng neu du an tach thong tin nay thanh entity doc lap. |
| `app/Models/DailyRevenue.php` | Model snapshot doanh thu ngay de staff xem bao cao. |

## `app/Providers/`

| File | Cong dung |
| --- | --- |
| `app/Providers/AppServiceProvider.php` | Noi bootstrap logic chung cua ung dung va dang ky service. |

## `app/Support/`

| File | Cong dung |
| --- | --- |
| `app/Support/DailyRevenueSnapshotService.php` | Dong bo va luu snapshot doanh thu theo ngay trong 30 ngay gan nhat. |

## `database/factories/`

| File | Cong dung |
| --- | --- |
| `database/factories/UserFactory.php` | Tao du lieu user gia cho test/seeding. |

## `database/migrations/`

| File | Cong dung |
| --- | --- |
| `database/migrations/2025_01_01_000001_create_user_roles_table.php` | Tao bang vai tro nguoi dung. |
| `database/migrations/2025_01_01_000002_create_users_table.php` | Tao bang users. |
| `database/migrations/2025_01_01_000003_create_addresses_table.php` | Tao bang dia chi. |
| `database/migrations/2025_01_01_000004_create_shifts_table.php` | Tao bang ca lam viec cua nhan vien. |
| `database/migrations/2025_01_01_000005_create_categories_table.php` | Tao bang danh muc san pham. |
| `database/migrations/2025_01_01_000006_create_products_table.php` | Tao bang san pham. |
| `database/migrations/2025_01_01_000007_create_extras_table.php` | Tao bang extra/topping. |
| `database/migrations/2025_01_01_000008_create_carts_table.php` | Tao bang gio hang. |
| `database/migrations/2025_01_01_000009_create_vouchers_table.php` | Tao bang voucher. |
| `database/migrations/2025_01_01_000010_create_orders_table.php` | Tao bang don hang. |
| `database/migrations/2025_01_01_000011_create_payments_table.php` | Tao bang thanh toan. |
| `database/migrations/2025_01_01_000012_create_inventory_table.php` | Tao bang ton kho. |
| `database/migrations/2026_04_04_000001_add_updated_at_to_users_table.php` | Them `updated_at` cho users. |
| `database/migrations/2026_04_04_000002_create_password_resets_table.php` | Tao bang reset mat khau. |
| `database/migrations/2026_04_04_000003_add_remember_token_to_users_table.php` | Them `remember_token` cho login ghi nho. |
| `database/migrations/2026_04_07_000001_create_order_items_table.php` | Tao bang chi tiet san pham trong don. |
| `database/migrations/2026_04_07_000002_create_order_item_extras_table.php` | Tao bang extra cua order item. |
| `database/migrations/2026_04_07_000003_create_sizes_table.php` | Tao bang kich co san pham. |
| `database/migrations/2026_04_07_000004_add_type_to_extras_table.php` | Them cot phan loai extra. |
| `database/migrations/2026_04_09_040738_create_sessions_table.php` | Tao bang session khi su dung session database. |
| `database/migrations/2026_04_15_000001_add_seasonal_drinks_category.php` | Bo sung danh muc do uong theo mua. |
| `database/migrations/2026_04_15_000002_rename_seasonal_products_to_match_images.php` | Doi ten san pham theo ten anh san co. |
| `database/migrations/2026_04_15_000003_change_seasonal_matcha_to_strawberry_tea.php` | Thay san pham seasonal cu bang tra dau. |
| `database/migrations/2026_04_15_000004_add_tra_duong_nhan_to_seasonal_drinks.php` | Them tra duong nhan vao seasonal drinks. |
| `database/migrations/2026_04_15_000005_add_tropical_fruit_tea_to_seasonal_drinks.php` | Them tra trai cay nhiet doi vao seasonal drinks. |
| `database/migrations/2026_04_15_000006_create_daily_revenues_table.php` | Tao bang snapshot doanh thu ngay. |

## `database/seeders/`

| File | Cong dung |
| --- | --- |
| `database/seeders/DatabaseSeeder.php` | Seeder tong goi cac seeder con. |
| `database/seeders/UserRoleSeeder.php` | Seed du lieu vai tro. |
| `database/seeders/UserSeeder.php` | Seed tai khoan mau. |
| `database/seeders/AddressSeeder.php` | Seed dia chi mau. |
| `database/seeders/ProductSeeder.php` | Seed san pham mau. |
| `database/seeders/CartSeeder.php` | Seed gio hang mau. |
| `database/seeders/OrderSeeder.php` | Seed don hang mau. |
| `database/seeders/ShiftSeeder.php` | Seed lich su ca lam/ca truc. |
| `database/seeders/SizeSeeder.php` | Seed kich co va phu thu. |
| `database/seeders/VoucherSeeder.php` | Seed voucher giam gia. |
| `database/seeders/InventorySeeder.php` | Seed ton kho ban dau. |

## `resources/css/` va `resources/js/`

| File | Cong dung |
| --- | --- |
| `resources/css/app.css` | File CSS dau vao neu build asset bang Vite. |
| `resources/js/app.js` | Entry JS chinh khi build voi Vite. |
| `resources/js/bootstrap.js` | Cau hinh JS bootstrap/axios cho frontend theo scaffold Laravel. |

## `resources/views/`

### View chung cho khach

| File | Cong dung |
| --- | --- |
| `resources/views/home.blade.php` | Trang chu marketing/gioi thieu cua quan. |
| `resources/views/login.blade.php` | Trang dang nhap va dang ky. |
| `resources/views/menu.blade.php` | Trang menu dat mon, co modal chon size/topping/da/duong. |
| `resources/views/cart.blade.php` | Trang gio hang va thanh toan tu gio hang. |
| `resources/views/payment.blade.php` | Giao dien thanh toan neu route nay duoc su dung rieng. |
| `resources/views/profile.blade.php` | Trang ho so nguoi dung. |
| `resources/views/order-history.blade.php` | Lich su don cua khach va nut huy don khi con hop le. |
| `resources/views/search-result.blade.php` | Hien thi ket qua tim kiem san pham. |

### `resources/views/auth/`

| File | Cong dung |
| --- | --- |
| `resources/views/auth/forgot-password-email.blade.php` | Form nhap email de xin dat lai mat khau. |
| `resources/views/auth/forgot-password-verify.blade.php` | Form nhap ma xac minh reset mat khau. |
| `resources/views/auth/forgot-password-reset.blade.php` | Form dat mat khau moi. |

### `resources/views/components/`

| File | Cong dung |
| --- | --- |
| `resources/views/components/search-bar.blade.php` | Thanh tim kiem tai su dung tren cac trang khach. |

### `resources/views/errors/`

| File | Cong dung |
| --- | --- |
| `resources/views/errors/403.blade.php` | Trang bao loi cam truy cap. |

### `resources/views/admin/`

| File | Cong dung |
| --- | --- |
| `resources/views/admin/layout.blade.php` | Layout chung cho admin. |
| `resources/views/admin/dashboard.blade.php` | Dashboard tong quan admin. |
| `resources/views/admin/reports.blade.php` | Trang bao cao cho admin. |
| `resources/views/admin/categories/index.blade.php` | Quan ly danh muc. |
| `resources/views/admin/products/index.blade.php` | Danh sach san pham. |
| `resources/views/admin/products/create.blade.php` | Form tao san pham. |
| `resources/views/admin/products/edit.blade.php` | Form sua san pham. |
| `resources/views/admin/users/index.blade.php` | Danh sach nguoi dung. |
| `resources/views/admin/users/create.blade.php` | Form tao nguoi dung. |
| `resources/views/admin/users/edit.blade.php` | Form sua nguoi dung. |
| `resources/views/admin/orders/index.blade.php` | Danh sach don cho admin. |
| `resources/views/admin/orders/detail.blade.php` | Chi tiet don cho admin. |

### `resources/views/staff/`

| File | Cong dung |
| --- | --- |
| `resources/views/staff/layout.blade.php` | Layout staff, sidebar, topbar, va JS nhac don hang. |
| `resources/views/staff/dashboard.blade.php` | Dashboard nhan vien. |
| `resources/views/staff/orders.blade.php` | Danh sach don cho nhan vien xu ly theo trang thai. |
| `resources/views/staff/order-detail.blade.php` | Chi tiet don va hanh dong cap nhat trang thai/huy. |
| `resources/views/staff/create-order.blade.php` | Giao dien nhan vien tao don tai quan, co bill cash va QR. |
| `resources/views/staff/created-order-history.blade.php` | Lich su don tai quan do nhan vien tao. |
| `resources/views/staff/current-day-revenue.blade.php` | Bao cao doanh thu ngay hien tai. |
| `resources/views/staff/daily-revenue.blade.php` | Bao cao doanh thu thang/30 ngay gan nhat. |
| `resources/views/staff/pagination.blade.php` | View phan trang tuy bien cho staff. |

## `public/`

### Root public files

| File | Cong dung |
| --- | --- |
| `public/index.php` | Front controller cua Laravel. |
| `public/robots.txt` | Huong dan co ban cho search engine crawler. |
| `public/audio/order-reminder.wav` | Am thanh nhac nhan vien khi don bi cham xu ly. |

### Asset tuy bien do du an su dung truc tiep

| File | Cong dung |
| --- | --- |
| `public/css/style.css` | CSS lon dang duoc cac view khach tai truc tiep. |
| `public/css/login.css` | CSS rieng cho man dang nhap. |
| `public/css/footer.css` | CSS footer. |
| `public/js/main.js` | JS chung cua theme/frontend khach. |
| `public/js/footer.js` | JS footer neu co tuong tac rieng. |
| `public/js/google-map.js` | JS map neu view can nhung ban do. |
| `public/js/range.js` | JS cho thanh range/tuong tac so. |
| `public/images/logo.png` | Logo quan. |
| `public/images/user.jpg` | Anh dai dien mac dinh. |
| `public/images/*san-pham*` | Anh menu/san pham dung tren giao dien. |
| `public/images/gallery-*`, `drink-*`, `dessert-*`, `menu-*` | Anh noi dung/trang tri cho trang chu va cac section marketing. |
| `public/fonts/*` | Font/icon font su dung boi CSS. |

### Asset ben thu ba duoc nap truc tiep

| Thu muc/File | Cong dung |
| --- | --- |
| `public/css/bootstrap.min.css`, `public/js/bootstrap.min.js`, `public/js/popper.min.js` | Thu vien Bootstrap cho layout va component. |
| `public/js/jquery*.js` | Thu vien jQuery va plugin ho tro. |
| `public/css/aos.css`, `public/js/aos.js` | Thu vien animation scroll. |
| `public/css/owl*.css`, `public/js/owl.carousel.min.js` | Thu vien carousel. |
| `public/css/magnific-popup.css`, `public/js/jquery.magnific-popup.min.js` | Thu vien popup/modal. |
| `public/css/ionicons.min.css`, `public/css/icomoon.css`, `public/css/flaticon.css`, `public/css/open-iconic-bootstrap.min.css` | Bo icon/font ben thu ba. |

## `tests/`

| File | Cong dung |
| --- | --- |
| `tests/TestCase.php` | Base test case cho toan bo test Laravel. |
| `tests/Feature/ExampleTest.php` | Feature test mau cua Laravel. |
| `tests/Unit/ExampleTest.php` | Unit test mau cua Laravel. |

## Ghi chu su dung tai lieu

- Neu can tim nhanh mot file dang phuc vu muc nao, tra theo nhom thu muc truoc, sau do doc ten file.
- Neu can note chi tiet tung ham/method trong mot file lon nhu `StaffController.php` hoac `menu.blade.php`, nen tach them tai lieu rieng theo module vi nhung file nay da vuot muc "mo ta muc dich file".
- Neu muon, buoc tiep theo co the tao them tai lieu cap 2 cho tung module: khach hang, nhan vien, admin, database.