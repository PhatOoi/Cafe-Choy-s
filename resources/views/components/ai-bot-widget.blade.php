{{-- ═══════════════════════════════════════════════════════════
     Floating AI Bot Widget — hiển thị trên mọi trang user.
     Mode AI  : Gửi đến /widget/ai-send (public, không cần auth)
     Mode Human: Gửi đến /chat/send (yêu cầu auth)
     ═══════════════════════════════════════════════════════════ --}}

<div id="aiBotWidget">

    {{-- Floating trigger button --}}
    <button id="aiBotTrigger" onclick="aiBotToggle()" title="Chat với Choy AI">
        <i class="fas fa-robot" id="aiBotTriggerIcon"></i>
        <span class="aibot-badge">?</span>
    </button>

    {{-- Chat panel --}}
    <div id="aiBotPanel" class="aibot-panel aibot-hidden">

        {{-- Header --}}
        <div class="aibot-header" id="aiBotHeader">
            <div class="aibot-header-left">
                <div class="aibot-header-avatar" id="aiBotHeaderAvatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <div class="aibot-header-name" id="aiBotHeaderName">Choy AI</div>
                    <div class="aibot-header-status">
                        <span class="aibot-status-dot" id="aiBotStatusDot"></span>
                        <span id="aiBotStatusText">Trợ lý AI</span>
                    </div>
                </div>
            </div>
            <div class="aibot-header-actions">
                <button class="aibot-icon-btn" onclick="aiBotClear()" title="Xóa lịch sử">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button class="aibot-icon-btn" onclick="aiBotToggle()" title="Đóng">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="aiBotMessages" class="aibot-messages">
            <div class="aibot-msg-row aibot-ai">
                <div class="aibot-avatar"><i class="fas fa-robot"></i></div>
                <div class="aibot-bubble">
                    Xin chào! Tôi là <strong>Choy AI</strong> 👋<br>
                    Hỏi tôi về menu, giá cả, đặt hàng hoặc bất cứ điều gì về Choy's Cafe nhé!
                </div>
            </div>
        </div>

        {{-- Escalation prompt (ẩn mặc định) --}}
        <div id="aiBotEscalatePrompt" class="aibot-escalate-prompt aibot-hidden">
            <p>Bạn muốn chat trực tiếp với nhân viên hỗ trợ không?</p>
            <div class="aibot-escalate-btns">
                <button onclick="aiBotSwitchToHuman()">✅ Có, kết nối ngay</button>
                <button class="aibot-btn-no" onclick="aiBotDismissEscalate()">Không, cảm ơn</button>
            </div>
        </div>

        {{-- Input --}}
        <div class="aibot-input-row" id="aiBotInputRow">
            <input
                id="aiBotInput"
                type="text"
                placeholder="Nhập câu hỏi..."
                maxlength="500"
                onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); aiBotSend(); }"
            >
            <button id="aiBotSendBtn" onclick="aiBotSend()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

        {{-- Mode bar --}}
        <div class="aibot-modebar">
            <button class="aibot-mode-btn aibot-mode-active" id="aiBotModeAI" onclick="aiBotSetMode('ai')">
                <i class="fas fa-robot"></i> AI
            </button>
            @auth
            @if(!auth()->user()->isStaff() && !auth()->user()->isAdmin())
            <button class="aibot-mode-btn" id="aiBotModeHuman" onclick="aiBotSetMode('human')">
                <i class="fas fa-headset"></i> Nhân viên
            </button>
            @endif
            @endauth
        </div>
    </div>
</div>

<style>
#aiBotWidget {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9999;
    font-family: 'Poppins', sans-serif;
}

/* Trigger button */
#aiBotTrigger {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #c8a26b, #a07040);
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(200,162,107,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: transform .25s, box-shadow .25s;
}

#aiBotTrigger:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 32px rgba(200,162,107,.55);
}

.aibot-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #ff6b6b;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #1a1009;
    animation: aibot-pulse 2.5s infinite;
}

@keyframes aibot-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

