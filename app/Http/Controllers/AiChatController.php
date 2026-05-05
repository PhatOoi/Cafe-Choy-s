<?php

namespace App\Http\Controllers;

use App\AI\GeminiService;
use App\Models\Product;
use App\Support\AiMenuSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    private GeminiService $gemini;
    private AiMenuSnapshotService $snapshotService;

    public function __construct(GeminiService $gemini, AiMenuSnapshotService $snapshotService)
    {
        $this->gemini = $gemini;
        $this->snapshotService = $snapshotService;
    }

    // Hiển thị trang chat AI
    public function index()
    {
        return view('ai-chat');
    }

    // Trả về snapshot DB.json để frontend lưu vào localStorage.
    public function dbJson()
    {
        $path = $this->snapshotService->absolutePath();
        if (!File::exists($path)) {
            return response()->json([
                'ok' => false,
                'message' => 'DB.json chua san sang.',
            ], 503);
        }

        $decoded = json_decode((string) File::get($path), true);
        if (!is_array($decoded)) {
            return response()->json([
                'ok' => false,
                'message' => 'DB.json khong hop le.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'snapshot' => $decoded,
            'signature' => $this->signSnapshot($decoded),
        ]);
    }

    // Nhận tin nhắn và trả về phản hồi từ Gemini (trang ai-chat.blade.php)
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'ai_snapshot' => 'nullable|array',
            'ai_snapshot_signature' => 'nullable|string|max:255',
        ]);

        $message = trim($request->message);
        $history = session('ai_chat_history', []);
        $snapshotOverride = $this->extractSnapshotOverride($request);
        [$reply, $escalate] = $this->callGemini($message, $history, 'ai_chat_history', $snapshotOverride);
        [$reply, $quickAction] = $this->resolveQuickActionFlow(
            $message,
            $history,
            $reply,
            'ai_chat_pending_quick_action'
        );
        $reply = $this->alignReplyWithQuickAction($reply, $quickAction);

        return response()->json([
            'reply'        => $reply,
            'escalate'     => $escalate,
            'quick_action' => $quickAction,
        ]);
    }

    // Nhận tin nhắn từ floating widget (public route, hoạt động cả khi chưa đăng nhập)
    public function widgetSend(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'ai_snapshot' => 'nullable|array',
            'ai_snapshot_signature' => 'nullable|string|max:255',
        ]);

        $message = trim($request->message);
        $history = session('widget_ai_history', []);
        $snapshotOverride = $this->extractSnapshotOverride($request);
        [$reply, $escalate] = $this->callGemini($message, $history, 'widget_ai_history', $snapshotOverride);
        [$reply, $quickAction] = $this->resolveQuickActionFlow(
            $message,
            $history,
            $reply,
            'widget_ai_pending_quick_action'
        );
        $reply = $this->alignReplyWithQuickAction($reply, $quickAction);

        return response()->json([
            'reply'        => $reply,
            'escalate'     => $escalate,
            'quick_action' => $quickAction,
        ]);
    }

    // Xóa lịch sử hội thoại trang ai-chat
    public function clear()
    {
        session()->forget('ai_chat_history');
        session()->forget('ai_chat_pending_quick_action');
        return response()->json(['ok' => true]);
    }

    // Xóa lịch sử widget
    public function widgetClear()
    {
        session()->forget('widget_ai_history');
        session()->forget('widget_ai_pending_quick_action');
        return response()->json(['ok' => true]);
    }

    // ── PRIVATE ───────────────────────────────────────────────

    /**
     * Gọi Gemini, cập nhật lịch sử session, parse cờ ##ESCALATE##.
     *
     * @return array{0: string, 1: bool}  [replyText, escalate]
     */
    private function callGemini(string $message, array $history, string $sessionKey, ?array $snapshotOverride = null): array
    {
        $raw = $this->gemini->chat($message, $history, $snapshotOverride);

        // Phát hiện yêu cầu chuyển sang nhân viên
        $escalate = str_contains($raw, '##ESCALATE##');
        $reply    = trim(str_replace('##ESCALATE##', '', $raw));

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

        return [$reply, $escalate];
    }

    private function stripRepeatedGreeting(string $reply): string
    {
        $cleaned = preg_replace(
            '/^(xin\s+chào\b[\s,:;!?.-]*|chào\s+bạn\b[\s,:;!?.-]*|\bhello\b[\s,:;!?.-]*|\bhi\b[\s,:;!?.-]*|chào\s+buổi\s+(sáng|trưa|chiều|tối)\b[\s,:;!?.-]*)/iu',
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

    private function buildQuickActionForProductIntent(string $message, array $history = []): ?array
    {
        $normalized = $this->normalizeText($message);
        if ($normalized === '') {
            return null;
        }

        $isDirectRequest = $this->isDrinkNavigationIntent($normalized);
        $isFollowupNavigation = $this->isNavigationFollowupIntent($normalized);


        $products = Product::query()
            ->where('status', 'available')
            ->orderBy('name')
            ->get(['id', 'name']);

        $candidates = [$normalized];
        if ($isFollowupNavigation) {
            $recentModelText = $this->latestHistoryText($history, 'model', 2);
            $recentUserText = $this->latestHistoryText($history, 'user', 2);

            if ($recentModelText !== '') {
                $candidates[] = $this->normalizeText($recentModelText);
            }
            if ($recentUserText !== '') {
                $candidates[] = $this->normalizeText($recentUserText);
            }
        }

        $bestMatch = null;
        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            $bestMatch = $this->findBestMatchingProduct($candidate, $products->all());
            if ($bestMatch !== null) {
                break;
            }
        }

        if ($bestMatch !== null) {
            $productName = (string) $bestMatch->name;

            return [
                'intent' => 'product_found',
                'label' => 'Đi đến món: ' . $productName,
                'url' => '/search?q=' . urlencode($productName) . '&ai_jump=1',
            ];
        }

        // Chỉ fallback "không có món" khi user hỏi trực tiếp tên món trong lượt hiện tại.
        if (!$isDirectRequest) {
            return null;
        }

        return [
            'intent' => 'not_found',
            'label' => 'Xem menu hiện có',
            'url' => '/menu',
        ];
    }

    private function findBestMatchingProduct(string $normalizedMessage, array $products): ?Product
    {
        $messageTokens = $this->tokenize($normalizedMessage);
        $bestProduct = null;
        $bestScore = -1;

        foreach ($products as $product) {
            if (!$product instanceof Product) {
                continue;
            }

            $normalizedProductName = $this->normalizeText((string) $product->name);
            if ($normalizedProductName === '') {
                continue;
            }

            if (str_contains($normalizedMessage, $normalizedProductName)) {
                return $product;
            }

            if (str_contains($normalizedProductName, $normalizedMessage) && strlen($normalizedMessage) >= 6) {
                return $product;
            }

            $productTokens = $this->tokenize($normalizedProductName);
            if (empty($productTokens)) {
                continue;
            }

            $overlapCount = count(array_intersect($messageTokens, $productTokens));
            if ($overlapCount < 2) {
                continue;
            }

            $coverage = $overlapCount / max(count($productTokens), 1);
            $score = ($coverage * 10) + $overlapCount;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestProduct = $product;
            }
        }

        return $bestScore >= 6 ? $bestProduct : null;
    }

    private function isDrinkNavigationIntent(string $normalizedMessage): bool
    {
        if (!preg_match('/\b(muon|uong|tim|co\s+mon|goi|dat|thich|lay|chon|di|luon|ngay)\b/u', $normalizedMessage)) {
            return false;
        }

        return (bool) preg_match('/\b(ca\s?phe|tra\s?sua|tra|nuoc|sinh\s?to|ep|matcha|latte|bac\s?xiu|da\s?xay|cake|banh)\b/u', $normalizedMessage);
    }

    private function isNavigationFollowupIntent(string $normalizedMessage): bool
    {
        return (bool) preg_match('/\b(nut|button|chuyen\s+den|di\s+den|link|mo\s+mon|mo\s+toi\s+mon|den\s+mon)\b/u', $normalizedMessage);
    }

    private function latestHistoryText(array $history, string $role, int $limit = 2): string
    {
        $texts = [];

        for ($i = count($history) - 1; $i >= 0 && count($texts) < $limit; $i--) {
            $turn = $history[$i] ?? null;
            if (!is_array($turn) || ($turn['role'] ?? '') !== $role) {
                continue;
            }

            $text = trim((string) ($turn['text'] ?? ''));
            if ($text !== '') {
                $texts[] = $text;
            }
        }

        return implode(' ', $texts);
    }

    private function alignReplyWithQuickAction(string $reply, ?array $quickAction): string
    {
        if ($quickAction === null || ($quickAction['intent'] ?? null) !== 'product_found') {
            return $reply;
        }

        $normalizedReply = Str::lower(Str::ascii($reply));
        $hasContradiction = (bool) preg_match(
            '/(khong\s+co\s+(chuc\s+nang|nut|button))|(khong\s+the\s+(tao|chuyen))/u',
            $normalizedReply
        );

        $productName = trim((string) preg_replace('/^Đi đến món:\s*/u', '', (string) ($quickAction['label'] ?? '')));

        if ($hasContradiction) {
            $cleanProductName = $productName !== '' ? $productName : 'món bạn vừa chọn';

            return "Đã tìm thấy {$cleanProductName}.\nAnh/chị bấm nút bên dưới để mở đúng món nha.";
        }

        $trimmed = trim($reply);
        if ($trimmed === '') {
            return 'Anh/chị bấm nút bên dưới để đi đến đúng món nha.';
        }

        if (stripos($normalizedReply, 'bam nut') === false && stripos($normalizedReply, 'di den mon') === false) {
            $trimmed .= "\n\nAnh/chị bấm nút bên dưới để đi đến món nha.";
        }

        return $trimmed;
    }

    /**
     * @return array{0: string, 1: array|null}
     */
    private function resolveQuickActionFlow(string $message, array $history, string $reply, string $pendingKey): array
    {
        $normalized = $this->normalizeText($message);
        $pendingAction = session($pendingKey);

        if (is_array($pendingAction) && !empty($pendingAction['label']) && !empty($pendingAction['url'])) {
            if ($this->isAffirmativeIntent($normalized)) {
                session()->forget($pendingKey);

                $productName = trim((string) preg_replace('/^Đi đến món:\s*/u', '', (string) ($pendingAction['label'] ?? '')));
                $replyText = $productName !== ''
                    ? "Oke anh/chị, bé gửi nút để đi đến {$productName} nha."
                    : 'Oke anh/chị, bé gửi nút để đi đến món bạn vừa chọn nha.';

                return [$replyText, $pendingAction];
            }

            if ($this->isNegativeIntent($normalized)) {
                session()->forget($pendingKey);
                return ['Dạ oke anh/chị, khi nào cần thì nhắn bé để gửi nút chuyển món nha! 😊', null];
            }
        }

        $candidateAction = $this->buildQuickActionForProductIntent($message, $history);
        if ($candidateAction === null) {
            return [$reply, null];
        }

        if (($candidateAction['intent'] ?? null) === 'product_found') {
            if ($this->isAffirmativeIntent($normalized) || $this->isNavigationFollowupIntent($normalized)) {
                return [$reply, $candidateAction];
            }

            session([$pendingKey => $candidateAction]);

            $productName = trim((string) preg_replace('/^Đi đến món:\s*/u', '', (string) ($candidateAction['label'] ?? '')));
            $askReply = $productName !== ''
                ? "Anh/chị có muốn bé chuyển đến món {$productName} không? Nếu đồng ý, chỉ cần nhắn 'có' hoặc 'gửi nút' nha."
                : "Anh/chị có muốn bé gửi nút chuyển đến món này không? Nếu đồng ý, chỉ cần nhắn 'có' hoặc 'gửi nút' nha.";

            return [$askReply, null];
        }

        return [$reply, $candidateAction];
    }

    private function isAffirmativeIntent(string $normalizedMessage): bool
    {
        // Các từ khẳng định mạnh — match bất kể độ dài message
        if (preg_match('/\b(ok|oke|dong\s*y|gui\s+nut|chuyen\s+den|di\s+den|mo\s+mon|xac\s+nhan|di\s+luon|ngay\s+di)\b/u', $normalizedMessage)) {
            return true;
        }

        // "co" / "di" / "luon" chỉ tính là khẳng định khi message ngắn (≤ 4 từ)
        // và KHÔNG chứa từ hỏi như "gi", "khong", "nao", "sao", "bao"
        // tránh bắt nhầm "cơ chế", "có gì", "có không"...
        $wordCount = count(array_filter(explode(' ', trim($normalizedMessage)), fn ($w) => $w !== ''));
        $hasQuestionWord = (bool) preg_match('/\b(gi|khong|ko|nao|sao|bao|may|la gi|the nao|nhu nao)\b/u', $normalizedMessage);
        if ($wordCount <= 4 && !$hasQuestionWord && preg_match('/\b(co|di|luon)\b/u', $normalizedMessage)) {
            return true;
        }

        return false;
    }

    private function isNegativeIntent(string $normalizedMessage): bool
    {
        return (bool) preg_match('/\b(khong|ko|khong\s+can|thoi|huy|dung)\b/u', $normalizedMessage);
    }

    private function normalizeText(string $text): string
    {
        $ascii = Str::lower(Str::ascii($text));
        $ascii = preg_replace('/[^a-z0-9\s]+/u', ' ', $ascii) ?? $ascii;
        $ascii = preg_replace('/\s+/u', ' ', trim($ascii)) ?? trim($ascii);

        return $ascii;
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $normalizedText): array
    {
        $parts = preg_split('/\s+/u', trim($normalizedText)) ?: [];
        $parts = array_filter($parts, static fn ($part) => strlen((string) $part) >= 2);

        return array_values(array_unique(array_map('strval', $parts)));
    }

    private function extractSnapshotOverride(Request $request): ?array
    {
        $snapshot = $request->input('ai_snapshot');
        $signature = (string) $request->input('ai_snapshot_signature', '');

        if (!is_array($snapshot) || $signature === '') {
            return null;
        }

        // Tránh payload quá lớn từ client làm nặng request.
        $encoded = json_encode($snapshot, JSON_UNESCAPED_SLASHES);
        if ($encoded === false || strlen($encoded) > 1024 * 1024) {
            return null;
        }

        if (!hash_equals($this->signSnapshot($snapshot), $signature)) {
            return null;
        }

        return $snapshot;
    }

    private function signSnapshot(array $snapshot): string
    {
        $encoded = json_encode($snapshot, JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return '';
        }

        return hash_hmac('sha256', $encoded, (string) config('app.key'));
    }
}
