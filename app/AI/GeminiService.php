<?php

namespace App\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GeminiService
{
    private string $apiKey;
    private string $apiUrl          = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    private string $fallbackApiUrl  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private string $fallback2ApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    public function chat(string $message, array $history = [], ?array $snapshotOverride = null): string
    {
        if (empty($this->apiKey)) {
            return 'Xin lỗi, tính năng AI Chat chưa được cấu hình. Vui lòng liên hệ quản trị viên.';
        }

        $snapshot = $this->normalizeSnapshot($snapshotOverride) ?? $this->readSnapshot();
        if ($snapshot === null) {
            return 'Xin lỗi, dữ liệu menu AI (DB.json) chưa sẵn sàng. Vui lòng yêu cầu quản trị viên cập nhật dữ liệu menu.';
        }

        $systemPrompt = $this->buildSystemPrompt($snapshot);
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

            // Retry với model dự phòng khi model chính bị quá tải (503) hoặc rate limit (429)
            if (in_array($response->status(), [429, 503], true)) {
                Log::warning('Gemini primary model unavailable, retrying with fallback model', [
                    'status' => $response->status(),
                ]);
                $response = Http::timeout(15)->post("{$this->fallbackApiUrl}?key={$this->apiKey}", $payload);
            }

            // Retry lần 2 với model thứ 3 nếu model thứ 2 cũng thất bại
            if (in_array($response->status(), [429, 503], true)) {
                Log::warning('Gemini fallback model unavailable, retrying with model 3', [
                    'status' => $response->status(),
                ]);
                $response = Http::timeout(15)->post("{$this->fallback2ApiUrl}?key={$this->apiKey}", $payload);
            }

            if ($response->failed()) {
                Log::warning('Gemini API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackReply($message, $history, $snapshot);
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return 'Xin lỗi, tôi không thể tạo phản hồi lúc này.';
            }

            $finishReason = strtoupper((string) ($data['candidates'][0]['finishReason'] ?? ''));
            if ($finishReason === 'MAX_TOKENS') {
                $text = rtrim($text) . "\n\n(Tin nhắn khá dài nên có thể đã được rút gọn. Bạn muốn tôi gửi tiếp phần còn lại không?)";
            }

            return $text;
        } catch (\Exception $e) {
            Log::error('Gemini API exception', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackReply($message, $history, $snapshot);
        }
    }

    private function outOfScopeEscalationReply(): string
    {
        return 'Ngoài phạm vi thực hiện của bé, anh/chị có muốn chuyển sang nhắn với nhân viên để nhân viên trả lời không? ##ESCALATE##';
    }

    private function fallbackReply(string $message, array $history = [], ?array $snapshot = null): string
    {
        $snapshot ??= $this->readSnapshot();
        if ($snapshot === null) {
            return 'Xin lỗi, dữ liệu menu AI (DB.json) chưa sẵn sàng. Vui lòng yêu cầu quản trị viên cập nhật dữ liệu menu.';
        }

        $normalized      = mb_strtolower(trim($message));
        $normalizedAscii = Str::lower(Str::ascii($normalized));

        // --- Các câu trả lời cứng không cần Gemini ---
        if (preg_match('/\b(xin chao|chao|hello|hi|hey|alo)\b/u', $normalizedAscii)) {
            return 'Chào anh/chị, bé Choy AI đây ạ. Hôm nay mình muốn uống đậm vị cà phê, thanh mát hay ngọt nhẹ để bé gợi ý nhanh nè?';
        }

        if (preg_match('/(cach dat|dat mon|order nhu nao|huong dan dat)/u', $normalizedAscii)) {
            return "Mình đặt món theo 3 bước nha: (1) vào Menu chọn món + số lượng + tùy chỉnh, (2) thêm vào giỏ hàng, (3) vào giỏ hàng để kiểm tra và thanh toán.\nNếu cần, bé có thể gợi ý món hợp vị để mình chọn nhanh hơn.";
        }

        if (preg_match('/(gio mo cua|mo cua|may gio|thoi gian lam viec)/u', $normalizedAscii)) {
            return 'Quán mở cửa từ 8:00 đến 24:00 hàng ngày nha anh/chị.';
        }

        if (preg_match('/(dia chi|o dau|vi tri)/u', $normalizedAscii)) {
            return 'Địa điểm hiện đang được ghim trên bản đồ của Choy\'s Cafe là khu vực Cao Đẳng Kỹ Thuật Du Lịch Sài Gòn. Anh/chị có thể xem map ngay trên trang chủ để mở đường đi nhanh nha.';
        }

        if (preg_match('/(khieu nai|buc xuc|that vong|giao cham|nhan vien|quan ly)/u', $normalizedAscii)) {
            return 'Xin lỗi vì sự bất tiện này. Bạn có muốn mình kết nối ngay với nhân viên hỗ trợ không? ##ESCALATE##';
        }

        if (preg_match('/\b(bong da|chinh tri|thoi tiet|lap trinh|toan hoc|giai toan)\b/u', $normalizedAscii)) {
            return $this->outOfScopeEscalationReply();
        }

        if (preg_match('/(full menu|toan bo menu|xem menu|liet ke menu)/u', $normalizedAscii)) {
            $menu = $this->buildCompactMenuFallback($snapshot);
            if ($menu !== null) {
                return $menu;
            }
        }

        // --- Gợi ý theo khẩu vị nếu user đã nói rõ ---
        $preferenceSuggestion = $this->fallbackSuggestByPreference($normalized, $history, $snapshot);
        if ($preferenceSuggestion !== null) {
            return $preferenceSuggestion;
        }

        // --- Gợi ý theo cảm xúc đơn giản khi AI offline ---
        $emotionSuggestion = $this->fallbackSuggestByEmotion($normalizedAscii, $snapshot);
        if ($emotionSuggestion !== null) {
            return $emotionSuggestion;
        }

        // --- Tìm theo tên món nếu user nhắn tên cụ thể ---
        $productMatch = $this->fallbackSearchProduct($normalizedAscii, $snapshot);
        if ($productMatch !== null) {
            return $productMatch;
        }

        // --- Default: gợi ý top sellers nhưng không lặp nếu đã hiện trước đó ---
        $alreadySuggested = $this->hasAlreadyShownTopSellers($history);
        if ($alreadySuggested) {
            // Lần 2 trở đi: gợi ý ngẫu nhiên category khác
            return $this->fallbackSuggestRandomCategory($snapshot);
        }

        $intro = empty($history)
            ? 'Ủa sao vào đây mà chưa gọi món vậy? Bé Choy AI đây, để bé gợi ý luôn cho nóng nào!'
            : 'AI đang bận xíu, nhưng bé vẫn gợi ý được nha! Mấy món này đang hot lắm:';

        return $this->buildDirectSuggestionFallback($snapshot, $intro);
    }

    private function hasAlreadyShownTopSellers(array $history): bool
    {
        // Kiểm tra xem trong lịch sử trả lời có câu nào chứa cụm "đang hot" hoặc "gợi ý luôn" chưa
        for ($i = count($history) - 1; $i >= 0; $i--) {
            $turn = $history[$i] ?? null;
            if (!is_array($turn) || ($turn['role'] ?? '') !== 'model') {
                continue;
            }
            $text = mb_strtolower((string) ($turn['text'] ?? ''));
            if (
                str_contains($text, 'đang hot') ||
                str_contains($text, 'gợi ý luôn') ||
                str_contains($text, 'bé vẫn gợi ý') ||
                str_contains($text, 'mấy món này')
            ) {
                return true;
            }
        }
        return false;
    }

    private function fallbackSuggestByEmotion(string $normalizedAscii, array $snapshot): ?string
    {
        $products = array_values(array_filter((array) ($snapshot['products'] ?? []), static fn ($p) => is_array($p) && ($p['status'] ?? '') === 'available'));
        if (empty($products)) {
            return null;
        }

        $emotionMap = [
            'chia tay|that tinh|buon|co don|khoc' => [
                'keywords' => ['ca phe den', 'tra chanh', 'espresso', 'bac xiu', 'ca phe sua'],
                'intro'    => 'Chia tay rồi à? Uống gì cũng đắng, chi bằng uống đắng có chủ đích cho nó sang nhỉ! Bé gợi ý:',
            ],
            'vui|hanh phuc|tin tot|thang|dat|sinh nhat|ky niem' => [
                'keywords' => ['tra sua', 'matcha', 'latte', 'da xay', 'sinh to'],
                'intro'    => 'Ngày vui thì phải thưởng mình một thứ ngọt xứng đáng chứ! Bé gợi ý:',
            ],
            'hen ho|crush|yeu|nguoi thuong|dat hen' => [
                'keywords' => ['tra sua', 'sinh to', 'matcha', 'latte', 'da xay'],
                'intro'    => 'Hẹn hò thì phải order đôi cho đẹp cặp nha! Bé gợi ý:',
            ],
            'met|buon ngu|can tinh|ot|trang dem|cay' => [
                'keywords' => ['ca phe', 'espresso', 'bac xiu', 'latte', 'ca phe den'],
                'intro'    => 'Não đang lag thì phải reboot bằng caffeine thôi! Bé gợi ý:',
            ],
            'stress|ap luc|lo lang|cang thang' => [
                'keywords' => ['tra', 'nuoc ep', 'sinh to', 'matcha'],
                'intro'    => 'Stress rồi thì cần gì thanh mát, nhẹ nhàng cho não nghỉ ngơi! Bé gợi ý:',
            ],
            'nong|nang|oi buc|nong qua' => [
                'keywords' => ['da xay', 'nuoc ep', 'sinh to', 'tra da'],
                'intro'    => 'Trời nóng chảy mỡ rồi, phải đá nhiều thôi! Bé gợi ý:',
            ],
            'mua|lanh|se se' => [
                'keywords' => ['ca phe nong', 'tra nong', 'latte', 'bac xiu'],
                'intro'    => 'Mưa mà không có ly nóng trong tay thì phí cả buổi chiều! Bé gợi ý:',
            ],
        ];

        $matchedEmotion = null;
        foreach ($emotionMap as $pattern => $config) {
            if (preg_match('/(' . $pattern . ')/u', $normalizedAscii)) {
                $matchedEmotion = $config;
                break;
            }
        }

        if ($matchedEmotion === null) {
            return null;
        }

        // Tìm các món khớp keyword
        $matched = [];
        foreach ($products as $p) {
            $pName = Str::lower(Str::ascii((string) ($p['name'] ?? '')));
            foreach ($matchedEmotion['keywords'] as $kw) {
                if (str_contains($pName, $kw)) {
                    $matched[] = '- ' . $p['name'] . ' (' . number_format((float) ($p['price'] ?? 0), 0, ',', '.') . 'đ)';
                    break;
                }
            }
            if (count($matched) >= 3) {
                break;
            }
        }

        if (empty($matched)) {
            return null;
        }

        return $matchedEmotion['intro'] . "\n" . implode("\n", $matched) . "\nThích món nào cứ vào giỏ hàng nhé! ☕";
    }

    private function fallbackSearchProduct(string $normalizedAscii, array $snapshot): ?string
    {
        $products = array_values(array_filter((array) ($snapshot['products'] ?? []), static fn ($p) => is_array($p) && ($p['status'] ?? '') === 'available'));

        foreach ($products as $p) {
            $pName = Str::lower(Str::ascii((string) ($p['name'] ?? '')));
            if ($pName === '' || strlen($pName) < 4) {
                continue;
            }
            if (str_contains($normalizedAscii, $pName) || str_contains($pName, $normalizedAscii)) {
                $price = number_format((float) ($p['price'] ?? 0), 0, ',', '.');
                return $p['name'] . " giá {$price}đ nha anh/chị. Đặt ngay trong giỏ hàng cho nhanh! 😄";
            }
        }

        return null;
    }

    private function fallbackSuggestRandomCategory(array $snapshot): string
    {
        $menu = $snapshot['menu'] ?? [];
        if (empty($menu)) {
            return 'Bé đang bận xíu, anh/chị có thể xem menu trực tiếp để chọn nhé!';
        }

        // Chọn ngẫu nhiên một category
        $randomGroup = $menu[array_rand($menu)];
        $catName     = (string) ($randomGroup['category']['name'] ?? 'Món khác');
        $items       = array_slice((array) ($randomGroup['products'] ?? []), 0, 3);

        if (empty($items)) {
            return 'Bé đang bận xíu, anh/chị có thể xem menu trực tiếp để chọn nhé!';
        }

        $lines = [];
        foreach ($items as $p) {
            $lines[] = '- ' . ($p['name'] ?? '') . ' (' . number_format((float) ($p['price'] ?? 0), 0, ',', '.') . 'đ)';
        }

        return "Hôm nay bé gợi ý mục {$catName} cho anh/chị thử nha:\n" . implode("\n", $lines) . "\nNgón lắm đó! 🧋";
    }

    private function buildCompactMenuFallback(array $snapshot): ?string
    {
        $menu = $snapshot['menu'] ?? [];
        if (!is_array($menu) || empty($menu)) {
            return null;
        }

        $grouped = [];
        foreach ($menu as $group) {
            $cat = (string) ($group['category']['name'] ?? 'Khac');
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }

            $items = $group['products'] ?? [];
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $product) {
                if (count($grouped[$cat]) >= 4) {
                    break;
                }

                $grouped[$cat][] = (string) ($product['name'] ?? 'Mon')
                    . ' (' . number_format((float) ($product['price'] ?? 0), 0, ',', '.') . 'd)';
            }
        }

