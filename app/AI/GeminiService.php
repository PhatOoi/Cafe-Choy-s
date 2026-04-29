<?php

namespace App\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
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

    /**
     * Gửi tin nhắn đến Gemini và trả về phản hồi.
     *
     * @param string $message    Tin nhắn của người dùng
     * @param array  $history    Lịch sử hội thoại [{role: 'user'|'model', text: '...'}]
     * @return string
     */
    public function chat(string $message, array $history = []): string
    {
        if (empty($this->apiKey)) {
            return 'Xin lỗi, tính năng AI Chat chưa được cấu hình. Vui lòng liên hệ quản trị viên.';
        }

        $systemPrompt = $this->buildSystemPrompt();
        // Chuyển lịch sử thành định dạng Gemini yêu cầu
        $contents = [];
        foreach ($history as $turn) {
            $contents[] = [
                'role'  => $turn['role'],
                'parts' => [['text' => $turn['text']]],
            ];
        }

        // Thêm tin nhắn hiện tại của người dùng
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents'           => $contents,
            'generationConfig'   => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::timeout(15)
                ->post("{$this->apiUrl}?key={$this->apiKey}", $payload);

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

            return $this->fallbackReply($message, $history);
        }
    }

    private function fallbackReply(string $message, array $history = []): string
    {
        $normalized = mb_strtolower(trim($message));
        $normalizedAscii = Str::lower(Str::ascii($normalized));

        if (preg_match('/(khieu nai|khiếu nại|buc xuc|bức xúc|that vong|thất vọng|giao cham|giao chậm|nhan vien|nhân viên|quan ly|quản lý)/u', $normalizedAscii)) {
            return 'Xin lỗi vì sự bất tiện này. Bạn có muốn mình kết nối ngay với nhân viên hỗ trợ không? ##ESCALATE##';
        }

        if (preg_match('/\b(bong da|chinh tri|thoi tiet|lap trinh|toan hoc|giai toan)\b/u', $normalizedAscii)) {
            return 'Mình chỉ hỗ trợ các nội dung liên quan Choy\'s Cafe như menu, giá, đặt món và thanh toán. Bạn muốn mình tư vấn món nào hôm nay?';
        }

        if (preg_match('/(da chuyen khoan|đã chuyển khoản|chuyen khoan roi|chuyển khoản rồi|toi da ck|tôi đã ck)/u', $normalizedAscii)) {
            return 'Mình đã ghi nhận bạn báo chuyển khoản. Nhân viên sẽ đối chiếu và xác nhận sớm nhất. Bạn có thể theo dõi trạng thái trong lịch sử đơn hàng.';
        }

        if (preg_match('/(full menu|toan bo menu|toàn bộ menu|xem menu|liet ke menu|liệt kê menu)/u', $normalizedAscii)) {
            $menu = $this->buildCompactMenuFallback();
            if ($menu !== null) {
                return $menu;
            }
        }

        // Fallback mạnh cho luồng đặt món khi API tạm thời lỗi.
        if (preg_match('/\b(mua|dat|đặt|order|goi|gọi)\b/u', $normalized)) {
            $draft = $this->buildOrderDraftFromMessage($message);

            if ($draft === null) {
                return 'Mình đang gặp lỗi kết nối AI, nhưng vẫn có thể hỗ trợ đặt món. Bạn vui lòng gửi lại theo mẫu: "mua 2 ly Tên Món size M".';
            }

            $item = $draft['items'][0];
            $line = ($item['qty'] ?? 1) . ' x ' . ($item['name'] ?? 'Món đã chọn');
            if (!empty($item['size'])) {
                $line .= ' (size ' . $item['size'] . ')';
            }

            if (($draft['qty_over_limit'] ?? false) === true) {
                return 'Mình đã hiểu món bạn chọn: ' . $line . '. Hiện mỗi món tối đa 10 ly/lần đặt, bạn giúp mình giảm số lượng xuống tối đa 10 để mình lên bill nhé.';
            }

            $paymentMethod = $draft['payment_method'] ?? null;
            if (!$paymentMethod) {
                return 'Mình đã ghi nhận bill tạm: ' . $line . '. Bạn chọn thanh toán tiền mặt hay chuyển khoản QR để mình lên bill xác nhận nhé?';
            }

            $payLabel = $paymentMethod === 'bank_transfer' ? 'chuyển khoản QR' : 'tiền mặt';
            $json = json_encode([
                'items' => $draft['items'],
                'payment_method' => $paymentMethod,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return 'Mình đã lên bill nháp: ' . $line . '. Thanh toán: ' . $payLabel . '. Nếu đúng, bạn bấm Xác nhận đặt đơn giúp mình.'
                . "\n##ORDER_DRAFT##" . $json;
        }

        $preferenceSuggestion = $this->fallbackSuggestByPreference($normalized, $history);
        if ($preferenceSuggestion !== null) {
            return $preferenceSuggestion;
        }

        if (empty($history)) {
            return 'Xin chào, mình là Choy AI. Hiện kết nối AI hơi chậm, bạn cứ hỏi menu hoặc đặt món, mình vẫn hỗ trợ bạn bình thường.';
        }

        return 'Hiện kết nối AI đang gián đoạn tạm thời. Bạn có thể nói khẩu vị (ví dụ: ngọt chua, thanh mát, ít ngọt) hoặc tên món muốn uống, mình vẫn gợi ý và hỗ trợ đặt đơn được.';
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
                $grouped[$cat][] = (string) $row->name . ' (' . number_format((float) $row->price, 0, ',', '.') . 'đ)';
            }
        }

        $lines = ['Menu hiện tại của Choy\'s Cafe:'];
        foreach ($grouped as $cat => $items) {
            $lines[] = $cat . ': ' . implode(', ', $items);
        }
        $lines[] = 'Bạn muốn mình gợi ý theo khẩu vị để chọn nhanh hơn không?';

        return implode("\n", $lines);
    }

    private function fallbackSuggestByPreference(string $normalized, array $history = []): ?string
    {
        $context = $normalized . ' ' . mb_strtolower($this->latestUserMessages($history, 3));

        $isAskingRecommendation =
            str_contains($context, 'gợi ý')
            || str_contains($context, 'goi y')
            || str_contains($context, 'uống gì')
            || str_contains($context, 'uong gi')
            || str_contains($context, 'muốn uống')
            || str_contains($context, 'muon uong')
            || str_contains($context, 'nước mát')
            || str_contains($context, 'nuoc mat')
            || str_contains($context, 'ngọt')
            || str_contains($context, 'ngot')
            || str_contains($context, 'chua')
            || str_contains($context, 'thanh mát')
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
            return 'Hiện mình chưa tải được danh sách món để gợi ý. Bạn thử lại sau ít phút nhé.';
        }

        $wantsCool = str_contains($context, 'nước mát') || str_contains($context, 'nuoc mat') || str_contains($context, 'thanh mát') || str_contains($context, 'thanh mat');
        $wantsSweetSour = (str_contains($context, 'ngọt') || str_contains($context, 'ngot')) && str_contains($context, 'chua');

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
                str_contains($name, 'trà') || str_contains($name, 'tea') || str_contains($name, 'ép') || str_contains($name, 'sinh tố') || str_contains($name, 'trái cây')
            )) {
                $score += 2;
            }

            if ($wantsCool && (
                str_contains($name, 'trà') || str_contains($name, 'ép') || str_contains($name, 'sinh tố') || str_contains($name, 'đá xay')
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
            $lines[] = '- ' . $item['name'] . ' (' . number_format($item['price'], 0, ',', '.') . 'đ)';
        }

        return "Mình gợi ý 4 món hợp vị bạn:\n" . implode("\n", $lines)
            . "\nBạn muốn mình lên bill nháp món nào luôn không?";
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

    private function buildOrderDraftFromMessage(string $message): ?array
    {
        $normalized = mb_strtolower($message);
        $normalizedAscii = Str::lower(Str::ascii($normalized));

        preg_match('/(\d{1,2})\s*(ly|coc|cốc|chai|phan|phần)?/u', $normalizedAscii, $qtyMatch);
        $qty = isset($qtyMatch[1]) ? max(1, (int) $qtyMatch[1]) : 1;
        $qtyOverLimit = $qty > 10;
        if ($qtyOverLimit) {
            $qty = 10;
        }

        $size = null;
        if (preg_match('/size\s*(s|m|l|small|medium|large)/iu', $normalized, $sizeMatch)) {
            $rawSize = mb_strtolower($sizeMatch[1]);
            $size = match ($rawSize) {
                'small', 's' => 'S',
                'medium', 'm' => 'M',
                'large', 'l' => 'L',
                default => strtoupper($rawSize),
            };
        }

        $ice = null;
        if (str_contains($normalized, 'ít đá') || str_contains($normalizedAscii, 'it da')) {
            $ice = 'Ít Đá';
        } elseif (str_contains($normalized, 'nhiều đá') || str_contains($normalizedAscii, 'nhieu da')) {
            $ice = 'Nhiều Đá';
        } elseif (str_contains($normalized, 'đá riêng') || str_contains($normalizedAscii, 'da rieng')) {
            $ice = 'Đá Riêng';
        } elseif (str_contains($normalized, 'da vua') || str_contains($normalizedAscii, 'da vua') || str_contains($normalized, 'đá vừa')) {
            $ice = 'Ít Đá';
        }

        $sugar = null;
        if (str_contains($normalized, 'ít đường') || str_contains($normalizedAscii, 'it duong')) {
            $sugar = 'Ít Đường';
        } elseif (str_contains($normalized, 'nhiều đường') || str_contains($normalizedAscii, 'nhieu duong')) {
            $sugar = 'Nhiều Đường';
        } elseif (str_contains($normalized, 'không đường') || str_contains($normalizedAscii, 'khong duong')) {
            $sugar = 'Không Đường';
        } elseif (str_contains($normalizedAscii, 'duong bth') || str_contains($normalized, 'đường bth') || str_contains($normalized, 'đường bình thường')) {
            $sugar = 'Nhiều Đường';
        }

        $paymentMethod = null;
        if (str_contains($normalized, 'qr') || str_contains($normalized, 'chuyển khoản') || str_contains($normalized, 'chuyen khoan')) {
            $paymentMethod = 'bank_transfer';
        } elseif (str_contains($normalized, 'tiền mặt') || str_contains($normalized, 'tien mat') || str_contains($normalized, 'cash')) {
            $paymentMethod = 'cash';
        }

        $product = $this->resolveProductFromMessage($normalized);
        if (!$product) {
            return null;
        }

        return [
            'items' => [[
                'product_id' => (int) $product->id,
                'name' => (string) $product->name,
                'qty' => $qty,
                'size' => $size,
                'sugar' => $sugar,
                'ice' => $ice,
                'toppings' => [],
                'note' => null,
            ]],
            'payment_method' => $paymentMethod,
            'qty_over_limit' => $qtyOverLimit,
        ];
    }

    private function resolveProductFromMessage(string $normalizedMessage): ?object
    {
        $normalizedAscii = Str::lower(Str::ascii($normalizedMessage));

        $products = DB::table('products')
            ->where('status', 'available')
            ->get(['id', 'name', 'description']);

        if ($products->isEmpty()) {
            return null;
        }

        $aliases = [
            'trà đào' => ['peach tea', 'trà đào', 'tra dao'],
            'tra dao' => ['peach tea', 'trà đào', 'tra dao'],
            'peach tea' => ['peach tea', 'trà đào', 'tra dao'],
        ];

        $keywords = [];
        foreach ($aliases as $needle => $mapped) {
            if (str_contains($normalizedMessage, $needle)) {
                $keywords = array_merge($keywords, $mapped);
            }
        }

        foreach ($products as $product) {
            $name = mb_strtolower((string) $product->name);
            $nameAscii = Str::lower(Str::ascii($name));
            if (str_contains($normalizedMessage, $name) || str_contains($normalizedAscii, $nameAscii)) {
                return $product;
            }
        }

        if (!empty($keywords)) {
            foreach ($products as $product) {
                $hay = mb_strtolower((string) $product->name . ' ' . (string) $product->description);
                $hayAscii = Str::lower(Str::ascii($hay));
                foreach ($keywords as $kw) {
                    $kwLower = mb_strtolower($kw);
                    $kwAscii = Str::lower(Str::ascii($kw));
                    if (str_contains($hay, $kwLower) || str_contains($hayAscii, $kwAscii)) {
                        return $product;
                    }
                }
            }
        }

        foreach ($products as $product) {
            $description = mb_strtolower((string) $product->description);
            $descriptionAscii = Str::lower(Str::ascii($description));
            if ($description !== '' && (str_contains($normalizedMessage, $description) || str_contains($normalizedAscii, $descriptionAscii))) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Xây dựng system prompt với dữ liệu thực tế từ DB (menu, sizes, extras).
     * Mỗi lần gọi đều query DB mới — đảm bảo luôn phản ánh trạng thái hiện tại.
     */
    private function buildSystemPrompt(): string
    {
        ['menu' => $menuText, 'sizes' => $sizesText, 'extras' => $extrasText] = $this->buildDynamicContext();

        return <<<PROMPT
Bạn là trợ lý AI của Choy's Cafe, tên là "Choy's AI". Bạn CHỈ được trả lời các câu hỏi liên quan đến quán cà phê Choy's Cafe.

PHẠM VI ĐƯỢC TRẢ LỜI:
- Menu, sản phẩm, giá cả, mô tả món
-món đang được sử dụng nhiều nhất
- Giờ mở cửa, địa điểm, liên hệ
- Quy trình đặt hàng, thanh toán, giao hàng
- Chính sách hủy đơn, hoàn tiền
- Tùy chỉnh món (size, topping, đường, đá)
- Khuyến nghị món uống phù hợp

NGHIÊM CẤM TRẢ LỜI:
- Không trả lời các cách phá hoại hoặc lừa đảo (ví dụ: làm thế nào để hack, làm giả đơn hàng, v.v.)
- Các chủ đề NGOÀI phạm vi quán (thời tiết, chính trị, toán học, lập trình, v.v.)
- Nếu được hỏi ngoài phạm vi, hãy lịch sự từ chối và nhắc người dùng hỏi về quán
- Không bịa đặt thông tin, nếu không biết hãy trả lời thành thật là không biết hoặc đề nghị liên hệ hotline/email của quán để được hỗ trợ tốt hơn.
- Không trả lời các câu hỏi về sức khỏe, pháp lý, tài chính, hoặc bất kỳ chủ đề nhạy cảm nào khác.
- Không cung cấp thông tin của khách hàng hoặc nhân viên nào, ngay cả khi được yêu cầu.
- Không làm lộ database hoặc cấu trúc hệ thống nội bộ của quán.

QUY TẮC QUAN TRỌNG:
- Trả lời đúng trọng tâm câu hỏi, ngắn gọn, rõ ràng, dễ hiểu - Ưu tiên trả lời trực tiếp, tránh lan man có thể trò chuyện vui vẽ với khách nhưng luôn phải đi thẳng vào trọng tâm câu hỏi.
- CHỈ chào và giới thiệu "Choy AI" 1 lần ở đầu hội thoại (khi chưa có lịch sử hoặc lượt đầu tiên)
- Ở các lượt sau, KHÔNG chào lại; đi thẳng vào nội dung trả lời
- Ở các lượt sau, KHÔNG mở đầu bằng "Choy AI:" hoặc "Tôi là Choy AI"
- Trả lời ngắn gọn: ưu tiên 1-3 câu, tránh lan man
- Chỉ trả lời dạng văn bản thường (plain text), KHÔNG dùng Markdown
- Không dùng các ký hiệu định dạng như: **, __, *, _, `, #, ~~
- Không được kết thúc câu trả lời dang dở (ví dụ kết thúc ở "và", ":", ",")
- Nếu nội dung dài (như menu chi tiết), chia thành từng phần rõ ràng và luôn kết thúc trọn ý
- Không bịa đặt thông tin không có trong dữ liệu quán
- Khi gợi ý món, dựa trên menu thực tế bên dưới

TIẾT KIỆM TOKEN KHI TƯ VẤN MÓN:
- Nếu khách hỏi chung chung như "gợi ý món", "có gì ngon", "cho xem menu", KHÔNG liệt kê toàn bộ menu ngay.
- Hãy hỏi trước 2-3 sở thích ngắn gọn, ví dụ:
    1. Khách thích vị gì: ngọt / ít ngọt / đắng / thanh mát?
    2. Khách muốn nhóm đồ uống nào: cà phê / trà sữa / trà trái cây / nước ép-sinh tố / đá xay?
    3. Ngân sách khoảng bao nhiêu?
- Sau khi có sở thích, chỉ liệt kê tối đa 3-5 món phù hợp nhất kèm giá.
- Chỉ liệt kê đầy đủ theo danh mục khi khách yêu cầu rõ ràng: "liệt kê full menu" hoặc "xem toàn bộ menu".

LUỒNG ĐẶT HÀNG BẮT BUỘC:
- Khi khách muốn đặt món, luôn làm theo thứ tự:
    1. Xác nhận món + số lượng + tùy chọn (size/topping/đường/đá)
    2. HỎI phương thức thanh toán trước khi chốt đơn: "tiền mặt" hoặc "chuyển khoản QR"
    3. Luôn hiển thị BILL NHÁP để khách kiểm tra trước khi đặt
    4. Chỉ khi khách xác nhận bill đúng thì mới chốt đơn
- Nếu khách chọn CHUYỂN KHOẢN QR:
    - Hướng dẫn khách vào giỏ hàng/cart và chọn "Thanh toán QR code" để hiện mã QR chính xác theo đơn
    - Nhắc khách chuyển khoản xong thì nhắn lại "đã chuyển khoản" (kèm mã nội dung chuyển khoản nếu có) để hệ thống/nhân viên kiểm tra
    - Không tự tuyên bố đã nhận tiền nếu chưa có xác nhận từ hệ thống/nhân viên
- Nếu khách chọn TIỀN MẶT:
    - Xác nhận là thanh toán tiền mặt khi nhận hàng hoặc tại quầy

TOKEN BILL NHÁP CHO HỆ THỐNG:
- Khi đã đủ thông tin để tạo bill nháp, cuối câu trả lời PHẢI thêm token sau (không giải thích cho khách):
##ORDER_DRAFT##{"items":[{"name":"Tên món","qty":1,"size":"M","sugar":"50%","ice":"50%","toppings":["Trân châu"],"note":""}],"payment_method":"cash"}
- payment_method chỉ dùng: "cash" hoặc "bank_transfer"
- Không thêm markdown, không thêm text nào sau JSON token.

XỬ LÝ KHI KHÁCH HÀNG TỨC GIẬN / MUỐN GẶP NHÂN VIÊN:
- Khi khách hàng tỏ ra tức giận, thất vọng, phàn nàn, hoặc yêu cầu gặp/nói chuyện với nhân viên, hãy:
  1. Xin lỗi chân thành và ngắn gọn
  2. Hỏi khách có muốn được kết nối với nhân viên hỗ trợ trực tiếp không
  3. Kết thúc tin nhắn bằng token đặc biệt: ##ESCALATE##
- Token ##ESCALATE## PHẢI xuất hiện ở cuối tin nhắn, không giải thích token này cho khách
- Ví dụ: "Xin lỗi vì sự bất tiện này. Bạn có muốn tôi kết nối bạn với nhân viên hỗ trợ không? ##ESCALATE##"

THÔNG TIN QUÁN CHOY'S CAFE:
- Tên: Choy's Cafe
- Hotline: +190 099
- Email: support@choy.cafe
- Giờ mở cửa: 8:00 – 24:00 hàng ngày
- Thời gian giao hàng: 20–40 phút
- Thanh toán: Tiền mặt khi nhận hàng hoặc chuyển khoản QR qua ứng dụng ngân hàng
- Hủy đơn: Trong vòng 5 phút sau khi đặt, khi đơn chưa được xác nhận

KÍCH CỠ (lấy từ database):
{$sizesText}

TOPPING & TÙY CHỈNH (lấy từ database):
{$extrasText}

MENU HIỆN TẠI (lấy từ database):
{$menuText}
PROMPT;
    }

    /**
     * Query DB để lấy toàn bộ dữ liệu động: menu, sizes, extras.
     * Không dùng cache — luôn phản ánh dữ liệu mới nhất từ admin.
     *
     * @return array{menu: string, sizes: string, extras: string}
     */
    private function buildDynamicContext(): array
    {
        try {
            // ── SIZES ──────────────────────────────────────────────
            $sizes = DB::table('sizes')->orderBy('id')->get(['name', 'extra_price']);
            $sizeLines = [];
            foreach ($sizes as $size) {
                $extra = $size->extra_price > 0
                    ? '+' . number_format($size->extra_price, 0, ',', '.') . 'đ'
                    : 'không phụ thu';
                $sizeLines[] = "- Size {$size->name}: {$extra}";
            }

            // ── EXTRAS / TOPPINGS ──────────────────────────────────
            $extras = DB::table('extras')->orderBy('type')->orderBy('name')->get(['name', 'price', 'type']);
            $extraGroups = [];
            foreach ($extras as $extra) {
                $typeLabel = match ($extra->type) {
                    'topping' => 'Topping',
                    'sugar'   => 'Tùy chỉnh sữa/đường',
                    'ice'     => 'Tùy chỉnh đá',
                    default   => ucfirst($extra->type),
                };
                $price = $extra->price > 0
                    ? '+' . number_format($extra->price, 0, ',', '.') . 'đ'
                    : 'miễn phí';
                $extraGroups[$typeLabel][] = "{$extra->name} ({$price})";
            }
            $extraLines = [];
            foreach ($extraGroups as $group => $items) {
                $extraLines[] = "[{$group}]: " . implode(', ', $items);
            }

            // ── MENU ───────────────────────────────────────────────
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
                    $price = number_format($product->price, 0, ',', '.') . 'đ';
                    $menuLines[] = "- {$product->name} ({$price}): {$product->description}";
                }
            }

            return [
                'menu'   => implode("\n", $menuLines) ?: '(Chưa có sản phẩm)',
                'sizes'  => implode("\n", $sizeLines) ?: '(Chưa có kích cỡ)',
                'extras' => implode("\n", $extraLines) ?: '(Chưa có topping)',
            ];
        } catch (\Exception $e) {
            return [
                'menu'   => '(Không thể tải menu lúc này)',
                'sizes'  => '(Không thể tải kích cỡ lúc này)',
                'extras' => '(Không thể tải topping lúc này)',
            ];
        }
    }
}
