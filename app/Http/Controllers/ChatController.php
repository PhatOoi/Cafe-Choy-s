<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // ── CUSTOMER ──────────────────────────────────────────────

    // Lấy tin nhắn của cuộc trò chuyện khách hàng hiện tại (dùng để polling)
    public function messages(Request $request)
    {
        $userId = Auth::id();
        $after  = (int) $request->query('after', 0);

        $messages = ChatMessage::where('user_id', $userId)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get(['id', 'message', 'sender', 'created_at']);

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
            'messages' => $messages,
        ]);
    }

    // Khách hàng gửi tin nhắn
    public function send(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $user = Auth::user();
        $msg = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'sender'  => 'customer',
        ]);

        // Update lần hoạt động cuối của khách hàng
        $user->update(['last_chat_activity_at' => now()]);

        return response()->json(['id' => $msg->id, 'created_at' => $msg->created_at]);
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
            ->get(['id', 'message', 'sender', 'created_at']);

        // Đánh dấu đã đọc khi staff mở hội thoại
        ChatMessage::where('user_id', $userId)
            ->where('sender', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // Staff phản hồi khách
    public function reply(Request $request, $userId)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        // Chắc chắn user tồn tại
        User::findOrFail($userId);

        $msg = ChatMessage::create([
            'user_id'    => $userId,
            'message'    => $request->message,
            'sender'     => 'staff',
            'replied_by' => Auth::id(),
        ]);

        return response()->json(['id' => $msg->id, 'created_at' => $msg->created_at]);
    }

    // Tổng số tin nhắn chưa đọc từ khách (dùng cho badge sidebar)
    public function unreadCount()
    {
        $count = ChatMessage::where('sender', 'customer')->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }
}
