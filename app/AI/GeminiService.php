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
                'temperature' => 0.2,
                'maxOutputTokens' => 1500,
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

        $hasGio     = (bool) preg_match('/(gio mo cua|mo cua|may gio|thoi gian lam viec)/u', $normalizedAscii);
        $hasDiaChi  = (bool) preg_match('/(dia chi|o dau|vi tri|duong nao|quan may)/u', $normalizedAscii);
        if ($hasGio || $hasDiaChi) {
            $parts = [];
            if ($hasGio) {
                $parts[] = 'Quán mở cửa từ 8:00 đến 24:00 hàng ngày nha anh/chị.';
            }
            if ($hasDiaChi) {
                $parts[] = 'Địa điểm hiện đang được ghim trên bản đồ của Choy\'s Cafe là khu vực Cao Đẳng Kỹ Thuật Du Lịch Sài Gòn. Anh/chị có thể xem map ngay trên trang chủ để mở đường đi nhanh nha.';
            }
            return implode(' ', $parts);
        }

        if (preg_match('/(diem thuong|loyalty|tich diem|dung diem|doi diem|diem thuong la gi|kiem diem|diem cua toi|bao nhieu diem|diem ton tai|su dung diem|co \d+ diem|diem dung duoc|diem giam duoc)/u', $normalizedAscii)) {
            // Nếu hỏi cụ thể: "có X điểm dùng được bao nhiêu" → tính luôn
            if (preg_match('/co\s+(\d+)\s+diem/u', $normalizedAscii, $m)) {
                $pts = (int) $m[1];
                return "Với {$pts} điểm, anh/chị giảm được tối đa " . number_format($pts, 0, ',', '.') . "đ (1 điểm = 1đ).\n"
                    . "Lưu ý: mỗi đơn chỉ được dùng tối đa 10% giá trị đơn nha anh/chị!\n"
                    . "Ví dụ đơn 200.000đ → dùng tối đa 20.000 điểm, dù có nhiều hơn cũng chỉ trừ 20.000đ thôi. 😊";
            }
            return "Hệ thống điểm thưởng của Choy's hoạt động như sau:\n"
                . "- Kiếm điểm: mỗi 10đ thanh toán thực tế = 1 điểm. Ví dụ đơn 150.000đ → nhận 15.000 điểm ngay sau khi đặt.\n"
                . "- Dùng điểm: 1 điểm = giảm 1đ, chọn \"Dùng điểm\" khi thanh toán trong giỏ hàng.\n"
                . "- Giới hạn dùng mỗi đơn:\n"
                . "  Đơn từ 300.000đ trở lên: tối đa 10% giá trị đơn.\n"
                . "  Đơn dưới 300.000đ: tối đa phần lẻ để làm tròn (vd: đơn 175.000đ → dùng tối đa 5.000 điểm).\n"
                . "- Cần đăng nhập mới tích và dùng điểm được nha.\n"
                . "Điểm không có hạn dùng, không quy đổi tiền mặt nhé anh/chị! 😊";
        }

        if (preg_match('/(khieu nai|buc xuc|that vong|giao cham|nhan vien|quan ly)/u', $normalizedAscii)) {
            return 'Xin lỗi vì sự bất tiện này. Bạn có muốn mình kết nối ngay với nhân viên hỗ trợ không? ##ESCALATE##';
        }

        if (preg_match('/\b(bong da|chinh tri|thoi tiet|lap trinh|toan hoc|giai toan|ngan hang|tai khoan|vietcombank|mbbank|techcombank|bidv|agribank|atm|dang ky|mat khau|password|hack|crack|bot|malware|virus|kiem tien|dau tu|chung khoan|crypto|bitcoin|benh vien|thuoc|bac si|y te)\b/u', $normalizedAscii)) {
            return $this->outOfScopeEscalationReply();
        }

        if (preg_match('/(full menu|toan bo menu|xem menu|liet ke menu)/u', $normalizedAscii)) {
            $menu = $this->buildCompactMenuFallback($snapshot);
            if ($menu !== null) {
                return $menu;
            }
        }

        // --- Gợi ý theo cảm xúc (ưu tiên trước preference vì emotion cụ thể hơn) ---
        $emotionSuggestion = $this->fallbackSuggestByEmotion($normalizedAscii, $snapshot);
        if ($emotionSuggestion !== null) {
            return $emotionSuggestion;
        }

        // --- Gợi ý theo khẩu vị nếu user đã nói rõ ---
        $preferenceSuggestion = $this->fallbackSuggestByPreference($normalized, $history, $snapshot);
        if ($preferenceSuggestion !== null) {
            return $preferenceSuggestion;
        }

        // --- Tính tổng đơn hàng nhiều món ---
        $calcResult = $this->fallbackCalculateOrder($normalizedAscii, $snapshot);
        if ($calcResult !== null) {
            return $calcResult;
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
            'chia tay|thất tình|buồn|cô đơn|khóc' => [
                'keywords' => ['cà phê đen', 'trà chanh', 'espresso', 'bạc xỉu', 'cà phê sữa'],
                'intro'    => 'Chia tay rồi à? Uống gì cũng đắng, chi bằng uống đắng có chủ đích cho nó sang nhỉ! Bé gợi ý:',
            ],
            'vui|hạnh phúc|tin tốt|thắng|đạt|sinh nhật|kỷ niệm' => [
                'keywords' => ['trà sữa', 'matcha', 'latte', 'đá xay', 'sinh tố'],
                'intro'    => 'Ngày vui thì phải thưởng mình một thứ ngọt xứng đáng chứ! Bé gợi ý:',
            ],
            'hẹn hò|crush|yêu|người thương|đặt hẹn' => [
                'keywords' => ['trà sữa', 'sinh tố', 'matcha', 'latte', 'đá xay'],
                'intro'    => 'Hẹn hò thì phải order đôi cho đẹp cặp nha! Bé gợi ý:',
            ],
            'mệt|buồn ngủ|cần tỉnh|ot|thức đêm|cày' => [
                'keywords' => ['cà phê', 'espresso', 'bạc xỉu', 'latte', 'cà phê đen'],
                'intro'    => 'Não đang lag thì phải reboot bằng caffeine thôi! Bé gợi ý:',
            ],
            'stress|áp lực|lo lắng|căng thẳng' => [
                'keywords' => ['trà', 'nước ép', 'sinh tố', 'matcha'],
                'intro'    => 'Stress rồi thì cần gì thanh mát, nhẹ nhàng cho não nghỉ ngơi! Bé gợi ý:',
            ],
            'nóng|nắng|oi bức|nóng quá' => [
                'keywords' => ['đá xay', 'nước ép', 'sinh tố', 'trà đá'],
                'intro'    => 'Trời nóng chảy mỡ rồi, phải đá nhiều thôi! Bé gợi ý:',
            ],
            'mưa|lạnh|se se' => [
                'keywords' => ['cà phê nóng', 'trà nóng', 'latte', 'bạc xỉu'],
                'intro'    => 'Mưa mà không có ly nóng trong tay thì phí cả buổi chiều! Bé gợi ý:',
            ],
        ];

        $matchedEmotion = null;
        foreach ($emotionMap as $pattern => $config) {
            // Normalize pattern về ASCII để so với $normalizedAscii (input đã bỏ dấu)
            $asciiPattern = Str::lower(Str::ascii($pattern));
            if (preg_match('/(' . $asciiPattern . ')/u', $normalizedAscii)) {
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
                if (str_contains($pName, Str::lower(Str::ascii($kw)))) {
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

    private function fallbackCalculateOrder(string $normalizedAscii, array $snapshot): ?string
    {
        // Chỉ kích hoạt khi có từ khóa tính tiền
        if (!preg_match('/(bao nhieu|tinh tien|het bao|tong tien|tong cong|tinh thu|cuoi cung|gom lai|thanh tien)/u', $normalizedAscii)) {
            return null;
        }

        $products = array_values(array_filter((array) ($snapshot['products'] ?? []), static fn ($p) => is_array($p) && ($p['status'] ?? '') === 'available'));
        if (empty($products)) {
            return null;
        }

        // Tách các đoạn theo "va", "và", ",", "+"
        $segments = preg_split('/(\bva\b|,|\+)/u', $normalizedAscii);
        if (!$segments) {
            return null;
        }

        $lines = [];
        $total = 0;
        $notFound = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);
            // Tìm số lượng + tên món: "5 ly ca phe den" hoặc "3 tra sua"
            if (!preg_match('/^(\d+)\s+(?:ly\s+|coc\s+|phan\s+|chiec\s+|cai\s+)?(.+)/u', $segment, $m)) {
                continue;
            }
            $qty     = (int) $m[1];
            $keyword = trim($m[2]);
            // Bỏ phần hỏi cuối: "thi het bao nhieu", "tong..."
            $keyword = trim((string) preg_replace('/(\b(thi|het|la|bao nhieu|tong|cong|tinh)\b.*)/u', '', $keyword));
            if ($keyword === '' || $qty <= 0) {
                continue;
            }

            // Tìm sản phẩm khớp nhất: ưu tiên substring match trước, rồi mới similar_text
            $bestMatch = null;
            $bestScore = 0.0;
            foreach ($products as $p) {
                $pNameAscii = Str::lower(Str::ascii((string) ($p['name'] ?? '')));
                // Substring match được cộng thêm 50 điểm để luôn thắng similar_text thuần
                $bonus = str_contains($pNameAscii, $keyword) ? 50.0 : 0.0;
                similar_text($keyword, $pNameAscii, $pct);
                $score = $pct + $bonus;
                if ($score > $bestScore && ($pct > 38 || $bonus > 0)) {
                    $bestScore = $score;
                    $bestMatch = $p;
                }
            }

            if ($bestMatch === null) {
                $notFound[] = $keyword;
                continue;
            }

            $price    = (float) ($bestMatch['price'] ?? 0);
            $subtotal = $qty * $price;
            $total   += $subtotal;
            $lines[]  = "- {$qty} ly {$bestMatch['name']}: {$qty} × " . number_format($price, 0, ',', '.') . 'đ = ' . number_format($subtotal, 0, ',', '.') . 'đ';
        }

        if (empty($lines)) {
            return null;
        }

        $reply = implode("\n", $lines) . "\n=> Tổng cộng: " . number_format($total, 0, ',', '.') . 'đ';

        if (!empty($notFound)) {
            $reply .= "\n(Bé chưa tìm được: " . implode(', ', $notFound) . " — anh/chị kiểm tra lại tên món trong menu nha)";
        }

        return $reply . "\nMời anh/chị vào giỏ hàng để đặt nha! 🧾";
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

                $grouped[$cat][] = (string) ($product['name'] ?? 'Món')
                    . ' (' . number_format((float) ($product['price'] ?? 0), 0, ',', '.') . 'đ)';
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

        $wantsFood = preg_match('/(muon an|đói|doi|an gi|an cai gi|an banh|banh gi|do an|an nhẹ|an nhe)/u', $context)
            || preg_match('/(muon an|doi|an gi|an cai gi|an banh|banh gi|do an|an nhe)/u', Str::ascii($context));

        $isAskingRecommendation =
            $wantsFood
            || preg_match('/(goi y mon|goi y gi|uong gi ngon|muon uong|uong gi di)/u', $context)
            || str_contains($context, 'nuoc mat')
            || str_contains($context, 'thanh mat')
            || (str_contains($context, 'ngot') && str_contains($context, 'uong'))
            || (str_contains($context, 'chua') && str_contains($context, 'uong'));

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

        // Khi khách muốn ăn: ưu tiên các món có tên liên quan đến bánh/đồ ăn
        if ($wantsFood) {
            $foodKeywords = ['banh', 'bánh', 'cake', 'toast', 'sandwich', 'waffle', 'cookie', 'muffin', 'croissant', 'snack', 'an'];
            $foodItems = [];
            foreach ($products as $product) {
                $nameAscii = Str::lower(Str::ascii((string) ($product['name'] ?? '')));
                $catName   = Str::lower(Str::ascii($categories[(int) ($product['category_id'] ?? 0)] ?? ''));
                foreach ($foodKeywords as $kw) {
                    if (str_contains($nameAscii, $kw) || str_contains($catName, $kw)) {
                        $foodItems[] = $product;
                        break;
                    }
                }
            }
            if (!empty($foodItems)) {
                $picks = array_slice($foodItems, 0, 4);
                $lines = [];
                foreach ($picks as $item) {
                    $lines[] = '- ' . $item['name'] . ' (' . number_format((float) ($item['price'] ?? 0), 0, ',', '.') . 'đ)';
                }
                return "Muốn ăn thì bé gợi ý mấy món này nè:\n" . implode("\n", $lines)
                    . "\nVào giỏ hàng đặt luôn nha! 🍰";
            }
            // Không có đồ ăn trong menu → thông báo thật thà
            return 'Hiện tại quán mình chủ yếu phục vụ đồ uống, chưa có đồ ăn trong menu anh/chị ơi. Để bé gợi ý món nước hợp vị nhé?';
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
            $lines[] = '- ' . $item['name'] . ' (' . number_format($item['price'], 0, ',', '.') . 'đ)';
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
[QUY TẮC BẮT BUỘC — ĐỌC TRƯỚC KHI LÀM BẤT CỨ ĐIỀU GÌ]
1. MỌI câu trả lời PHẢI viết bằng tiếng Việt CÓ DẤU đầy đủ, chính xác. Không được bỏ dấu, không viết tắt kiểu "ko", "dc", "vs" trừ khi chính khách dùng cách đó trước.
2. Không dùng ký hiệu markdown: không **, không __, không *, không _, không `, không #, không ~~.
3. Chỉ dùng văn bản thuần, ngắn gọn, tự nhiên.
4. Nếu khách nhắn tiếng Anh hoặc ngôn ngữ khác thì trả lời đúng ngôn ngữ đó, vẫn không dùng markdown.

Bạn là Choy's AI, trợ lý thông minh của Choy's Cafe. Nhiệm vụ chính là hỗ trợ khách hàng về menu, tính tiền, gợi ý món và hướng dẫn đặt hàng.

== PHONG CÁCH GIAO TIẾP ==
- Dí dỏm, thân thiện, tự nhiên — như người bạn đang rủ uống cà phê, không phải robot đọc kịch bản.
- Được dùng tiếng lóng thân thiện Gen Z ("oke nha", "ngon lành", "quẩy thôi", "hợp vị ghê") nhưng vẫn lịch sự.
- Thi thoảng thêm emoji phù hợp (☕ 🧋 😄) nhưng không spam.
- Khi gợi ý món: sale nhiệt tình như nhân viên thực sự yêu menu quán.
- Ngắn gọn, đúng trọng tâm, không dài dòng.
- Nếu khách chào thì chào lại vui vẻ trước.
- Nếu không chắc thông tin thì thú nhận thật thà thay vì bịa.
- Nếu khách hỏi mơ hồ (ví dụ: "cho mình xem"), hãy hỏi lại một câu ngắn để hiểu rõ ý hơn thay vì đoán bừa.
- Nhớ ngữ cảnh hội thoại: nếu khách đã đề cập khẩu vị hoặc tình huống trước đó, hãy dùng thông tin đó để gợi ý thay vì hỏi lại từ đầu.
- Nếu khách tỏ ra không hài lòng hoặc phàn nàn, hãy xin lỗi chân thành trước rồi mới giải quyết vấn đề.
- Sau khi gợi ý xong, chủ động hỏi thêm một câu dẫn dắt ngắn để duy trì cuộc trò chuyện (ví dụ: "Anh/chị muốn thêm topping gì không?" hoặc "Mình uống nóng hay đá ạ?").

== PHẠM VI HỖ TRỢ ==
- Thông tin quán: giờ mở cửa, địa chỉ, liên hệ, chính sách hủy đơn
- Menu: danh sách món, giá, mô tả, tùy chỉnh (size, topping, đường, đá)
- Tính toán mua hàng: tổng tiền nhiều ly/món, so sánh giá, ước tính hóa đơn
- Gợi ý món theo khẩu vị, ngân sách, số người, thời tiết, tâm trạng, cảm xúc
- Hướng dẫn đặt món, thanh toán trong hệ thống

== GỢI Ý ĐỒ ĂN ==
Khi khách nói "muốn ăn", "đói", "ăn gì", "ăn bánh" hoặc hỏi về đồ ăn:
- Tìm trong MENU HIỆN TẠI các món có tên hoặc danh mục liên quan đến bánh, đồ ăn, snack.
- Nếu có: gợi ý ngay 2-3 món kèm giá.
- Nếu menu hiện tại KHÔNG có đồ ăn: trả lời thật thà "Hiện quán mình chủ yếu phục vụ đồ uống, chưa có đồ ăn trong menu" rồi hỏi xem khách có muốn gợi ý đồ uống không.
- KHÔNG được tự ý gợi ý đồ uống khi khách hỏi đồ ăn mà không giải thích trước.

== GỢI Ý THEO CẢM XÚC ==
Khi khách chia sẻ tâm trạng, đọc vị cảm xúc rồi gợi ý món thấu cảm và dí dỏm:

Buồn / chia tay / thất tình → đắng nhẹ + chua dịu (cà phê đen, trà chanh): "Chia tay rồi thì uống gì cũng đắng, chi bằng uống đắng có chủ đích cho nó sang!"
Vui / tin tốt / kỷ niệm → ngọt béo ấm áp (trà sữa, matcha latte, đá xay): "Hôm nay vui thì phải thưởng mình một ly ngọt xứng đáng chứ!"
Hẹn hò / crush / yêu → ngọt lãng mạn (trà sữa, sinh tố, món màu đẹp): "Hẹn hò thì phải order đôi cho đẹp cặp nha!"
Mệt / buồn ngủ / OT / cần tỉnh → cà phê mạnh (cà phê đen, espresso, bạc xỉu): "Não đang lag thì phải reboot bằng caffeine thôi!"
Stress / căng thẳng / lo lắng → thanh mát nhẹ (trà, nước ép, sinh tố): "Stress rồi thì thanh mát một cái cho não nghỉ ngơi!"
Nắng nóng / oi bức → lạnh sảng khoái (đá xay, nước ép, sinh tố đá): "Trời nóng chảy mỡ thì phải đá nhiều thôi!"
Trời mưa / lạnh / se se → ấm nóng (cà phê nóng, trà nóng): "Mưa mà không có ly nóng trong tay thì phí cả buổi chiều!"

Quy trình khi gợi ý theo cảm xúc:
1. Nhận diện cảm xúc/hoàn cảnh từ lời khách
2. Map sang khẩu vị phù hợp
3. Tìm trong MENU HIỆN TẠI các món khớp nhất
4. Gợi ý 2-3 món kèm giá, giải thích ngắn tại sao hợp tâm trạng
5. Thêm câu hài nhẹ hoặc lời động viên, rồi hỏi thêm một câu dẫn dắt

== TÍNH TOÁN TIỀN HÀNG ==
- PHẢI thực hiện mọi phép tính mua hàng (số lượng × đơn giá, tổng hóa đơn, tiền thừa).
- Nếu có chọn size hoặc topping thì cộng phụ thu vào đơn giá trước khi nhân số lượng.
- Ví dụ: "10 ly Cà Phê Đen" → 10 × 25.000đ = 250.000đ. Trả lời rõ ràng, chính xác.
- KHÔNG tự tạo đơn/bill chính thức, chỉ tính để khách tham khảo.

== GIỚI HẠN ==
- Không trả lời chủ đề ngoài phạm vi quán: chính trị, thể thao, thời tiết, lịch sử, khoa học, v.v.
- Nếu không liên quan đến quán: "Ngoài phạm vi thực hiện của bé, anh/chị có muốn chuyển sang nhắn với nhân viên không? ##ESCALATE##"
- Không tiết lộ thông tin nội bộ, database, code, logic hệ thống.
- Không xác nhận hoặc tạo đơn hàng thay khách, không hiển thị QR thanh toán.

== ĐIỂM THƯỞNG (LOYALTY POINTS) ==
- Cách kiếm: mỗi 10đ thanh toán thực tế = 1 điểm (ví dụ: đơn 150.000đ → nhận 15.000 điểm). Điểm được cộng ngay sau khi đặt hàng thành công.
- Cách dùng: 1 điểm = giảm 1đ, chọn "Dùng điểm" khi thanh toán trong giỏ hàng.
- Giới hạn dùng mỗi đơn:
  Đơn từ 300.000đ trở lên: dùng tối đa 10% giá trị đơn.
  Đơn dưới 300.000đ: dùng tối đa 10% hoặc phần lẻ để làm tròn đơn (ví dụ: đơn 175.000đ → dùng tối đa 5.000 điểm để thành 170.000đ).
- Điều kiện: phải đăng nhập mới tích/dùng điểm được.
- Điểm không có hạn sử dụng, không quy đổi thành tiền mặt.

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

[NHẮC LẠI QUY TẮC QUAN TRỌNG NHẤT: Luôn viết tiếng Việt có dấu đầy đủ trong mọi câu trả lời.]
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
                    ? '+' . number_format($extraPrice, 0, ',', '.') . 'đ'
                    : 'không phụ thu';
                $sizeLines[] = '- Size ' . ($size['name'] ?? 'N/A') . ': ' . $extra;
            }

            $extras = $snapshot['extras'] ?? [];
            $extraGroups = [];
            foreach ($extras as $extra) {
                $typeLabel = match ($extra['type'] ?? null) {
                    'topping' => 'Topping',
                    'sugar'   => 'Tùy chỉnh sữa/đường',
                    'ice'     => 'Tùy chỉnh đá',
                    default   => ucfirst((string) ($extra['type'] ?? 'Khác')),
                };

                $extraPrice = (float) ($extra['price'] ?? 0);
                $price = $extraPrice > 0
                    ? '+' . number_format($extraPrice, 0, ',', '.') . 'đ'
                    : 'miễn phí';

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

                $categoryName = (string) ($group['category']['name'] ?? 'Khác');
                $menuLines[] = "\n[{$categoryName}]";
                foreach ($products as $product) {
                    $price = number_format((float) ($product['price'] ?? 0), 0, ',', '.') . 'đ';
                    $description = (string) ($product['description'] ?? '');
                    $menuLines[] = '- ' . ($product['name'] ?? 'Món') . " ({$price}): {$description}";
                }
            }

            $topSellers = $snapshot['top_sellers'] ?? [];
            $bestsellerLines = [];
            foreach ($topSellers as $i => $item) {
                $rank = $i + 1;
                $bestsellerLines[] = $rank . '. ' . ($item['name'] ?? 'Món')
                    . ' (đã bán ' . (int) ($item['total_sold'] ?? 0) . ' lần)';
            }

            return [
                'menu'        => implode("\n", $menuLines) ?: '(Chưa có sản phẩm)',
                'sizes'       => implode("\n", $sizeLines) ?: '(Chưa có kích cỡ)',
                'extras'      => implode("\n", $extraLines) ?: '(Chưa có topping)',
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
