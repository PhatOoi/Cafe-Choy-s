<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Extra;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class AiMenuSnapshotService
{
    public const SNAPSHOT_RELATIVE_PATH = 'app/ai/DB.json';

    public function ensureSnapshotExists(): void
    {
        if (!File::exists($this->absolutePath())) {
            $this->refresh();
        }
    }

    public function refresh(): array
    {
        $payload = $this->buildPayload();

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Khong the ma hoa du lieu menu sang JSON.');
        }

        File::ensureDirectoryExists(dirname($this->absolutePath()));
        File::put($this->absolutePath(), $json);

        return $payload;
    }

    public function absolutePath(): string
    {
        return storage_path(self::SNAPSHOT_RELATIVE_PATH);
    }

    private function buildPayload(): array
    {
        $categories = Category::query()
            ->orderByRaw('COALESCE(sort_order, 999999)')
            ->orderBy('name')
            ->get()
            ->map(static function (Category $category) {
                return [
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'sort_order' => $category->sort_order !== null ? (int) $category->sort_order : null,
                ];
            })
            ->values()
            ->all();

        $products = Product::query()
            ->orderBy('name')
            ->get()
            ->map(static function (Product $product) {
                return [
                    'id' => (int) $product->id,
                    'category_id' => (int) $product->category_id,
                    'name' => (string) $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'stock' => $product->stock !== null ? (int) $product->stock : null,
                    'status' => (string) $product->status,
                    'image_url' => $product->image_url,
                ];
            })
            ->values()
            ->all();

        $sizes = Size::query()
            ->orderBy('id')
            ->get()
            ->map(static function (Size $size) {
                return [
                    'id' => (int) $size->id,
                    'name' => (string) $size->name,
                    'extra_price' => (float) $size->extra_price,
                ];
            })
            ->values()
            ->all();

        $extras = Extra::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(static function (Extra $extra) {
                return [
                    'id' => (int) $extra->id,
                    'name' => (string) $extra->name,
                    'price' => (float) $extra->price,
                    'type' => (string) $extra->type,
                ];
            })
            ->values()
            ->all();

        $toppings = array_values(array_filter($extras, static function (array $extra) {
            return ($extra['type'] ?? null) === 'topping';
        }));

        $productsByCategory = [];
        foreach ($products as $product) {
            if (($product['status'] ?? '') !== 'available') {
                continue;
            }

            $productsByCategory[$product['category_id']][] = $product;
        }

        $menu = [];
        foreach ($categories as $category) {
            $categoryProducts = $productsByCategory[$category['id']] ?? [];
            if (empty($categoryProducts)) {
                continue;
            }

            $menu[] = [
                'category' => $category,
                'products' => $categoryProducts,
            ];
        }

        $topSellers = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get()
            ->map(static function ($item) {
                return [
                    'product_id' => (int) $item->id,
                    'name' => (string) $item->name,
                    'total_sold' => (int) $item->total_sold,
                ];
            })
            ->values()
            ->all();

        return [
            'generated_at' => now()->toIso8601String(),
            'source' => 'database',
            'version' => 1,
            'categories' => $categories,
            'products' => $products,
            'sizes' => $sizes,
            'extras' => $extras,
            'toppings' => $toppings,
            'menu' => $menu,
            'top_sellers' => $topSellers,
        ];
    }
}
