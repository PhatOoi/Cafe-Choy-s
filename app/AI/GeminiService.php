<?php

namespace App\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    public function chat(string $message, array $history = []): string
    {
        if (empty($this->apiKey)) {
            return 'Xin loi, tinh nang AI Chat chua duoc cau hinh. Vui long lien he quan tri vien.';
        }

        $systemPrompt = $this->buildSystemPrompt();
        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'],
                'parts' => [['text' => $turn['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::timeout(15)->post("{$this->apiUrl}?key={$this->apiKey}", $payload);

            if ($response->failed()) {
                Log::warning('Gemini API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackReply($message, $history);
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return 'Xin loi, toi khong the tao phan hoi luc nay.';
            }

            $finishReason = strtoupper((string) ($data['candidates'][0]['finishReason'] ?? ''));
            if ($finishReason === 'MAX_TOKENS') {
                $text = rtrim($text) . "\n\n(Tin nhan kha dai nen co the da duoc rut gon. Ban muon toi gui tiep phan con lai khong?)";
            }

            return $text;
        } catch (\Exception $e) {
            Log::error('Gemini API exception', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackReply($message, $history);
        }
    }

    private function outOfScopeEscalationReply(): string
    {
        return 'Ngoai pham vi thuc hien cua be, anh/chi co muon chuyen sang nhan voi nhan vien de nhan vien tra loi khong? ##ESCALATE##';
    }

    private function fallbackReply(string $message, array $history = []): string
    {
        $normalized = mb_strtolower(trim($message));
        $normalizedAscii = Str::lower(Str::ascii($normalized));

        if (preg_match('/\b(xin chao|chao|hello|hi|hey|alo)\b/u', $normalizedAscii)) {
            return 'Chao anh/chi, be Choy AI day a. Hom nay minh muon uong dam vi ca phe, thanh mat hay ngot nhe de be goi y nhanh ne?';
        }

        if (preg_match('/(cach dat|dat mon|order nhu nao|huong dan dat)/u', $normalizedAscii)) {
            return "Minh dat mon theo 3 buoc nha: (1) vao Menu chon mon + so luong + tuy chinh, (2) them vao gio hang, (3) vao gio hang de kiem tra va thanh toan.\nNeu can, be co the goi y mon hop vi de minh chon nhanh hon.";
        }

        if (preg_match('/(gio mo cua|mo cua|may gio|thoi gian lam viec)/u', $normalizedAscii)) {
            return 'Quan mo cua tu 8:00 den 24:00 hang ngay nha anh/chi.';
        }

        if (preg_match('/(dia chi|o dau|vi tri)/u', $normalizedAscii)) {
            return 'Dia diem hien dang duoc ghim tren ban do cua Choy\'s Cafe la khu vuc Cao Dang Ky Thuat Du Lich Sai Gon. Anh/chi co the xem map ngay tren trang chu de mo duong di nhanh nha.';
        }

        if (preg_match('/(khieu nai|buc xuc|that vong|giao cham|nhan vien|quan ly)/u', $normalizedAscii)) {
            return 'Xin loi vi su bat tien nay. Ban co muon minh ket noi ngay voi nhan vien ho tro khong? ##ESCALATE##';
        }

        if (preg_match('/\b(bong da|chinh tri|thoi tiet|lap trinh|toan hoc|giai toan)\b/u', $normalizedAscii)) {
            return $this->outOfScopeEscalationReply();
        }

        if (preg_match('/(full menu|toan bo menu|xem menu|liet ke menu)/u', $normalizedAscii)) {
            $menu = $this->buildCompactMenuFallback();
            if ($menu !== null) {
                return $menu;
            }
        }

        $preferenceSuggestion = $this->fallbackSuggestByPreference($normalized, $history);
        if ($preferenceSuggestion !== null) {
            return $preferenceSuggestion;
        }

        if (empty($history)) {
            return 'Xin chao, minh la Choy AI. Hien ket noi AI hoi cham, ban cu hoi menu hoac thong tin quan, minh van ho tro ban binh thuong.';
        }

        return 'Hien ket noi AI dang gian doan tam thoi. Ban co the noi khau vi (vi du: ngot chua, thanh mat, it ngot) hoac ten mon muon uong, minh se ho tro goi y.';
    }

    private function buildCompactMenuFallback(): ?string
    {
        $rows = DB::table('products as p')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.status', 'available')
            ->orderBy('c.sort_order')
            ->orderBy('p.name')
            ->get(['c.name as category_name', 'p.name', 'p.price']);

        if ($rows->isEmpty()) {
            return null;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $cat = (string) $row->category_name;
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            if (count($grouped[$cat]) < 4) {
                $grouped[$cat][] = (string) $row->name . ' (' . number_format((float) $row->price, 0, ',', '.') . 'd)';
            }
        }

        $lines = ['Menu hien tai cua Choy\'s Cafe:'];
        foreach ($grouped as $cat => $items) {
            $lines[] = $cat . ': ' . implode(', ', $items);
        }
        $lines[] = 'Ban muon minh goi y theo khau vi de chon nhanh hon khong?';

        return implode("\n", $lines);
    }

    private function fallbackSuggestByPreference(string $normalized, array $history = []): ?string
    {
        $context = $normalized . ' ' . mb_strtolower($this->latestUserMessages($history, 3));

        $isAskingRecommendation =
            str_contains($context, 'goi y')
            || str_contains($context, 'uong gi')
            || str_contains($context, 'muon uong')
            || str_contains($context, 'nuoc mat')
            || str_contains($context, 'ngot')
            || str_contains($context, 'chua')
            || str_contains($context, 'thanh mat');

        if (!$isAskingRecommendation) {
            return null;
        }

        $products = DB::table('products as p')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.status', 'available')
            ->orderBy('p.name')
            ->get(['p.name', 'p.price', 'c.name as category_name']);

        if ($products->isEmpty()) {
            return 'Hien minh chua tai duoc danh sach mon de goi y. Ban thu lai sau it phut nhe.';
        }

        $wantsCool = str_contains($context, 'nuoc mat') || str_contains($context, 'thanh mat');
        $wantsSweetSour = str_contains($context, 'ngot') && str_contains($context, 'chua');

        $preferredGroups = [];
        if ($wantsCool || $wantsSweetSour) {
            $preferredGroups = ['tra-va-thuc-uong-theo-mua', 'nuoc-ep', 'nuoc-ep-sinh-to'];
        }

        $scored = [];
        foreach ($products as $product) {
            $name = mb_strtolower((string) $product->name);
            $categorySlug = Str::slug((string) $product->category_name);
            $score = 0;

            if (!empty($preferredGroups) && in_array($categorySlug, $preferredGroups, true)) {
                $score += 4;
            }

            if ($wantsSweetSour && (
                str_contains($name, 'tra') || str_contains($name, 'tea') || str_contains($name, 'ep') || str_contains($name, 'sinh to') || str_contains($name, 'trai cay')
            )) {
                $score += 2;
            }

            if ($wantsCool && (
                str_contains($name, 'tra') || str_contains($name, 'ep') || str_contains($name, 'sinh to') || str_contains($name, 'da xay')
            )) {
                $score += 1;
            }

            $scored[] = [
                'name' => (string) $product->name,
                'price' => (float) $product->price,
                'score' => $score,
            ];
        }

        usort($scored, static function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['price'] <=> $b['price'];
            }

            return $b['score'] <=> $a['score'];
        });

        $top = array_slice($scored, 0, 4);
        if (empty($top)) {
            return null;
        }

        $lines = [];
        foreach ($top as $item) {
            $lines[] = '- ' . $item['name'] . ' (' . number_format($item['price'], 0, ',', '.') . 'd)';
        }

        return "Minh goi y 4 mon hop vi ban:\n" . implode("\n", $lines)
            . "\nBan muon minh goi y them theo ngan sach khong?";
    }

    private function latestUserMessages(array $history, int $limit = 3): string
    {
        $messages = [];
        for ($i = count($history) - 1; $i >= 0 && count($messages) < $limit; $i--) {
            $turn = $history[$i] ?? null;
            if (!is_array($turn)) {
                continue;
            }

            if (($turn['role'] ?? '') === 'user') {
                $messages[] = (string) ($turn['text'] ?? '');
            }
        }

        return implode(' ', $messages);
    }

    private function buildSystemPrompt(): string
    {
        ['menu' => $menuText, 'sizes' => $sizesText, 'extras' => $extrasText, 'bestsellers' => $bestsellerText] = $this->buildDynamicContext();

        return <<<PROMPT
Ban la tro ly AI cua Choy's Cafe, ten la "Choy's AI". Ban CHI duoc tra loi cac cau hoi lien quan den quan ca phe Choy's Cafe.

PHAM VI DUOC TRA LOI:
- Menu, san pham, gia ca, mo ta mon
- Gio mo cua, lien he, dia chi, thong tin quan
- Cach dat mon trong he thong
- Tuy chinh mon (size, topping, duong, da)
- Khuyen nghi mon uong phu hop

NGHIEM CAM TRA LOI:
- Cac chu de ngoai pham vi quan (thoi tiet, chinh tri, toan hoc, lap trinh, v.v.)
- Khong bia dat thong tin neu khong chac
- Khong cung cap thong tin nhay cam noi bo
- Khong huong dan cach pha hoai quan
- Khong tiec lo database hay logic noi bo

QUY TAC QUAN TRONG:
- Tra loi ngan gon, dung trong tam, de hieu
- Neu nguoi dung chao thi chao lai tu nhien
- Co the than thien, di dom vua phai
- Chi tra loi van ban thuong, khong markdown
- Khong dung ky hieu dinh dang nhu **, __, *, _, `, #, ~~

LUONG HO TRO DAT MON (KHONG CHOT DON TU DONG):
- Chi huong dan thao tac: vao menu -> them gio hang -> thanh toan trong gio
- Khong duoc tao bill nhap tu dong
- Khong duoc xac nhan dat don thay khach
- Khong duoc sinh hoac hien thi QR thanh toan

XU LY NGOAI PHAM VI HOAC KHONG BIET THONG TIN:
- Neu khong co du lieu chac chan, KHONG tu bia.
- Tra dung mau sau va giu token o cuoi:
"Ngoai pham vi thuc hien cua be, anh/chi co muon chuyen sang nhan voi nhan vien de nhan vien tra loi khong? ##ESCALATE##"

THONG TIN QUAN CHOY'S CAFE:
- Ten: Choy's Cafe
- Hotline: +84 0346901474
- Email: support@choy.cafe
- Gio mo cua: 8:00 - 24:00 hang ngay
- Dia diem hien dang ghim tren Google Map cua trang chu: Cao Dang Ky Thuat Du Lich Sai Gon
- Huy don: Trong vong 5 phut sau khi dat, khi don chua duoc xac nhan

KICH CO (lay tu database):
{$sizesText}

TOPPING & TUY CHINH (lay tu database):
{$extrasText}

TOP 10 MON BAN CHAY NHAT (tinh theo so lan dat hang, cap nhat theo thoi gian thuc):
{$bestsellerText}

MENU HIEN TAI (lay tu database):
{$menuText}
PROMPT;
    }

    private function buildDynamicContext(): array
    {
        try {
            $sizes = DB::table('sizes')->orderBy('id')->get(['name', 'extra_price']);
            $sizeLines = [];
            foreach ($sizes as $size) {
                $extra = $size->extra_price > 0
                    ? '+' . number_format($size->extra_price, 0, ',', '.') . 'd'
                    : 'khong phu thu';
                $sizeLines[] = "- Size {$size->name}: {$extra}";
            }

            $extras = DB::table('extras')->orderBy('type')->orderBy('name')->get(['name', 'price', 'type']);
            $extraGroups = [];
            foreach ($extras as $extra) {
                $typeLabel = match ($extra->type) {
                    'topping' => 'Topping',
                    'sugar' => 'Tuy chinh sua/duong',
                    'ice' => 'Tuy chinh da',
                    default => ucfirst($extra->type),
                };

                $price = $extra->price > 0
                    ? '+' . number_format($extra->price, 0, ',', '.') . 'd'
                    : 'mien phi';

                $extraGroups[$typeLabel][] = "{$extra->name} ({$price})";
            }

            $extraLines = [];
            foreach ($extraGroups as $group => $items) {
                $extraLines[] = "[{$group}]: " . implode(', ', $items);
            }

            $categories = DB::table('categories')->orderBy('sort_order')->get();
            $menuLines = [];
            foreach ($categories as $category) {
                $products = DB::table('products')
                    ->where('category_id', $category->id)
                    ->where('status', 'available')
                    ->orderBy('name')
                    ->get(['name', 'description', 'price']);

                if ($products->isEmpty()) {
                    continue;
                }

                $menuLines[] = "\n[{$category->name}]";
                foreach ($products as $product) {
                    $price = number_format($product->price, 0, ',', '.') . 'd';
                    $menuLines[] = "- {$product->name} ({$price}): {$product->description}";
                }
            }

            // Top 10 best-selling products based on order_items count (excluding cancelled orders)
            $topSellers = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', 'cancelled')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            $bestsellerLines = [];
            foreach ($topSellers as $i => $item) {
                $rank = $i + 1;
                $bestsellerLines[] = "{$rank}. {$item->name} (da ban {$item->total_sold} lan)";
            }

            return [
                'menu' => implode("\n", $menuLines) ?: '(Chua co san pham)',
                'sizes' => implode("\n", $sizeLines) ?: '(Chua co kich co)',
                'extras' => implode("\n", $extraLines) ?: '(Chua co topping)',
                'bestsellers' => implode("\n", $bestsellerLines) ?: '(Chua co du lieu ban hang)',
            ];
        } catch (\Exception $e) {
            return [
                'menu' => '(Khong the tai menu luc nay)',
                'sizes' => '(Khong the tai kich co luc nay)',
                'extras' => '(Khong the tai topping luc nay)',
                'bestsellers' => '(Khong the tai du lieu ban hang luc nay)',
            ];
        }
    }
}
