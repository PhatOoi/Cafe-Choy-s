<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
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
