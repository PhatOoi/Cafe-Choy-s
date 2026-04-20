Các bước chạy web
mở terminal
b1 chạy lệnh: composer install, copy .env.example .env, php artisan key:generate, npm install, npm run dev
b2 trong .env vừa copy chỉnh DB_CONNECTION=mysql
                             DB_HOST=127.0.0.1
                             DB_PORT=3306
                             DB_DATABASE=choy-cafe-s
                             DB_USERNAME=root
                             DB_PASSWORD=
b3 tạo bảng và dữ liệu mẫu: php artisan migrate:fresh --seed
b5 chạy web: php artisan serve