<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Extra;
use App\Models\Product;
use App\Models\Size;
use App\Observers\AiMenuSnapshotObserver;
use App\Support\AiMenuSnapshotService;
use Illuminate\Support\Facades\Log;
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
        Product::observe(AiMenuSnapshotObserver::class);
        Category::observe(AiMenuSnapshotObserver::class);
        Size::observe(AiMenuSnapshotObserver::class);
        Extra::observe(AiMenuSnapshotObserver::class);

        if (!$this->app->runningInConsole()) {
            try {
                app(AiMenuSnapshotService::class)->ensureSnapshotExists();
            } catch (\Throwable $e) {
                Log::warning('Khong the khoi tao AI DB.json luc khoi dong web', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Chia sẻ số lượng sản phẩm trong giỏ hàng cho tất cả view
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $cart = session('cart', []);
            $cartCount = array_sum(array_column($cart, 'qty'));
            $view->with('cartCount', $cartCount);
        });
    }
}
