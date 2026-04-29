@extends('staff.layout')

@section('title', 'Hỗ trợ khách hàng')
@section('page-title', 'Hỗ trợ khách hàng')

@section('styles')
<style>
    .support-shell {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 0;
        height: calc(100vh - 64px);
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #eef0f4;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }

    /* ── Sidebar hội thoại ── */
    .conv-sidebar {
        background: #f8f9fc;
        border-right: 1px solid #eef0f4;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .conv-sidebar-header {
        padding: 18px 16px 14px;
        border-bottom: 1px solid #eef0f4;
        font-weight: 600;
        font-size: 14px;
        color: #1a1a2e;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .conv-unread-total {
        background: var(--primary);
        color: #fff;
        border-radius: 10px;
        padding: 1px 8px;
        font-size: 11px;
        font-weight: 700;
    }
    .conv-list { flex: 1; overflow-y: auto; }
    .conv-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f0f1f5;
        transition: background .15s;
    }
    .conv-item:hover { background: #f0f4ff; }
    .conv-item.active { background: #fff4e5; border-left: 3px solid var(--primary); }
    .conv-avatar {
        width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
        background: #dde;
    }
    .conv-info { flex: 1; min-width: 0; }
    .conv-name { font-size: 13px; font-weight: 600; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conv-preview { font-size: 11px; color: #8a8fa8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conv-badge {
        background: var(--primary); color: #fff; border-radius: 50%;
        min-width: 18px; height: 18px; font-size: 10px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    /* ── Khu vực chat ── */
    .chat-area {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    .chat-header {
        padding: 16px 20px;
        border-bottom: 1px solid #eef0f4;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
    }
    .chat-header-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }
    .chat-header-sub  { font-size: 12px; color: #8a8fa8; }

    .chat-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #b0b4c8;
        gap: 12px;
    }
    .chat-empty i { font-size: 48px; }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #fafbff;
    }
    .msg-row {
        display: flex;
        flex-direction: column;
    }
    .msg-row.customer { align-items: flex-start; }
    .msg-row.staff    { align-items: flex-end; }
    .msg-bubble {
        max-width: 72%;
        padding: 9px 14px;
        border-radius: 14px;
        font-size: 13px;
        line-height: 1.6;
        word-break: break-word;
    }
    .msg-row.customer .msg-bubble {
        background: #f0f1f6;
        color: #1a1a2e;
        border-bottom-left-radius: 3px;
    }
    .msg-row.staff .msg-bubble {
        background: var(--primary);
        color: #fff;
        border-bottom-right-radius: 3px;
    }
    .msg-image {
        width: 100%;
        max-width: 280px;
        border-radius: 10px;
        display: block;
        margin-top: 8px;
        box-shadow: 0 8px 16px rgba(0,0,0,.18);
    }
    .msg-time { font-size: 10px; color: #b0b4c8; margin-top: 3px; }

    .chat-input-bar {
        padding: 14px 16px;
        border-top: 1px solid #eef0f4;
        display: flex;
        gap: 10px;
        background: #fff;
    }
    .chat-input-bar input {
        flex: 1;
        border: 1px solid #dde0ea;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: 13px;
        outline: none;
        font-family: 'Poppins', sans-serif;
    }
    .chat-input-bar input:focus { border-color: var(--primary); }
    .chat-send-btn {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 18px;
        cursor: pointer;
        font-size: 14px;
        transition: background .18s;
    }
    .chat-send-btn:hover { background: var(--primary-dark); }
    .chat-image-hint {
        min-height: 18px;
        font-size: 11px;
        color: #8a8fa8;
        padding: 0 16px 10px;
    }
</style>
@endsection

@section('content')
<div class="support-shell">

    {{-- ── Danh sách hội thoại ── --}}
    <div class="conv-sidebar">
        <div class="conv-sidebar-header">
            <span>Hội thoại</span>
            <span class="conv-unread-total" id="totalUnread">0</span>
        </div>
        <div class="conv-list" id="convList">
            <div style="padding:20px;text-align:center;color:#b0b4c8;font-size:13px;">Đang tải...</div>
        </div>
    </div>

    {{-- ── Khu vực chat ── --}}
    <div class="chat-area" id="chatArea">
        <div class="chat-empty" id="chatEmpty">
            <i class="fas fa-comments"></i>
            <p style="font-size:14px;">Chọn một hội thoại để bắt đầu phản hồi</p>
        </div>

        <div id="chatActive" style="display:none;flex-direction:column;height:100%;">
            <div class="chat-header">
                <img id="activeAvatar" src="{{ asset('images/user.jpg') }}" class="conv-avatar">
                <div>
                    <div class="chat-header-name" id="activeName">—</div>
                    <div class="chat-header-sub" id="activeEmail">—</div>
                </div>
            </div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input-bar">
                <input id="replyInput" type="text" placeholder="Nhập phản hồi..." onkeydown="if(event.key==='Enter')sendReply()">
                <input id="replyImageInput" type="file" accept="image/*" style="display:none;">
                <button class="chat-send-btn" type="button" title="Gửi ảnh" onclick="document.getElementById('replyImageInput').click()"><i class="fas fa-image"></i></button>
                <button class="chat-send-btn" onclick="sendReply()"><i class="fas fa-paper-plane"></i></button>
            </div>
            <div class="chat-image-hint" id="replyImageHint"></div>
        </div>
    </div>
</div>

<script>
    var activeUserId = null;
    var lastMsgId = 0;
    var convPoll = null;
    var msgPoll  = null;
    var staffPastedImageFile = null;

    function extractClipboardImage(event) {
        var clipboardData = event.clipboardData || window.clipboardData;
        if (!clipboardData || !clipboardData.items) return null;

        for (var i = 0; i < clipboardData.items.length; i++) {
            var item = clipboardData.items[i];
            if (item.kind === 'file' && item.type.indexOf('image/') === 0) {
                return item.getAsFile();
            }
        }

        return null;
    }

    // ── Load danh sách hội thoại ──
    function loadConversations() {
        fetch('{{ route("staff.chat.conversations") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(function(list) {
            var total = list.reduce(function(s,c){ return s + (c.unread||0); }, 0);
            document.getElementById('totalUnread').textContent = total;

            var html = '';
            if (list.length === 0) {
                html = '<div style="padding:20px;text-align:center;color:#b0b4c8;font-size:13px;">Chưa có hội thoại nào</div>';
            } else {
                list.forEach(function(c) {
                    var name  = c.user ? c.user.name  : 'Khách';
                    var email = c.user ? c.user.email : '';
                    var avatarUrl = c.user && c.user.avatar_url
                        ? '/storage/' + c.user.avatar_url
                        : '{{ asset("images/user.jpg") }}';
                    var badge = c.unread > 0
                        ? '<span class="conv-badge">' + c.unread + '</span>'
                        : '';
                    html += '<div class="conv-item' + (c.user_id == activeUserId ? ' active' : '') + '" onclick="openConv(' + c.user_id + ',\'' + name.replace(/'/g,"\\'") + '\',\'' + email + '\',\'' + avatarUrl + '\')">' +
                        '<img src="' + avatarUrl + '" class="conv-avatar" onerror="this.src=\'{{ asset("images/user.jpg") }}\'">' +
                        '<div class="conv-info"><div class="conv-name">' + name + '</div><div class="conv-preview">' + email + '</div></div>' +
                        badge +
                        '</div>';
                });
            }
            document.getElementById('convList').innerHTML = html;
        });
    }

    // ── Mở hội thoại ──
    function openConv(userId, name, email, avatarUrl) {
        activeUserId = userId;
        lastMsgId = 0;
        document.getElementById('chatEmpty').style.display  = 'none';
        document.getElementById('chatActive').style.display = 'flex';
        document.getElementById('activeName').textContent   = name;
        document.getElementById('activeEmail').textContent  = email;
        document.getElementById('activeAvatar').src         = avatarUrl;
        document.getElementById('chatMessages').innerHTML   = '';
        loadConversations(); // refresh highlight active
        loadMessages();
        if (msgPoll) clearInterval(msgPoll);
        msgPoll = setInterval(loadMessages, 3500);
    }

    // ── Load tin nhắn ──
    function loadMessages() {
        if (!activeUserId) return;
        fetch('/staff/chat/conversation/' + activeUserId + '?after=' + lastMsgId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(function(msgs) {
            msgs.forEach(function(m) {
                appendMessage(m);
                if (m.id > lastMsgId) lastMsgId = m.id;
            });
        });
    }

    function appendMessage(m) {
        var container = document.getElementById('chatMessages');
        var row = document.createElement('div');
        row.className = 'msg-row ' + m.sender;
        var time = m.created_at ? m.created_at.substring(11,16) : '';
        var html = '<div class="msg-bubble">' + escHtml(m.message || '');
        if (m.image_url) {
            html += '<img class="msg-image" src="' + escAttr(m.image_url) + '" alt="Ảnh đính kèm" loading="lazy">';
        }
        html += '</div><span class="msg-time">' + time + '</span>';
        row.innerHTML = html;
        container.appendChild(row);
        container.scrollTop = container.scrollHeight;
    }

    // ── Gửi phản hồi ──
    function sendReply() {
        var input = document.getElementById('replyInput');
        var imageInput = document.getElementById('replyImageInput');
        var imageHint = document.getElementById('replyImageHint');
        var text  = input.value.trim();
        var imageFile = imageInput && imageInput.files.length ? imageInput.files[0] : staffPastedImageFile;
        if ((!text && !imageFile) || !activeUserId) return;
        input.value = '';

        var formData = new FormData();
        formData.append('message', text);
        if (imageFile) {
            formData.append('image', imageFile);
        }

        fetch('/staff/chat/reply/' + activeUserId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            });
        })
        .then(function(result) {
            if (!result.ok) {
                alert(result.data && result.data.message ? result.data.message : 'Không thể gửi phản hồi.');
                return;
            }

            var data = result.data;
            appendMessage(data);
            if (data.id > lastMsgId) lastMsgId = data.id;

            if (imageInput) {
                imageInput.value = '';
            }
            staffPastedImageFile = null;

            if (imageHint) {
                imageHint.textContent = '';
            }
        });
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function escAttr(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    var replyImageInput = document.getElementById('replyImageInput');
    var replyInput = document.getElementById('replyInput');
    var replyImageHint = document.getElementById('replyImageHint');

    if (replyImageInput && replyImageHint) {
        replyImageInput.addEventListener('change', function () {
            var file = this.files && this.files.length ? this.files[0] : null;
            staffPastedImageFile = null;
            replyImageHint.textContent = file ? ('Đã chọn ảnh: ' + file.name) : '';
        });
    }

    if (replyInput && replyImageHint) {
        replyInput.addEventListener('paste', function (event) {
            var pastedImage = extractClipboardImage(event);
            if (!pastedImage) return;

            event.preventDefault();
            staffPastedImageFile = pastedImage;

            if (replyImageInput) {
                replyImageInput.value = '';
            }

            replyImageHint.textContent = 'Đã dán ảnh từ clipboard.';
        });
    }

    // ── Khởi động ──
    loadConversations();
    convPoll = setInterval(loadConversations, 5000);
</script>
@endsection
