<?php

namespace App\Http\Controllers;

use App\AI\GeminiService;
use Illuminate\Http\Request;

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
        [$reply, $escalate] = $this->callGemini($message, $history, 'widget_ai_history');

        return response()->json([
            'reply'    => $reply,
            'escalate' => $escalate,
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