/* Panel */
.aibot-panel {
    position: absolute;
    bottom: 72px;
    right: 0;
    width: 340px;
    border-radius: 18px;
    background: #1e140a;
    border: 1px solid rgba(200,162,107,.22);
    box-shadow: 0 24px 56px rgba(0,0,0,.55);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: opacity .25s, transform .25s;
}

.aibot-hidden {
    opacity: 0;
    pointer-events: none;
    transform: translateY(12px) scale(.97);
}

/* Header */
.aibot-header {
    padding: 14px 14px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.04);
}

.aibot-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.aibot-header-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #c8a26b, #a07040);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    flex-shrink: 0;
}

.aibot-header-avatar.aibot-human-avatar {
    background: linear-gradient(135deg, #4e9eff, #2a6bcf);
}

.aibot-header-name {
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
}

.aibot-header-status {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: rgba(255,255,255,.5);
}

.aibot-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4ade80;
    display: inline-block;
    animation: aibot-dotpulse 2s infinite;
}

.aibot-status-dot.aibot-dot-human {
    background: #60b0ff;
}

@keyframes aibot-dotpulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .4; }
}

.aibot-header-actions {
    display: flex;
    gap: 4px;
}

.aibot-icon-btn {
    width: 30px;
    height: 30px;
    border: none;
    background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.5);
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s, color .2s;
}

.aibot-icon-btn:hover {
    background: rgba(255,100,100,.2);
    color: #ff7070;
}

/* Messages */
.aibot-messages {
    flex: 1;
    min-height: 260px;
    max-height: 340px;
    overflow-y: auto;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.aibot-messages::-webkit-scrollbar { width: 3px; }
.aibot-messages::-webkit-scrollbar-thumb { background: rgba(200,162,107,.3); border-radius: 3px; }

.aibot-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 7px;
}

.aibot-msg-row.aibot-user {
    flex-direction: row-reverse;
}

.aibot-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(200,162,107,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #c8a26b;
    flex-shrink: 0;
}

.aibot-msg-row.aibot-human .aibot-avatar {
    background: rgba(78,158,255,.18);
    color: #4e9eff;
}

.aibot-bubble {
    max-width: 80%;
    padding: 9px 13px;
    border-radius: 14px;
    font-size: 12.5px;
    line-height: 1.6;
    word-break: break-word;
    color: #fff;
}

.aibot-msg-row.aibot-ai .aibot-bubble,
.aibot-msg-row.aibot-human .aibot-bubble {
    background: rgba(255,255,255,.1);
    border-bottom-left-radius: 3px;
}

.aibot-msg-row.aibot-user .aibot-bubble {
    background: linear-gradient(135deg, #c8a26b, #b88e54);
    border-bottom-right-radius: 3px;
}

/* Typing */
.aibot-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 9px 13px;
    background: rgba(255,255,255,.1);
    border-radius: 14px;
    border-bottom-left-radius: 3px;
    max-width: 60px;
}

.aibot-typing span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255,255,255,.5);
    animation: aibot-bounce 1.2s infinite;
}
.aibot-typing span:nth-child(2) { animation-delay: .2s; }
.aibot-typing span:nth-child(3) { animation-delay: .4s; }

@keyframes aibot-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: .5; }
    30% { transform: translateY(-5px); opacity: 1; }
}

/* Escalate prompt */
.aibot-escalate-prompt {
    margin: 0 12px 8px;
    padding: 12px 14px;
    background: rgba(255,180,60,.1);
    border: 1px solid rgba(255,180,60,.3);
    border-radius: 12px;
    font-size: 12.5px;
    color: rgba(255,255,255,.9);
}

.aibot-escalate-prompt p {
    margin: 0 0 10px;
    line-height: 1.5;
}

.aibot-escalate-btns {
    display: flex;
    gap: 8px;
}

.aibot-escalate-btns button {
    flex: 1;
    border: none;
    border-radius: 8px;
    padding: 7px 6px;
    font-size: 11.5px;
    cursor: pointer;
    font-weight: 600;
    transition: opacity .2s;
}

