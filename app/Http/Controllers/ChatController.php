<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function formatMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'message' => $message->message,
            'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
            'sender' => $message->sender,
            'created_at' => $message->created_at,
        ];
    }

    // ── CUSTOMER ──────────────────────────────────────────────

    // Lấy tin nhắn của cuộc trò chuyện khách hàng hiện tại (dùng để polling)
    public function messages(Request $request)
    {
        $userId = Auth::id();
        $after  = (int) $request->query('after', 0);

        $messages = ChatMessage::where('user_id', $userId)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get(['id', 'message', 'image_path', 'sender', 'created_at']);

        // Nếu client đang giữ lịch sử (after > 0) nhưng DB đã không còn tin nào,
        // trả cờ reset để frontend xóa ngay lịch sử cũ đang hiển thị.
        $hasAnyMessages = ChatMessage::where('user_id', $userId)->exists();
        $shouldReset = $after > 0 && $messages->isEmpty() && !$hasAnyMessages;

        // Đánh dấu tin nhắn của staff là đã đọc
        ChatMessage::where('user_id', $userId)
            ->where('sender', 'staff')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'reset' => $shouldReset,
            'messages' => $messages->map(fn (ChatMessage $message) => $this->formatMessage($message))->values(),
        ]);
    }

    // Khách hàng gửi tin nhắn
    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $text = trim((string) ($validated['message'] ?? ''));
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('chat-images', 'public') : null;

        if ($text === '' && !$imagePath) {
            return response()->json([
                'message' => 'Vui lòng nhập nội dung hoặc chọn ảnh để gửi.',
            ], 422);
        }

        $user = Auth::user();
        $msg = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $text,
            'image_path' => $imagePath,
            'sender'  => 'customer',
        ]);

        // Update lần hoạt động cuối của khách hàng
        $user->update(['last_chat_activity_at' => now()]);

        return response()->json($this->formatMessage($msg));
    }

    // ── STAFF ─────────────────────────────────────────────────

    // Danh sách các khách hàng đang có hội thoại
    public function conversations()
    {
        $conversations = ChatMessage::selectRaw('user_id, MAX(id) as last_id, COUNT(CASE WHEN sender="customer" AND is_read=0 THEN 1 END) as unread')
            ->groupBy('user_id')
            ->orderByDesc('last_id')
            ->with('user:id,name,email,avatar_url')
            ->get();

        return response()->json($conversations);
    }

    // Staff lấy toàn bộ tin nhắn của một khách
    public function conversation(Request $request, $userId)
    {
        $after = $request->query('after', 0);

        $messages = ChatMessage::where('user_id', $userId)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get(['id', 'message', 'image_path', 'sender', 'created_at']);

        // Đánh dấu đã đọc khi staff mở hội thoại
        ChatMessage::where('user_id', $userId)
            ->where('sender', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages->map(fn (ChatMessage $message) => $this->formatMessage($message))->values());
    }

    // Staff phản hồi khách
    public function reply(Request $request, $userId)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $text = trim((string) ($validated['message'] ?? ''));
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('chat-images', 'public') : null;

        if ($text === '' && !$imagePath) {
            return response()->json([
                'message' => 'Vui lòng nhập nội dung hoặc chọn ảnh để gửi.',
            ], 422);
        }

        // Chắc chắn user tồn tại
        User::findOrFail($userId);

        $msg = ChatMessage::create([
            'user_id'    => $userId,
            'message'    => $text,
            'image_path' => $imagePath,
            'sender'     => 'staff',
            'replied_by' => Auth::id(),
        ]);

        return response()->json($this->formatMessage($msg));
    }

    // Tổng số tin nhắn chưa đọc từ khách (dùng cho badge sidebar)
    public function unreadCount()
    {
        $count = ChatMessage::where('sender', 'customer')->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    // Staff kết thúc hội thoại — xóa toàn bộ tin nhắn của khách đó
    public function closeConversation($userId)
    {
        User::findOrFail($userId);
        ChatMessage::where('user_id', $userId)->delete();
        return response()->json(['ok' => true]);
    }
}