        $lines = ['Menu hiện tại của Choy\'s Cafe:'];
        foreach ($grouped as $cat => $items) {
            $lines[] = $cat . ': ' . implode(', ', $items);
        }
        $lines[] = 'Bạn muốn mình gợi ý theo khẩu vị để chọn nhanh hơn không?';

        return implode("\n", $lines);
    }

    private function fallbackSuggestByPreference(string $normalized, array $history = [], array $snapshot = []): ?string
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

        $categories = [];
        foreach (($snapshot['categories'] ?? []) as $category) {
            $categories[(int) ($category['id'] ?? 0)] = (string) ($category['name'] ?? '');
        }

        $products = array_values(array_filter((array) ($snapshot['products'] ?? []), static function ($product) {
            return is_array($product) && (($product['status'] ?? '') === 'available');
        }));

        if (empty($products)) {
            return 'Hiện mình chưa tải được danh sách món để gợi ý. Bạn thử lại sau ít phút nhe.';
        }

        $wantsCool = str_contains($context, 'nuoc mat') || str_contains($context, 'thanh mat');
        $wantsSweetSour = str_contains($context, 'ngot') && str_contains($context, 'chua');

        $preferredGroups = [];
        if ($wantsCool || $wantsSweetSour) {
            $preferredGroups = ['tra-va-thuc-uong-theo-mua', 'nuoc-ep', 'nuoc-ep-sinh-to'];
        }

        $scored = [];
        foreach ($products as $product) {
            $name = mb_strtolower((string) ($product['name'] ?? ''));
            $categoryName = $categories[(int) ($product['category_id'] ?? 0)] ?? '';
            $categorySlug = Str::slug($categoryName);
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
                'name' => (string) ($product['name'] ?? ''),
                'price' => (float) ($product['price'] ?? 0),
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

        return "Mình gợi ý 4 món hợp vị bạn:\n" . implode("\n", $lines)
            . "\nĐặt thử ngay trong giỏ hàng nha, ngon lắm đó! ☕";
    }

    private function buildDirectSuggestionFallback(array $snapshot, string $intro): string
    {
        // Ưu tiên top sellers
        $topSellers = $snapshot['top_sellers'] ?? [];
        $picks = [];
        foreach ($topSellers as $item) {
            $name = (string) ($item['name'] ?? '');
            if ($name === '') {
                continue;
            }
            // Tìm giá từ products
            $price = 0;
            foreach (($snapshot['products'] ?? []) as $p) {
                if (($p['name'] ?? '') === $name) {
                    $price = (float) ($p['price'] ?? 0);
                    break;
                }
            }
            $picks[] = '- ' . $name . ($price > 0 ? ' (' . number_format($price, 0, ',', '.') . 'đ)' : '');
            if (count($picks) >= 4) {
                break;
            }
        }

        // Fallback: lấy ngẫu nhiên từ products nếu top sellers thiếu
        if (count($picks) < 4) {
            $products = array_values(array_filter((array) ($snapshot['products'] ?? []), static fn($p) => is_array($p) && ($p['status'] ?? '') === 'available'));
            shuffle($products);
            foreach ($products as $p) {
                $name = (string) ($p['name'] ?? '');
                if ($name === '' || in_array('- ' . $name, array_map(fn($l) => substr($l, 0, strpos($l . ' (', ' (')), $picks), true)) {
                    continue;
                }
                $picks[] = '- ' . $name . ' (' . number_format((float) ($p['price'] ?? 0), 0, ',', '.') . 'đ)';
                if (count($picks) >= 4) {
                    break;
                }
            }
        }

        if (empty($picks)) {
            return $intro . ' Ủa menu chưa load được, thử F5 lại trang xem nha!';
        }

        return $intro . "\n" . implode("\n", $picks) . "\nThích món nào cứ cho bé biết nha! 😄";
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

    private function buildSystemPrompt(array $snapshot): string
    {
        ['menu' => $menuText, 'sizes' => $sizesText, 'extras' => $extrasText, 'bestsellers' => $bestsellerText] = $this->buildDynamicContext($snapshot);

        return <<<PROMPT
Bạn là Choy's AI, trợ lý thông minh của Choy's Cafe. Nhiệm vụ chính là hỗ trợ khách hàng hiểu về menu, tính toán tiền hàng, gợi ý món uống và hướng dẫn đặt hàng.

== NGÔN NGỮ ==
- LUÔN trả lời bằng tiếng Việt có dấu, rõ ràng, tự nhiên.
- Nếu khách nhắn bằng tiếng Anh hoặc ngôn ngữ khác,hãy trả lời bằng tiếng của khách.
- Không dùng tiếng Việt không dấu hoặc viết tắt kiểu chat trừ khi khách yêu cầu.

== PHẠM VI HỖ TRỢ ==
- Thông tin quán: giờ mở cửa, địa chỉ, liên hệ, chính sách hủy đơn
- Menu: danh sách món, giá, mô tả, hình thức tùy chỉnh (size, topping, đường, đá)
- Tính toán liên quan đến việc mua hàng: tổng tiền nhiều ly/món, so sánh giá, ước tính hóa đơn
- Gợi ý món theo khẩu vị, ngân sách, số người, thời tiết, tâm trạng, cảm xúc
- Hướng dẫn đặt món, thanh toán trong hệ thống

== GỢI Ý THEO CẢM XÚC ==
Khi khách chia sẻ tâm trạng hoặc hoàn cảnh, hãy dùng não để đọc vị cảm xúc rồi gợi ý món từ menu một cách thấu cảm và dí dỏm. Nguyên tắc map cảm xúc → khẩu vị:

Buồn / chia tay / thất tình → đắng nhẹ + chua dịu (cà phê đen, trà chanh, các món có vị chua): "Chia tay rồi thì uống gì cũng đắng, chi bằng uống đắng có chủ đích cho nó sang!"
Vui / có tin tốt / kỷ niệm → ngọt béo ấm áp (trà sữa, matcha latte, đá xay): "Hôm nay được điểm cao / thăng chức thì phải reward bản thân một ly ngọt xứng đáng chứ!"
Yêu đương / hẹn hò / crush → ngọt ngào lãng mạn (trà sữa, sinh tố, các món màu đẹp): "Hẹn hò thì phải order đôi cho nó đẹp cặp nha!"
Mệt mỏi / cần tỉnh táo / OT → cà phê mạnh (cà phê đen, espresso, bạc xỉu): "Não đang lag thì phải reboot bằng caffeine thôi!"
Căng thẳng / stress → thanh mát nhẹ nhàng (trà, nước ép, sinh tố): "Stress rồi thì cần gì mạnh, thanh mát một cái cho não nghỉ ngơi!"
Nắng nóng / oi bức → lạnh sảng khoái (đá xay, nước ép, sinh tố đá): "Trời nóng chảy mỡ thì phải đá nhiều, đá xay, đá cục gì cũng được!"
Trời mưa / lạnh → ấm nóng dễ chịu (cà phê nóng, trà nóng): "Mưa mà không có ly nóng trong tay thì phí cả buổi chiều!"

Quy trình gợi ý theo cảm xúc:
1. Nhận diện cảm xúc/hoàn cảnh từ lời khách
2. Map sang khẩu vị/tông vị phù hợp theo bảng trên
3. Tìm trong MENU HIỆN TẠI các món khớp nhất
4. Gợi ý 2-3 món cụ thể kèm giá, giải thích ngắn tại sao hợp với tâm trạng đó
5. Thêm câu hài nhẹ hoặc lời động viên phù hợp tâm trạng

== TÍNH TOÁN TIỀN HÀNG ==
- Được phép và PHẢI thực hiện mọi phép tính liên quan đến mua hàng tại quán (số lượng × đơn giá, tổng hóa đơn, tiền thừa, v.v.)
- Ví dụ: "10 ly Cà Phê Đen" → 10 × 25.000đ = 250.000đ. Trả lời rõ ràng, chính xác.
- Nếu tính có chọn size hoặc topping thì cộng thêm phụ thu tương ứng vào đơn giá trước khi nhân số lượng.
- KHÔNG được tự tạo đơn/bill chính thức, chỉ tính toán để khách tham khảo trước khi vào giỏ hàng.

== GIỚI HẠN ==
- Không trả lời chủ đề hoàn toàn ngoài phạm vi quán: chính trị, thể thao, thời tiết, lịch sử, khoa học phổ thông, v.v.
- Nếu câu hỏi không liên quan gì đến quán, trả lời: "Ngoài phạm vi thực hiện của bé, anh/chị có muốn chuyển sang nhắn với nhân viên để nhân viên trả lời không? ##ESCALATE##"
- Không tiết lộ thông tin nội bộ, cấu trúc database, code hay logic hệ thống.
- Không xác nhận hoặc tạo đơn hàng thay khách, không hiển thị QR thanh toán.

== PHONG CÁCH TRẢ LỜI ==
- Dí dỏm, hài hước, vui vẻ — như người bạn thân đang rủ đi uống cà phê chứ không phải robot trả lời tự động.
- Được phép dùng tiếng lóng thân thiện kiểu Gen Z ("oke bé", "ngon lành", "quẩy thôi", "hợp vị ghê") nhưng vẫn lịch sự, không thô tục.
- Thi thoảng thêm câu hài nhẹ hoặc emoji phù hợp để tạo không khí (☕ 🧋 😄) nhưng đừng spam.
- Khi gợi ý món, hãy "sale" nhiệt tình như nhân viên thực sự yêu menu của quán.
- Ngắn gọn, đúng trọng tâm — tránh dài dòng giáo khoa không cần thiết.
- Nếu khách chào thì chào lại vui vẻ trước khi hỗ trợ.
- Chỉ dùng văn bản thường, KHÔNG dùng ký hiệu markdown như ** __ * _ ` # ~~.
- Nếu không chắc thông tin, thú nhận thật thà thay vì bịa đặt.

== THÔNG TIN QUÁN ==
- Tên: Choy's Cafe
- Hotline: +84 0346901474
- Email: support@choy.cafe
- Giờ mở cửa: 8:00 - 24:00 hàng ngày
- Vị trí: Cao Đẳng Kỹ Thuật Du Lịch Sài Gòn (xem bản đồ trên trang chủ)
- Hủy đơn: trong vòng 5 phút sau khi đặt, khi đơn chưa được xác nhận

== KÍCH CỠ ==
{$sizesText}

== TOPPING & TÙY CHỈNH ==
{$extrasText}

== TOP 10 MÓN BÁN CHẠY ==
{$bestsellerText}

== MENU HIỆN TẠI ==
{$menuText}
PROMPT;
    }

    private function buildDynamicContext(array $snapshot): array
    {
        try {
            $sizes = $snapshot['sizes'] ?? [];
            $sizeLines = [];
            foreach ($sizes as $size) {
                $extraPrice = (float) ($size['extra_price'] ?? 0);
                $extra = $extraPrice > 0
                    ? '+' . number_format($extraPrice, 0, ',', '.') . 'd'
                    : 'khong phu thu';
                $sizeLines[] = '- Size ' . ($size['name'] ?? 'N/A') . ': ' . $extra;
            }

            $extras = $snapshot['extras'] ?? [];
            $extraGroups = [];
            foreach ($extras as $extra) {
                $typeLabel = match ($extra['type'] ?? null) {
                    'topping' => 'Topping',
                    'sugar' => 'Tuy chinh sua/duong',
                    'ice' => 'Tuy chinh da',
                    default => ucfirst((string) ($extra['type'] ?? 'khac')),
                };

                $extraPrice = (float) ($extra['price'] ?? 0);
                $price = $extraPrice > 0
                    ? '+' . number_format($extraPrice, 0, ',', '.') . 'd'
                    : 'mien phi';

                $extraGroups[$typeLabel][] = ($extra['name'] ?? 'Extra') . ' (' . $price . ')';
            }

            $extraLines = [];
            foreach ($extraGroups as $group => $items) {
                $extraLines[] = "[{$group}]: " . implode(', ', $items);
            }

            $menuGroups = $snapshot['menu'] ?? [];
            $menuLines = [];
            foreach ($menuGroups as $group) {
                $products = $group['products'] ?? [];
                if (!is_array($products) || empty($products)) {
                    continue;
                }

                $categoryName = (string) ($group['category']['name'] ?? 'Khac');
                $menuLines[] = "\n[{$categoryName}]";
                foreach ($products as $product) {
                    $price = number_format((float) ($product['price'] ?? 0), 0, ',', '.') . 'd';
                    $description = (string) ($product['description'] ?? '');
                    $menuLines[] = '- ' . ($product['name'] ?? 'Mon') . " ({$price}): {$description}";
                }
            }

            $topSellers = $snapshot['top_sellers'] ?? [];
            $bestsellerLines = [];
            foreach ($topSellers as $i => $item) {
                $rank = $i + 1;
                $bestsellerLines[] = $rank . '. ' . ($item['name'] ?? 'Mon')
                    . ' (da ban ' . (int) ($item['total_sold'] ?? 0) . ' lan)';
            }

            return [
                'menu' => implode("\n", $menuLines) ?: '(Chưa có sản phẩm)',
                'sizes' => implode("\n", $sizeLines) ?: '(Chưa có kích cỡ)',
                'extras' => implode("\n", $extraLines) ?: '(Chưa có topping)',
                'bestsellers' => implode("\n", $bestsellerLines) ?: '(Chưa có dữ liệu bán hàng)',
            ];
        } catch (\Exception $e) {
            return [
                'menu' => '(Không thể tải menu lúc này)',
                'sizes' => '(Không thể tải kích cỡ lúc này)',
                'extras' => '(Không thể tải topping lúc này)',
                'bestsellers' => '(Không thể tải dữ liệu bán hàng lúc này)',
            ];
        }
    }

    private function readSnapshot(): ?array
    {
        try {
            $path = storage_path('app/ai/DB.json');
            if (!File::exists($path)) {
                return null;
            }

            $raw = File::get($path);
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return null;
            }

            return $decoded;
        } catch (\Throwable $e) {
            Log::warning('Cannot read AI DB.json snapshot', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function normalizeSnapshot(?array $snapshot): ?array
    {
        if (!is_array($snapshot)) {
            return null;
        }

        $requiredKeys = ['categories', 'products', 'sizes', 'extras', 'menu'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $snapshot) || !is_array($snapshot[$key])) {
                return null;
            }
        }

        return $snapshot;
    }
}
