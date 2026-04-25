<?php

namespace App\Http\Controllers;

use App\AI\GeminiService;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiChatController extends Controller
{
    private GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    // Hiển thị trang chat AI
    public function index()
    {
        return view('ai-chat');
    }

    // Nhận tin nhắn và trả về phản hồi từ Gemini (trang ai-chat.blade.php)
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = trim($request->message);
        $history = session('ai_chat_history', []);
        [$reply, $escalate] = array_slice($this->callGemini($message, $history, 'ai_chat_history'), 0, 2);

        return response()->json([
            'reply'    => $reply,
            'escalate' => $escalate,
        ]);
    }

    // Nhận tin nhắn từ floating widget (public route, hoạt động cả khi chưa đăng nhập)
    public function widgetSend(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = trim($request->message);
        $history = session('widget_ai_history', []);
        [$reply, $escalate, $orderDraft] = $this->callGemini($message, $history, 'widget_ai_history');

        return response()->json([
            'reply'    => $reply,
            'escalate' => $escalate,
            'orderDraft' => $orderDraft,
        ]);
    }

    public function widgetConfirmOrder(Request $request, CartController $cartController): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để đặt đơn qua AI.',
            ], 401);
        }

        $draft = session('widget_order_draft');
        if (!is_array($draft) || empty($draft['items'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bill nháp để xác nhận. Vui lòng yêu cầu AI tạo bill lại.',
            ], 422);
        }

        // Làm mới cart trước khi AI đặt đơn để tránh trộn với giỏ cũ.
        session()->forget(['cart', 'pending_qr_order_id']);

        foreach ($draft['items'] as $item) {
            $product = $this->resolveDraftProduct($item);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm: ' . ($item['name'] ?? 'không rõ tên') . '. Vui lòng chỉnh lại bill.',
                ], 422);
            }

            $addRequest = Request::create('/cart/add', 'POST', [
                'product_id' => $product->id,
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
                'size' => $item['size'] ?? null,
                'sugar' => $item['sugar'] ?? null,
                'ice' => $item['ice'] ?? null,
                'toppings' => is_array($item['toppings'] ?? null) ? $item['toppings'] : [],
                'note' => $item['note'] ?? null,
            ], [], [], [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ]);

            $addResponse = $cartController->add($addRequest);
            $addPayload = method_exists($addResponse, 'getData') ? $addResponse->getData(true) : [];

            if (($addPayload['success'] ?? true) === false) {
                return response()->json([
                    'success' => false,
                    'message' => $addPayload['message'] ?? 'Không thể thêm món vào giỏ. Vui lòng thử lại.',
                ], 422);
            }
        }

        $paymentMethod = $draft['payment_method'] ?? 'cash';
        $refCode = trim((string) ($draft['ref_code'] ?? ''));
        if ($paymentMethod === 'bank_transfer' && $refCode === '') {
            $refCode = 'DH' . now()->format('His') . rand(100, 999);
        }

        $checkoutRequest = Request::create('/cart/checkout/' . ($paymentMethod === 'bank_transfer' ? 'qr' : 'cash'), 'POST', [
            'qr_note' => $paymentMethod === 'bank_transfer' ? $refCode : null,
        ], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json',
        ]);

        $checkoutResponse = $paymentMethod === 'bank_transfer'
            ? $cartController->confirmQrPayment($checkoutRequest)
            : $cartController->confirmCashPayment($checkoutRequest);

        $checkoutPayload = method_exists($checkoutResponse, 'getData')
            ? $checkoutResponse->getData(true)
            : ['success' => false, 'message' => 'Không thể tạo đơn hàng.'];

        if (($checkoutPayload['success'] ?? false) !== true) {
            return response()->json([
                'success' => false,
                'message' => $checkoutPayload['message'] ?? 'Không thể tạo đơn hàng. Vui lòng thử lại.',
            ], 422);
        }

        $qrPayload = null;
        if ($paymentMethod === 'bank_transfer') {
            $qrOrderId = (int) ($checkoutPayload['order_id'] ?? session('pending_qr_order_id', 0));
            if ($qrOrderId > 0) {
                $payment = Payment::where('order_id', $qrOrderId)
                    ->where('method', 'bank_transfer')
                    ->latest('id')
                    ->first();

                if ($payment) {
                    $qrPayload = $this->buildQrPayload((float) $payment->amount, $payment->ref_code ?: $refCode);
                }
            }
        }

        session()->forget('widget_order_draft');

        return response()->json([
            'success' => true,
            'message' => $checkoutPayload['message'] ?? 'Đặt đơn thành công.',
            'order_id' => $checkoutPayload['order_id'] ?? null,
            'redirect_url' => $checkoutPayload['redirect_url'] ?? null,
            'payment_method' => $paymentMethod,
            'qr' => $qrPayload,
        ]);
    }

    // Xóa lịch sử hội thoại trang ai-chat
    public function clear()
    {
        session()->forget('ai_chat_history');
        return response()->json(['ok' => true]);
    }

    // Xóa lịch sử widget
    public function widgetClear()
    {
        session()->forget('widget_ai_history');
        return response()->json(['ok' => true]);
    }

    // ── PRIVATE ───────────────────────────────────────────────

    /**
     * Gọi Gemini, cập nhật lịch sử session, parse cờ ##ESCALATE##.
     *
     * @return array{0: string, 1: bool}  [replyText, escalate]
     */
    private function callGemini(string $message, array $history, string $sessionKey): array
    {
        $raw = $this->gemini->chat($message, $history);

        // Phát hiện yêu cầu chuyển sang nhân viên
        $escalate = str_contains($raw, '##ESCALATE##');
        $reply    = trim(str_replace('##ESCALATE##', '', $raw));

        [$reply, $orderDraft] = $this->extractOrderDraft($reply);
        if ($orderDraft !== null) {
            session(['widget_order_draft' => $orderDraft]);
        }

        // Nếu không phải lượt đầu, cắt phần chào mở đầu để tránh lặp lại.
        if (!empty($history)) {
            $reply = $this->stripRepeatedGreeting($reply);
        }

        // Chuẩn hóa output để UI không hiển thị ký hiệu markdown như **, #, `...
        $reply = $this->normalizePlainTextReply($reply);

        // Cập nhật lịch sử session
        $history[] = ['role' => 'user',  'text' => $message];
        $history[] = ['role' => 'model', 'text' => $reply];

        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        session([$sessionKey => $history]);

        return [$reply, $escalate, $orderDraft];
    }

    private function extractOrderDraft(string $reply): array
    {
        $matches = [];
        if (!preg_match('/##ORDER_DRAFT##\s*(\{.*\})/su', $reply, $matches)) {
            return [$reply, null];
        }

        $json = trim($matches[1]);
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || empty($decoded['items']) || !is_array($decoded['items'])) {
            $cleanReply = trim(str_replace($matches[0], '', $reply));
            return [$cleanReply, null];
        }

        $items = [];
        foreach ($decoded['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;

            if ($name === '' && (!$productId || $productId <= 0)) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'product_id' => $productId,
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
                'size' => isset($item['size']) ? trim((string) $item['size']) : null,
                'sugar' => isset($item['sugar']) ? trim((string) $item['sugar']) : null,
                'ice' => isset($item['ice']) ? trim((string) $item['ice']) : null,
                'toppings' => array_values(array_filter(array_map('strval', (array) ($item['toppings'] ?? [])), static fn ($x) => trim($x) !== '')),
                'note' => isset($item['note']) ? trim((string) $item['note']) : null,
            ];
        }

        if (empty($items)) {
            $cleanReply = trim(str_replace($matches[0], '', $reply));
            return [$cleanReply, null];
        }

        $paymentRaw = strtolower(trim((string) ($decoded['payment_method'] ?? 'cash')));
        $paymentMethod = in_array($paymentRaw, ['qr', 'bank_transfer'], true) ? 'bank_transfer' : 'cash';

        $draft = [
            'items' => $items,
            'payment_method' => $paymentMethod,
            'ref_code' => isset($decoded['ref_code']) ? trim((string) $decoded['ref_code']) : null,
        ];

        $cleanReply = trim(str_replace($matches[0], '', $reply));

        return [$cleanReply, $draft];
    }

    private function resolveDraftProduct(array $item): ?Product
    {
        $productId = (int) ($item['product_id'] ?? 0);
        if ($productId > 0) {
            $product = Product::where('id', $productId)->where('status', 'available')->first();
            if ($product) {
                return $product;
            }
        }

        $name = trim((string) ($item['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $normalized = mb_strtolower($name);
        $aliases = [
            'trà đào' => ['peach tea', 'trà đào', 'tra dao'],
            'tra dao' => ['peach tea', 'trà đào', 'tra dao'],
            'peach tea' => ['peach tea', 'trà đào', 'tra dao'],
        ];

        $exact = Product::where('status', 'available')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($exact) {
            return $exact;
        }

        if (isset($aliases[$normalized])) {
            foreach ($aliases[$normalized] as $kw) {
                $hit = Product::where('status', 'available')
                    ->where(function ($q) use ($kw) {
                        $q->where('name', 'like', '%' . $kw . '%')
                            ->orWhere('description', 'like', '%' . $kw . '%');
                    })
                    ->orderByRaw('LENGTH(name) ASC')
                    ->first();

                if ($hit) {
                    return $hit;
                }
            }
        }

        return Product::where('status', 'available')
            ->where(function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                    ->orWhere('description', 'like', '%' . $name . '%');
            })
            ->orderByRaw('LENGTH(name) ASC')
            ->first();
    }

    private function buildQrPayload(float $amount, ?string $refCode): array
    {
        $bankCode = 'vietcombank';
        $bankName = 'Vietcombank';
        $accountName = 'TRAN QUOC LONG';
        $accountNumber = '1042131375';
        $safeAmount = max(0, (int) round($amount));
        $finalRef = trim((string) ($refCode ?? '')) !== '' ? trim((string) $refCode) : ('DH' . now()->format('His'));

        $qrUrl = 'https://img.vietqr.io/image/' . $bankCode . '-' . $accountNumber . '-print.png'
            . '?amount=' . $safeAmount
            . '&addInfo=' . urlencode($finalRef)
            . '&accountName=' . urlencode($accountName);

        return [
            'bank_name' => $bankName,
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'amount' => $safeAmount,
            'ref_code' => $finalRef,
            'image_url' => $qrUrl,
        ];
    }

    private function stripRepeatedGreeting(string $reply): string
    {
        $cleaned = preg_replace(
            '/^(xin\s+chào[^\n.!?]*[.!?]?|chào\s+bạn[^\n.!?]*[.!?]?|hello[^\n.!?]*[.!?]?|hi[^\n.!?]*[.!?]?|chào\s+buổi\s+(sáng|trưa|chiều|tối)[^\n.!?]*[.!?]?)\s*/iu',
            '',
            trim($reply)
        );

        // Loại bỏ prefix tên trợ lý ở đầu câu như "Choy AI: ..." để tránh lặp.
        $cleaned = preg_replace(
            '/^(choy\s*\'?s?\s*ai|choy\s*ai)\s*[:\-\x{2013}\x{2014}]?\s*/iu',
            '',
            trim((string) $cleaned)
        );

        // Loại bỏ mở đầu kiểu "Tôi là Choy AI" ở các lượt sau.
        $cleaned = preg_replace(
            '/^(tôi\s+là\s+choy\s*\'?s?\s*ai[,.!?:\-\s]*)/iu',
            '',
            trim((string) $cleaned)
        );

        return trim((string) ($cleaned !== null && $cleaned !== '' ? $cleaned : $reply));
    }

    private function normalizePlainTextReply(string $reply): string
    {
        $text = trim($reply);

        // Xóa heading markdown ở đầu dòng: #, ##, ###...
        $text = preg_replace('/^\s*#{1,6}\s*/mu', '', $text) ?? $text;

        // Xóa các marker định dạng markdown phổ biến.
        $text = str_replace(['**', '__', '*', '_', '`', '~~'], '', $text);

        // Dọn khoảng trắng dư sau khi remove marker.
        $text = preg_replace('/[ \t]{2,}/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
