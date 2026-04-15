<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // Đăng ký service/container binding dùng chung cho toàn ứng dụng nếu cần.
    public function register(): void
    {
        //
    }

    // Boot các hook dùng chung sau khi ứng dụng đã sẵn sàng.
    public function boot(): void
    {
        // Chia sẻ số lượng sản phẩm trong giỏ hàng cho tất cả view
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $cart = session('cart', []);
            $cartCount = array_sum(array_column($cart, 'qty'));
            $view->with('cartCount', $cartCount);
        });
    }
}