.aibot-escalate-btns button:first-child {
    background: linear-gradient(135deg, #4e9eff, #2a6bcf);
    color: #fff;
}

.aibot-btn-no {
    background: rgba(255,255,255,.1) !important;
    color: rgba(255,255,255,.7) !important;
}

.aibot-escalate-btns button:hover { opacity: .85; }

/* Input */
.aibot-input-row {
    padding: 10px 12px;
    display: flex;
    gap: 7px;
    border-top: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.02);
}

.aibot-input-row input {
    flex: 1;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(200,162,107,.25);
    border-radius: 10px;
    color: #fff;
    padding: 9px 12px;
    font-size: 12.5px;
    outline: none;
    transition: border-color .2s;
    font-family: 'Poppins', sans-serif;
}

.aibot-input-row input::placeholder { color: rgba(255,255,255,.35); }
.aibot-input-row input:focus { border-color: rgba(200,162,107,.55); }

.aibot-input-row.aibot-human-input input {
    border-color: rgba(78,158,255,.3);
}

.aibot-input-row.aibot-human-input input:focus {
    border-color: rgba(78,158,255,.65);
}

.aibot-input-row button {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #c8a26b, #b88e54);
    color: #fff;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .2s;
    flex-shrink: 0;
}

.aibot-input-row button:hover { transform: translateY(-1px); }
.aibot-input-row button:disabled { opacity: .5; cursor: not-allowed; transform: none; }

.aibot-input-row.aibot-human-input button {
    background: linear-gradient(135deg, #4e9eff, #2a6bcf);
}

/* Mode bar */
.aibot-modebar {
    display: flex;
    gap: 0;
    border-top: 1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.02);
}

.aibot-mode-btn {
    flex: 1;
    border: none;
    background: none;
    color: rgba(255,255,255,.4);
    padding: 8px;
    font-size: 11px;
    cursor: pointer;
    transition: color .2s, background .2s;
    font-family: 'Poppins', sans-serif;
}

.aibot-mode-btn:hover {
    color: rgba(255,255,255,.75);
    background: rgba(255,255,255,.05);
}

.aibot-mode-active {
    color: #c8a26b !important;
    background: rgba(200,162,107,.1) !important;
    font-weight: 600;
}

#aiBotModeHuman.aibot-mode-active {
    color: #4e9eff !important;
    background: rgba(78,158,255,.1) !important;
}

@media (max-width: 480px) {
    .aibot-panel { width: calc(100vw - 40px); right: -4px; }
    #aiBotWidget { bottom: 16px; right: 16px; }
}
</style>

