<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Choy AI - Trợ lý AI Choy's Cafe</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand mr-3" href="{{ url('/') }}">
                <img src="/images/logo.png" style="height:72px;width:auto;object-fit:contain;">
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
                <span class="oi oi-menu"></span> Menu
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                    @if(!(auth()->check() && auth()->user()->isStaff()))
                        <li class="nav-item"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
                    @endif
                    @auth
                        @if(!auth()->user()->isStaff())
                            <li class="nav-item"><a href="{{ route('orders.history') }}" class="nav-link">Lịch sử đơn hàng</a></li>
                        @endif
                    @endauth
                    @guest
                        <li class="nav-item"><a href="{{ url('/login') }}" class="nav-link">Đăng nhập</a></li>
                    @endguest
                    <li class="nav-item"><a href="{{ route('support') }}" class="nav-link">Hỗ trợ</a></li>
                    <li class="nav-item active"><a href="{{ route('ai-chat.index') }}" class="nav-link">Choy AI</a></li>
                    <li class="nav-item flex-spacer"></li>
                    <li class="nav-item cart">
                        <a href="/cart" class="nav-link">
                            <span class="icon icon-shopping_cart"></span>
                            <span class="bag"><small id="cart-count">0</small></span>
                        </a>
                    </li>

                    @if(Auth::check())
                        <li class="nav-item user-dropdown-wrapper">
                            <div class="user-dropdown-container">
                                <button class="user-avatar-btn" type="button" id="userMenuBtn">
                                    @if(Auth::user()->avatar_url)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar_url) }}" class="user-avatar">
                                    @else
                                        <img src="{{ asset('images/user.jpg') }}" class="user-avatar">
                                    @endif
                                </button>
                                <div class="user-dropdown-menu" id="userDropdownMenu">
                                    <div class="dropdown-header-info">
                                        <img src="{{ Auth::user()->avatar_url ? asset('storage/' . Auth::user()->avatar_url) : asset('images/user.jpg') }}" class="dropdown-avatar">
                                        <div class="user-details">
                                            <p class="user-name">{{ Auth::user()->name }}</p>
                                            <p class="user-role"><span class="badge-customer">Khách hàng</span></p>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="/profile" class="dropdown-link">Hồ sơ</a>
                                    <div class="dropdown-divider"></div>
                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-link logout-btn">Đăng xuất</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <section class="ai-chat-section">
        <div class="container ai-chat-container">

            <!-- HERO -->
            <div class="ai-hero text-center">
                <div class="ai-hero-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <span class="ai-kicker">Trợ lý thông minh</span>
                <h1>Choy AI</h1>
                <p>Hỏi tôi về menu, giá cả, cách đặt hàng hoặc bất kỳ điều gì về Choy's Cafe. Tôi ở đây để giúp bạn!</p>
            </div>

            <!-- CHAT SHELL -->
            <div class="ai-chat-shell">

                <!-- Header -->
                <div class="ai-chat-header">
                    <div class="ai-chat-header-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="ai-chat-header-copy">
                        <h3>Choy AI</h3>
                        <span class="ai-status-dot"></span>
                        <p>Chuyên gia về Choy's Cafe · Trả lời tức thì</p>
                    </div>
                    <button class="ai-clear-btn" onclick="clearAiChat()" title="Xóa lịch sử hội thoại">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>

                <!-- Messages -->
                <div id="aiChatMessages" class="ai-chat-messages">
                    <div class="ai-msg-row ai">
                        <div class="ai-msg-avatar"><i class="fas fa-robot"></i></div>
                        <div class="ai-msg-bubble">
                            Xin chào! Tôi là <strong>Choy AI</strong> — trợ lý ảo của Choy's Cafe. 🎉<br><br>
                            Tôi có thể giúp bạn về:
                            <ul style="margin: 8px 0 0; padding-left: 18px; line-height: 1.9;">
                                <li>Menu & giá cả các món</li>
                                <li>Cách đặt hàng & thanh toán</li>
                                <li>Giao hàng & chính sách hủy đơn</li>
                                <li>Gợi ý món phù hợp</li>
                            </ul>
                            Bạn muốn hỏi gì?
                        </div>
                    </div>
                </div>

                <!-- Suggested questions -->
                <div class="ai-suggestions" id="aiSuggestions">
                    <button class="ai-suggestion-btn" onclick="sendSuggestion(this)">☕ Menu cà phê có gì?</button>
                    <button class="ai-suggestion-btn" onclick="sendSuggestion(this)">🧋 Trà sữa nào ngon nhất?</button>
                    <button class="ai-suggestion-btn" onclick="sendSuggestion(this)">💳 Thanh toán bằng cách nào?</button>
                    <button class="ai-suggestion-btn" onclick="sendSuggestion(this)">🚚 Giao hàng bao lâu?</button>
                    <button class="ai-suggestion-btn" onclick="sendSuggestion(this)">❌ Hủy đơn được không?</button>
                    <button class="ai-suggestion-btn" onclick="sendSuggestion(this)">🎂 Bánh & snack có gì?</button>
                </div>

                <!-- Input -->
                <div class="ai-chat-input-row">
                    <input
                        id="aiChatInput"
                        type="text"
                        placeholder="Hỏi về menu, giá cả, đặt hàng..."
                        maxlength="500"
                        onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); sendAiMessage(); }"
                    >
                    <button type="button" id="aiChatSendBtn" onclick="sendAiMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>

                <div class="ai-disclaimer">
                    <i class="fas fa-info-circle"></i>
                    Choy AI chỉ trả lời các câu hỏi liên quan đến Choy's Cafe.
                </div>
            </div>

        </div>
    </section>

    <style>
        .ai-chat-section {
            padding: 110px 0 90px;
            background: #1a1009;
            min-height: 100vh;
        }

        .ai-chat-container {
            max-width: 780px;
        }

        /* Hero */
        .ai-hero {
            margin-bottom: 48px;
        }

        .ai-hero-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c8a26b, #a07040);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 12px 32px rgba(200,162,107,.3);
        }

        .ai-hero-icon i {
            font-size: 28px;
            color: #fff;
        }

        .ai-kicker {
            display: inline-block;
            margin-bottom: 12px;
            color: #c8a26b;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
        }

        .ai-hero h1 {
            font-size: 46px;
            color: #fff;
            margin-bottom: 14px;
            font-weight: 700;
        }

        .ai-hero p {
            color: rgba(255,255,255,.7);
            font-size: 16px;
            line-height: 1.8;
            max-width: 560px;
            margin: 0 auto;
        }

        /* Chat shell */
        .ai-chat-shell {
            border: 1px solid rgba(200,162,107,.2);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,.065), rgba(255,255,255,.03));
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 24px 50px rgba(8,4,1,.3);
        }

        /* Header */
        .ai-chat-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.03);
        }

        .ai-chat-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #c8a26b, #a07040);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(200,162,107,.25);
        }

        .ai-chat-header-copy {
            flex: 1;
        }

        .ai-chat-header-copy h3 {
            margin: 0 0 3px;
            color: #fff;
            font-size: 17px;
            font-weight: 600;
        }

        .ai-chat-header-copy p {
            margin: 0;
            color: rgba(255,255,255,.55);
            font-size: 12px;
        }

        .ai-status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            margin-right: 6px;
            box-shadow: 0 0 6px rgba(74,222,128,.6);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        .ai-clear-btn {
            border: none;
            background: rgba(255,255,255,.07);
            color: rgba(255,255,255,.5);
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s, color .2s;
        }

        .ai-clear-btn:hover {
            background: rgba(255,100,100,.15);
            color: #ff7070;
        }

        /* Messages */
        .ai-chat-messages {
            flex: 1;
            min-height: 380px;
            max-height: 520px;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .ai-chat-messages::-webkit-scrollbar { width: 4px; }
        .ai-chat-messages::-webkit-scrollbar-track { background: transparent; }
        .ai-chat-messages::-webkit-scrollbar-thumb { background: rgba(200,162,107,.3); border-radius: 4px; }

        .ai-msg-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .ai-msg-row.user {
            flex-direction: row-reverse;
        }

        .ai-msg-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(200,162,107,.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #c8a26b;
            flex-shrink: 0;
        }

        .ai-msg-row.user .ai-msg-avatar {
            background: rgba(200,162,107,.25);
        }

        .ai-msg-bubble {
            max-width: 78%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.65;
            word-break: break-word;
        }

        .ai-msg-row.ai .ai-msg-bubble {
            background: rgba(255,255,255,.1);
            color: #fff;
            border-bottom-left-radius: 4px;
        }

        .ai-msg-row.user .ai-msg-bubble {
            background: linear-gradient(135deg, #c8a26b, #b88e54);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        /* Typing indicator */
        .ai-typing {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 16px;
            background: rgba(255,255,255,.1);
            border-radius: 16px;
            border-bottom-left-radius: 4px;
        }

        .ai-typing span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255,255,255,.55);
            animation: typing-bounce 1.2s infinite;
        }

        .ai-typing span:nth-child(2) { animation-delay: .2s; }
        .ai-typing span:nth-child(3) { animation-delay: .4s; }

        @keyframes typing-bounce {
            0%, 60%, 100% { transform: translateY(0); opacity: .5; }
            30% { transform: translateY(-6px); opacity: 1; }
        }

        /* Suggestions */
        .ai-suggestions {
            padding: 12px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-top: 1px solid rgba(255,255,255,.06);
            background: rgba(255,255,255,.02);
        }

        .ai-suggestion-btn {
            border: 1px solid rgba(200,162,107,.3);
            background: rgba(200,162,107,.08);
            color: rgba(255,255,255,.8);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12px;
            cursor: pointer;
            transition: background .2s, border-color .2s, color .2s;
        }

        .ai-suggestion-btn:hover {
            background: rgba(200,162,107,.2);
            border-color: rgba(200,162,107,.6);
            color: #fff;
        }

        /* Input row */
        .ai-chat-input-row {
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 14px 16px;
            display: flex;
            gap: 8px;
            background: rgba(255,255,255,.025);
        }

        .ai-chat-input-row input {
            flex: 1;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(200,162,107,.26);
            border-radius: 12px;
            color: #fff;
            padding: 12px 16px;
            font-size: 13px;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .ai-chat-input-row input::placeholder { color: rgba(255,255,255,.4); }

        .ai-chat-input-row input:focus {
            border-color: rgba(200,162,107,.6);
            box-shadow: 0 0 0 3px rgba(200,162,107,.14);
            background: rgba(255,255,255,.1);
        }

        .ai-chat-input-row button {
            border: none;
            border-radius: 12px;
            width: 48px;
            background: linear-gradient(135deg, #c8a26b, #b88e54);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(200,162,107,.22);
            transition: transform .2s, box-shadow .2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ai-chat-input-row button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(200,162,107,.3);
        }

        .ai-chat-input-row button:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }

        /* Disclaimer */
        .ai-disclaimer {
            padding: 10px 20px;
            text-align: center;
            font-size: 11px;
            color: rgba(255,255,255,.3);
            border-top: 1px solid rgba(255,255,255,.05);
        }

        .ai-disclaimer i { margin-right: 4px; }

        @media (max-width: 600px) {
            .ai-hero h1 { font-size: 32px; }
            .ai-chat-messages { min-height: 300px; max-height: 400px; }
            .ai-msg-bubble { max-width: 88%; }
        }
    </style>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        let aiIsSending = false;

        function scrollToBottom() {
            const el = document.getElementById('aiChatMessages');
            el.scrollTop = el.scrollHeight;
        }

        function appendMessage(role, html) {
            const container = document.getElementById('aiChatMessages');
            const row = document.createElement('div');
            row.className = 'ai-msg-row ' + (role === 'user' ? 'user' : 'ai');

            const avatar = document.createElement('div');
            avatar.className = 'ai-msg-avatar';
            avatar.innerHTML = role === 'user'
                ? '<i class="fas fa-user"></i>'
                : '<i class="fas fa-robot"></i>';

            const bubble = document.createElement('div');
            bubble.className = 'ai-msg-bubble';
            bubble.innerHTML = html;

            row.appendChild(avatar);
            row.appendChild(bubble);
            container.appendChild(row);
            scrollToBottom();
            return row;
        }

        function showTyping() {
            const container = document.getElementById('aiChatMessages');
            const row = document.createElement('div');
            row.className = 'ai-msg-row ai';
            row.id = 'aiTypingIndicator';

            const avatar = document.createElement('div');
            avatar.className = 'ai-msg-avatar';
            avatar.innerHTML = '<i class="fas fa-robot"></i>';

            const bubble = document.createElement('div');
            bubble.className = 'ai-typing';
            bubble.innerHTML = '<span></span><span></span><span></span>';

            row.appendChild(avatar);
            row.appendChild(bubble);
            container.appendChild(row);
            scrollToBottom();
        }

        function hideTyping() {
            const el = document.getElementById('aiTypingIndicator');
            if (el) el.remove();
        }

        function hideSuggestions() {
            const s = document.getElementById('aiSuggestions');
            if (s) s.style.display = 'none';
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');
        }

        function sendAiMessage() {
            if (aiIsSending) return;

            const input = document.getElementById('aiChatInput');
            const message = input.value.trim();
            if (!message) return;

            input.value = '';
            hideSuggestions();
            aiIsSending = true;
            document.getElementById('aiChatSendBtn').disabled = true;

            appendMessage('user', escapeHtml(message));
            showTyping();

            fetch('{{ route("ai-chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({ message }),
            })
            .then(r => r.json())
            .then(data => {
                hideTyping();
                appendMessage('ai', escapeHtml(data.reply || 'Xin lỗi, không thể nhận phản hồi.'));
            })
            .catch(() => {
                hideTyping();
                appendMessage('ai', 'Xin lỗi, đã xảy ra lỗi kết nối. Vui lòng thử lại.');
            })
            .finally(() => {
                aiIsSending = false;
                document.getElementById('aiChatSendBtn').disabled = false;
                document.getElementById('aiChatInput').focus();
            });
        }

        function sendSuggestion(btn) {
            const text = btn.textContent.replace(/^[\u{1F300}-\u{1FFFF} ]/u, '').trim();
            document.getElementById('aiChatInput').value = text;
            sendAiMessage();
        }

        function clearAiChat() {
            if (!confirm('Xóa toàn bộ lịch sử hội thoại?')) return;

            fetch('{{ route("ai-chat.clear") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            });

            const container = document.getElementById('aiChatMessages');
            container.innerHTML = '';
            appendMessage('ai', 'Hội thoại đã được xóa. Tôi có thể giúp gì cho bạn?');

            const s = document.getElementById('aiSuggestions');
            if (s) s.style.display = 'flex';
        }

        // User dropdown
        var btn = document.getElementById('userMenuBtn');
        var menu = document.getElementById('userDropdownMenu');
        if (btn && menu) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('active');
            });
            document.addEventListener('click', function () { menu.classList.remove('active'); });
        }
    </script>
</body>

</html>