<script>
(function () {
    var CSRF = '{{ csrf_token() }}';
    var isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    var mode = 'ai'; // 'ai' | 'human'
    var humanPollInterval = null;
    var humanLastId = 0;
    var isSending = false;
    var pendingOrderDraft = null;

    window.aiBotToggle = function () {
        var panel = document.getElementById('aiBotPanel');
        panel.classList.toggle('aibot-hidden');
        if (!panel.classList.contains('aibot-hidden')) {
            document.getElementById('aiBotInput').focus();
        }
    };

    window.aiBotSetMode = function (m) {
        if (mode === m) return;
        mode = m;
        updateModeUI();

        if (m === 'ai') {
            stopHumanPoll();
        } else {
            if (!isLoggedIn) {
                appendMsg('ai', 'Bạn cần <a href="/login" style="color:#c8a26b;">đăng nhập</a> để chat với nhân viên.');
                mode = 'ai';
                updateModeUI();
                return;
            }
            humanLastId = 0;
            startHumanPoll();
        }
    };

    window.aiBotSend = function () {
        if (isSending) return;
        var input = document.getElementById('aiBotInput');
        var msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        isSending = true;
        document.getElementById('aiBotSendBtn').disabled = true;

        appendMsg('user', escHtml(msg));
        hideEscalate();

        if (mode === 'ai') {
            showTyping();
            fetch('/widget/ai-send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ message: msg }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                hideTyping();
                appendMsg('ai', escHtml(data.reply || 'Xin lỗi, thử lại sau.'));
                if (data.escalate) showEscalate();
                if (data.orderDraft) {
                    pendingOrderDraft = data.orderDraft;
                    renderOrderDraftCard(data.orderDraft);
                }
            })
            .catch(function () {
                hideTyping();
                appendMsg('ai', 'Lỗi kết nối, thử lại sau.');
            })
            .finally(function () {
                isSending = false;
                document.getElementById('aiBotSendBtn').disabled = false;
                document.getElementById('aiBotInput').focus();
            });
        } else {
            // Human mode
            fetch('/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ message: msg }),
            })
            .then(function () {})
            .catch(function () {
                appendMsg('ai', 'Lỗi gửi tin nhắn. Vui lòng thử lại.');
            })
            .finally(function () {
                isSending = false;
                document.getElementById('aiBotSendBtn').disabled = false;
                document.getElementById('aiBotInput').focus();
            });
        }
    };

    window.aiBotSwitchToHuman = function () {
        hideEscalate();
        if (!isLoggedIn) {
            appendMsg('ai', 'Bạn cần <a href="/login" style="color:#c8a26b;">đăng nhập</a> để chat với nhân viên hỗ trợ.');
            return;
        }
        aiBotSetMode('human');
        appendMsg('human', '🔗 Đã kết nối với nhân viên hỗ trợ. Nhân viên sẽ phản hồi sớm nhất có thể.');
        document.getElementById('aiBotModeAI').classList.remove('aibot-mode-active');
        document.getElementById('aiBotModeHuman').classList.add('aibot-mode-active');
    };

    window.aiBotDismissEscalate = function () {
        hideEscalate();
        appendMsg('ai', 'Được rồi! Tôi vẫn ở đây để hỗ trợ bạn 😊');
    };

    window.aiBotClear = function () {
        var container = document.getElementById('aiBotMessages');
        container.innerHTML = '';
        appendMsg('ai', 'Hội thoại đã được xóa. Tôi có thể giúp gì cho bạn?');
        hideEscalate();
        pendingOrderDraft = null;
        fetch('/widget/ai-clear', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
    };

    window.aiBotConfirmOrderDraft = function () {
        if (!pendingOrderDraft) {
            appendMsg('ai', 'Không tìm thấy bill nháp. Vui lòng yêu cầu AI tạo lại bill.');
            return;
        }

        if (!isLoggedIn) {
            appendMsg('ai', 'Bạn cần <a href="/login" style="color:#c8a26b;">đăng nhập</a> để xác nhận đặt đơn.');
            return;
        }

        showTyping();
        fetch('/widget/ai-order/confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({}),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            hideTyping();
            if (data.success) {
                pendingOrderDraft = null;
                appendMsg('ai', escHtml(data.message || 'Đặt đơn thành công.'));

                if (data.payment_method === 'bank_transfer') {
                    if (data.qr && data.qr.image_url) {
                        renderQrPaymentCard(data.qr);
                        appendMsg('ai', 'Sau khi chuyển khoản xong, bạn nhắn "đã chuyển khoản" để tôi hỗ trợ kiểm tra nhanh cho bạn nhé.');
                    } else {
                        appendMsg('ai', 'Đơn của bạn đã tạo ở trạng thái chờ xác nhận chuyển khoản. Vui lòng vào giỏ hàng để quét QR và xác nhận thanh toán.');
                    }
                }
            } else {
                appendMsg('ai', escHtml(data.message || 'Không thể đặt đơn. Vui lòng chỉnh lại bill.'));
            }
        })
        .catch(function () {
            hideTyping();
            appendMsg('ai', 'Lỗi khi xác nhận đặt đơn. Vui lòng thử lại.');
        });
    };

    window.aiBotEditOrderDraft = function () {
        appendMsg('ai', 'Đã rõ. Bạn hãy nhắn phần cần chỉnh (món/số lượng/size/topping/thanh toán), tôi sẽ cập nhật bill nháp mới cho bạn.');
    };

    // ── Private helpers ──────────────────────────────────────

    function updateModeUI() {
        var aiBtn    = document.getElementById('aiBotModeAI');
        var humanBtn = document.getElementById('aiBotModeHuman');
        var avatar   = document.getElementById('aiBotHeaderAvatar');
        var name     = document.getElementById('aiBotHeaderName');
        var dot      = document.getElementById('aiBotStatusDot');
        var statusTx = document.getElementById('aiBotStatusText');
        var inputRow = document.getElementById('aiBotInputRow');

        if (aiBtn) aiBtn.classList.toggle('aibot-mode-active', mode === 'ai');
        if (humanBtn) humanBtn.classList.toggle('aibot-mode-active', mode === 'human');

        if (mode === 'ai') {
            avatar.innerHTML = '<i class="fas fa-robot"></i>';
            avatar.classList.remove('aibot-human-avatar');
            name.textContent = 'Choy AI';
            dot.classList.remove('aibot-dot-human');
            statusTx.textContent = 'Trợ lý AI';
            inputRow.classList.remove('aibot-human-input');
        } else {
            avatar.innerHTML = '<i class="fas fa-headset"></i>';
            avatar.classList.add('aibot-human-avatar');
            name.textContent = 'Nhân viên hỗ trợ';
            dot.classList.add('aibot-dot-human');
            statusTx.textContent = 'Hỗ trợ trực tiếp';
            inputRow.classList.add('aibot-human-input');
        }
    }

    function appendMsg(role, html) {
        var container = document.getElementById('aiBotMessages');
        var row = document.createElement('div');
        row.className = 'aibot-msg-row aibot-' + (role === 'user' ? 'user' : (role === 'human' ? 'human' : 'ai'));

        var av = document.createElement('div');
        av.className = 'aibot-avatar';
        if (role === 'user') {
            av.innerHTML = '<i class="fas fa-user"></i>';
        } else if (role === 'human') {
            av.innerHTML = '<i class="fas fa-headset"></i>';
        } else {
            av.innerHTML = '<i class="fas fa-robot"></i>';
        }

        var bubble = document.createElement('div');
        bubble.className = 'aibot-bubble';
        bubble.innerHTML = html;

        row.appendChild(av);
        row.appendChild(bubble);
        container.appendChild(row);
        container.scrollTop = container.scrollHeight;
        return row;
    }

    function renderOrderDraftCard(draft) {
        var items = Array.isArray(draft.items) ? draft.items : [];
        if (items.length === 0) return;

        var lines = items.map(function (item) {
            var parts = [];
            if (item.size) parts.push('Size ' + escHtml(String(item.size)));
            if (item.sugar) parts.push('Đường ' + escHtml(String(item.sugar)));
            if (item.ice) parts.push('Đá ' + escHtml(String(item.ice)));
            if (Array.isArray(item.toppings) && item.toppings.length) {
                parts.push('Topping: ' + escHtml(item.toppings.join(', ')));
            }

            var optionText = parts.length ? '<br><small style="opacity:.75">' + parts.join(' | ') + '</small>' : '';
            return '<li style="margin-bottom:6px;">' + (item.qty || 1) + ' x ' + escHtml(item.name || 'Món') + optionText + '</li>';
        }).join('');

        var paymentLabel = draft.payment_method === 'bank_transfer' ? 'Chuyển khoản QR' : 'Tiền mặt';

        var html = '' +
            '<div style="border:1px solid rgba(200,162,107,.35);border-radius:10px;padding:10px 12px;background:rgba(200,162,107,.07);">' +
            '<div style="font-weight:600;margin-bottom:6px;">Bill nháp - vui lòng xác nhận</div>' +
            '<ul style="margin:0 0 8px 18px;padding:0;">' + lines + '</ul>' +
            '<div style="font-size:12px;opacity:.9;margin-bottom:10px;">Thanh toán: ' + paymentLabel + '</div>' +
            '<div style="display:flex;gap:8px;">' +
            '<button onclick="aiBotConfirmOrderDraft()" style="flex:1;border:none;border-radius:8px;padding:7px 8px;background:linear-gradient(135deg,#4caf50,#2f8e3a);color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Xác nhận đặt đơn</button>' +
            '<button onclick="aiBotEditOrderDraft()" style="flex:1;border:none;border-radius:8px;padding:7px 8px;background:rgba(255,255,255,.12);color:#fff;font-size:12px;font-weight:600;cursor:pointer;">Chỉnh lại bill</button>' +
            '</div>' +
            '</div>';

        appendMsg('ai', html);
    }

    function renderQrPaymentCard(qr) {
        var amount = Number(qr.amount || 0);
        var amountText = Number.isFinite(amount) ? amount.toLocaleString('vi-VN') + ' đ' : '0 đ';

        var html = '' +
            '<div style="border:1px solid rgba(76,175,80,.45);border-radius:12px;padding:12px;background:rgba(76,175,80,.08);">' +
            '<div style="font-weight:700;margin-bottom:8px;">Mã QR thanh toán</div>' +
            '<div style="text-align:center;margin-bottom:10px;">' +
            '<img src="' + escHtml(String(qr.image_url || '')) + '" alt="QR thanh toán" style="width:180px;max-width:100%;border-radius:10px;border:2px solid rgba(255,255,255,.2);background:#fff;padding:6px;">' +
            '</div>' +
            '<div style="font-size:12px;line-height:1.65;">' +
            '<div><b>Ngân hàng:</b> ' + escHtml(String(qr.bank_name || 'Vietcombank')) + '</div>' +
            '<div><b>Thụ hưởng:</b> ' + escHtml(String(qr.account_name || '')) + '</div>' +
            '<div><b>Số tài khoản:</b> ' + escHtml(String(qr.account_number || '')) + '</div>' +
            '<div><b>Số tiền:</b> ' + escHtml(amountText) + '</div>' +
            '<div><b>Nội dung CK:</b> ' + escHtml(String(qr.ref_code || '')) + '</div>' +
            '</div>' +
            '<div style="margin-top:8px;font-size:11px;opacity:.85;">Vui lòng giữ đúng nội dung chuyển khoản để nhân viên xác nhận nhanh hơn.</div>' +
            '</div>';

        appendMsg('ai', html);
    }

    function showTyping() {
        var container = document.getElementById('aiBotMessages');
        var row = document.createElement('div');
        row.className = 'aibot-msg-row aibot-ai';
        row.id = 'aiBotTyping';

        var av = document.createElement('div');
        av.className = 'aibot-avatar';
        av.innerHTML = '<i class="fas fa-robot"></i>';

        var typing = document.createElement('div');
        typing.className = 'aibot-typing';
        typing.innerHTML = '<span></span><span></span><span></span>';

        row.appendChild(av);
        row.appendChild(typing);
        container.appendChild(row);
        container.scrollTop = container.scrollHeight;
    }

    function hideTyping() {
        var el = document.getElementById('aiBotTyping');
        if (el) el.remove();
    }

    function showEscalate() {
        document.getElementById('aiBotEscalatePrompt').classList.remove('aibot-hidden');
    }

    function hideEscalate() {
        document.getElementById('aiBotEscalatePrompt').classList.add('aibot-hidden');
    }

    function escHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    function startHumanPoll() {
        pollHumanMessages();
        humanPollInterval = setInterval(pollHumanMessages, 3000);
    }

    function stopHumanPoll() {
        if (humanPollInterval) {
            clearInterval(humanPollInterval);
            humanPollInterval = null;
        }
    }

    function pollHumanMessages() {
        fetch('/chat/messages?after=' + humanLastId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.reset) { humanLastId = 0; return; }
            (data.messages || []).forEach(function (msg) {
                if (msg.sender === 'staff') {
                    appendMsg('human', escHtml(msg.message));
                }
                if (msg.id > humanLastId) humanLastId = msg.id;
            });
        })
        .catch(function () {});
    }
})();
</script>
