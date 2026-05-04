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
            <input id="aiBotHumanImageInput" type="file" accept="image/*" style="display:none;">
            <button id="aiBotImageBtn" class="aibot-hidden" type="button" onclick="document.getElementById('aiBotHumanImageInput').click()" title="Gửi ảnh cho nhân viên">
                <i class="fas fa-image"></i>
            </button>
            <button id="aiBotSendBtn" onclick="aiBotSend()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div id="aiBotAttachmentHint" class="aibot-attachment-hint aibot-hidden"></div>

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
    cursor: grab;
    box-shadow: 0 8px 24px rgba(200,162,107,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: transform .25s, box-shadow .25s;
    user-select: none;
    -webkit-user-select: none;
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

#aiBotWidget.aibot-side-left .aibot-panel {
    left: 0;
    right: auto;
}

#aiBotWidget.aibot-side-right .aibot-panel {
    right: 0;
    left: auto;
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

.aibot-msg-image {
    width: 100%;
    max-width: 220px;
    border-radius: 10px;
    display: block;
    margin-top: 8px;
    box-shadow: 0 8px 16px rgba(0,0,0,.22);
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

.aibot-quick-action {
    margin: 0 14px 6px 49px;
}

.aibot-quick-action-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(200,162,107,.4);
    background: rgba(200,162,107,.16);
    color: #f5e6cf;
    border-radius: 10px;
    padding: 7px 11px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s, border-color .2s;
}

.aibot-quick-action-link:hover {
    text-decoration: none;
    color: #fff;
    background: rgba(200,162,107,.3);
    border-color: rgba(200,162,107,.65);
}

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

.aibot-attachment-hint {
    min-height: 18px;
    padding: 0 12px 8px;
    font-size: 11px;
    color: rgba(255,255,255,.55);
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
    var AI_DB_JSON_ENDPOINT = '{{ route("widget.ai-db-json") }}';
    var AI_DB_LOCAL_KEY = 'choys_ai_db_snapshot_v1';
    var isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    var mode = 'ai'; // 'ai' | 'human'
    var humanPollInterval = null;
    var humanLastId = 0;
    var isSending = false;
    var humanImageFile = null;
    var waitingEscalateChoice = false;

    function loadAiSnapshotFromLocalStore() {
        try {
            var raw = localStorage.getItem(AI_DB_LOCAL_KEY);
            if (!raw) return null;

            var parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return null;
            if (!parsed.snapshot || !parsed.signature) return null;

            return parsed;
        } catch (e) {
            return null;
        }
    }

    function syncAiSnapshotToLocalStore() {
        return fetch(AI_DB_JSON_ENDPOINT, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || data.ok !== true || !data.snapshot || !data.signature) {
                return null;
            }

            var payload = {
                snapshot: data.snapshot,
                signature: data.signature,
                updated_at: new Date().toISOString(),
            };

            localStorage.setItem(AI_DB_LOCAL_KEY, JSON.stringify(payload));
            return payload;
        })
        .catch(function () { return null; });
    }

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

        if (mode === 'ai') {
            if (!msg) return;

            if (waitingEscalateChoice) {
                var normalized = msg.toLowerCase();
                if (/^(co|có|ok|oke|yes|y|dong y|đồng ý)$/.test(normalized)) {
                    input.value = '';
                    appendMsg('user', escHtml(msg));
                    aiBotSwitchToHuman();
                    return;
                }

                if (/^(khong|không|ko|k|no|khong can|không cần)$/.test(normalized)) {
                    input.value = '';
                    appendMsg('user', escHtml(msg));
                    hideEscalate();
                    appendMsg('ai', 'Dạ oke anh/chị, bé tiếp tục hỗ trợ trong phạm vi thông tin của quán nha.');
                    return;
                }
            }

            input.value = '';
            isSending = true;
            document.getElementById('aiBotSendBtn').disabled = true;

            appendMsg('user', escHtml(msg));
            hideEscalate();

            showTyping();
            var localSnapshot = loadAiSnapshotFromLocalStore();
            var requestBody = { message: msg };
            if (localSnapshot && localSnapshot.snapshot && localSnapshot.signature) {
                requestBody.ai_snapshot = localSnapshot.snapshot;
                requestBody.ai_snapshot_signature = localSnapshot.signature;
            }

            fetch('/widget/ai-send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(requestBody),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                hideTyping();
                appendMsg('ai', escHtml(data.reply || 'Xin lỗi, thử lại sau.'));
                if (data.quick_action) {
                    appendQuickAction(data.quick_action);
                }
                if (data.escalate) showEscalate();
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
            var fileInput = document.getElementById('aiBotHumanImageInput');
            var attachment = (fileInput && fileInput.files && fileInput.files.length) ? fileInput.files[0] : humanImageFile;
            if (!msg && !attachment) return;

            input.value = '';
            isSending = true;
            document.getElementById('aiBotSendBtn').disabled = true;
            hideEscalate();

            var formData = new FormData();
            formData.append('message', msg);
            if (attachment) {
                formData.append('image', attachment);
            }

            fetch('/chat/send', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF },
                body: formData,
            })
            .then(function (r) {
                return r.json().then(function (data) {
                    return { ok: r.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    appendMsg('ai', escHtml((result.data && result.data.message) ? result.data.message : 'Không thể gửi tin nhắn.'));
                    return;
                }

                appendHumanMessage('user', result.data);
                if (result.data.id > humanLastId) humanLastId = result.data.id;

                if (fileInput) {
                    fileInput.value = '';
                }
                humanImageFile = null;
                setAttachmentHint('');
            })
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
        fetch('/widget/ai-clear', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
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
        var imageBtn = document.getElementById('aiBotImageBtn');
        var imageInput = document.getElementById('aiBotHumanImageInput');

        if (aiBtn) aiBtn.classList.toggle('aibot-mode-active', mode === 'ai');
        if (humanBtn) humanBtn.classList.toggle('aibot-mode-active', mode === 'human');

        if (mode === 'ai') {
            avatar.innerHTML = '<i class="fas fa-robot"></i>';
            avatar.classList.remove('aibot-human-avatar');
            name.textContent = 'Choy AI';
            dot.classList.remove('aibot-dot-human');
            statusTx.textContent = 'Trợ lý AI';
            inputRow.classList.remove('aibot-human-input');
            if (imageBtn) imageBtn.classList.add('aibot-hidden');
            if (imageInput) imageInput.value = '';
            humanImageFile = null;
            setAttachmentHint('');
        } else {
            avatar.innerHTML = '<i class="fas fa-headset"></i>';
            avatar.classList.add('aibot-human-avatar');
            name.textContent = 'Nhân viên hỗ trợ';
            dot.classList.add('aibot-dot-human');
            statusTx.textContent = 'Hỗ trợ trực tiếp';
            inputRow.classList.add('aibot-human-input');
            if (imageBtn) imageBtn.classList.remove('aibot-hidden');
        }
    }

    function setAttachmentHint(text) {
        var hint = document.getElementById('aiBotAttachmentHint');
        if (!hint) return;

        hint.textContent = text || '';
        hint.classList.toggle('aibot-hidden', !text);
    }

    function appendHumanMessage(role, msg) {
        var text = msg && msg.message ? escHtml(String(msg.message)) : '';
        var imageHtml = (msg && msg.image_url)
            ? '<img class="aibot-msg-image" src="' + escAttr(String(msg.image_url)) + '" alt="Ảnh đính kèm" loading="lazy">'
            : '';

        appendMsg(role, text + imageHtml);
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

    function appendQuickAction(action) {
        if (!action || typeof action !== 'object') {
            return;
        }

        var url = normalizeActionUrl(action.url || '');
        var label = String(action.label || '').trim();

        if (!url || !label) {
            return;
        }

        var container = document.getElementById('aiBotMessages');
        var wrap = document.createElement('div');
        wrap.className = 'aibot-quick-action';

        var link = document.createElement('a');
        link.className = 'aibot-quick-action-link';
        link.href = url;
        link.innerHTML = '<i class="fas fa-location-arrow"></i><span>' + escHtml(label) + '</span>';

        wrap.appendChild(link);
        container.appendChild(wrap);
        container.scrollTop = container.scrollHeight;
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
        waitingEscalateChoice = true;
    }

    function hideEscalate() {
        document.getElementById('aiBotEscalatePrompt').classList.add('aibot-hidden');
        waitingEscalateChoice = false;
    }

    function escHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    function escAttr(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function normalizeActionUrl(url) {
        var cleaned = String(url || '').trim();
        if (!cleaned) return '';
        if (/^\/\//.test(cleaned)) return '';
        if (/^[a-z]+:/i.test(cleaned)) return '';
        if (cleaned.charAt(0) !== '/') {
            cleaned = '/' + cleaned;
        }

        return cleaned;
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
                    appendHumanMessage('human', msg);
                }
                if (msg.id > humanLastId) humanLastId = msg.id;
            });
        })
        .catch(function () {});
    }

    var aiBotInput = document.getElementById('aiBotInput');
    var aiBotHumanImageInput = document.getElementById('aiBotHumanImageInput');

    if (aiBotHumanImageInput) {
        aiBotHumanImageInput.addEventListener('change', function () {
            var file = (this.files && this.files.length) ? this.files[0] : null;
            humanImageFile = null;
            setAttachmentHint(file ? ('Đã chọn ảnh: ' + file.name) : '');
        });
    }

    if (aiBotInput) {
        aiBotInput.addEventListener('paste', function (event) {
            if (mode !== 'human') return;

            var clipboardData = event.clipboardData || window.clipboardData;
            if (!clipboardData || !clipboardData.items) return;

            for (var i = 0; i < clipboardData.items.length; i++) {
                var item = clipboardData.items[i];
                if (item.kind === 'file' && item.type.indexOf('image/') === 0) {
                    event.preventDefault();
                    humanImageFile = item.getAsFile();
                    if (aiBotHumanImageInput) {
                        aiBotHumanImageInput.value = '';
                    }
                    setAttachmentHint('Đã dán ảnh từ clipboard.');
                    break;
                }
            }
        });
    }

    // ── Drag to reposition ──────────────────────────────────────────
    (function () {
        var widget = document.getElementById('aiBotWidget');
        var trigger = document.getElementById('aiBotTrigger');
        var dragging = false;
        var dragMoved = false;
        var startX, startY, origLeft, origTop;
        var snapGap = 28;
        var currentSide = 'right';

        function getWidgetRect() {
            return widget.getBoundingClientRect();
        }

        function setSideClass(side) {
            currentSide = side === 'left' ? 'left' : 'right';
            widget.classList.toggle('aibot-side-left', currentSide === 'left');
            widget.classList.toggle('aibot-side-right', currentSide === 'right');
        }

        function initPos() {
            // Chuyển từ bottom/right sang top/left để drag hoạt động
            var rect = getWidgetRect();
            widget.style.bottom = 'auto';
            widget.style.right = 'auto';
            widget.style.top = rect.top + 'px';
            widget.style.left = rect.left + 'px';
            setSideClass(rect.left + (rect.width / 2) < (window.innerWidth / 2) ? 'left' : 'right');
        }

        function clamp(val, min, max) {
            return Math.max(min, Math.min(max, val));
        }

        function getVisibleBackToTop() {
            var candidates = document.querySelectorAll('.back-to-top-btn, #backToTopBtn, .js-top');
            for (var i = 0; i < candidates.length; i++) {
                var el = candidates[i];
                var cs = window.getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity || '1') === 0) {
                    continue;
                }
                var r = el.getBoundingClientRect();
                if (r.width > 0 && r.height > 0) return { el: el, rect: r };
            }
            return null;
        }

        function getSnapGap() {
            var target = getVisibleBackToTop();
            if (!target) return snapGap;

            var rightGap = window.innerWidth - target.rect.right;
            var leftGap = target.rect.left;
            var inferred = target.rect.left > (window.innerWidth / 2) ? rightGap : leftGap;

            // Keep spacing in a reasonable range in case the button animates from off-screen.
            return clamp(Math.round(inferred), 12, 48);
        }

        function isOverlapping(a, b) {
            return !(a.right <= b.left || a.left >= b.right || a.bottom <= b.top || a.top >= b.bottom);
        }

        function avoidOverlapWithBackToTop() {
            var target = getVisibleBackToTop();
            if (!target) return;
            var liveGap = getSnapGap();

            var widgetRect = getWidgetRect();
            if (!isOverlapping(widgetRect, target.rect)) return;

            var maxTop = window.innerHeight - widget.offsetHeight - liveGap;
            var movedTop = clamp(target.rect.top - widget.offsetHeight - 12, liveGap, maxTop);
            widget.style.top = movedTop + 'px';

            widgetRect = getWidgetRect();
            if (!isOverlapping(widgetRect, target.rect)) return;

            var buttonCenterX = target.rect.left + (target.rect.width / 2);
            var oppositeSide = buttonCenterX > (window.innerWidth / 2) ? 'left' : 'right';
            snapToSide(oppositeSide);
        }

        function snapToSide(side) {
            if (!widget.style.top || widget.style.top === '' || widget.style.top === 'auto') {
                initPos();
            }

            var liveGap = getSnapGap();

            var topValue = parseInt(widget.style.top, 10);
            var clampedTop = clamp(topValue, liveGap, window.innerHeight - widget.offsetHeight - liveGap);
            var leftValue = side === 'left'
                ? liveGap
                : window.innerWidth - widget.offsetWidth - liveGap;

            widget.style.left = leftValue + 'px';
            widget.style.top = clampedTop + 'px';
            setSideClass(side);
            avoidOverlapWithBackToTop();
        }

        function snapToNearestSide() {
            var rect = getWidgetRect();
            var widgetCenterX = rect.left + (rect.width / 2);
            var side = widgetCenterX < (window.innerWidth / 2) ? 'left' : 'right';
            snapToSide(side);
        }

        function onStart(clientX, clientY) {
            if (!widget.style.top || widget.style.top === '' || widget.style.top === 'auto') {
                initPos();
            }
            dragging = true;
            dragMoved = false;
            startX = clientX;
            startY = clientY;
            origLeft = parseInt(widget.style.left);
            origTop  = parseInt(widget.style.top);
            trigger.style.cursor = 'grabbing';
        }

        function onMove(clientX, clientY) {
            if (!dragging) return;
            var dx = clientX - startX;
            var dy = clientY - startY;
            if (Math.abs(dx) > 4 || Math.abs(dy) > 4) dragMoved = true;
            var newLeft = clamp(origLeft + dx, 0, window.innerWidth  - widget.offsetWidth);
            var newTop  = clamp(origTop  + dy, 0, window.innerHeight - widget.offsetHeight);
            widget.style.left = newLeft + 'px';
            widget.style.top  = newTop  + 'px';
        }

        function onEnd() {
            if (!dragging) return;
            dragging = false;
            trigger.style.cursor = 'grab';
            snapToNearestSide();
        }

        // Mouse
        trigger.addEventListener('mousedown', function (e) {
            e.preventDefault();
            onStart(e.clientX, e.clientY);
        });
        document.addEventListener('mousemove', function (e) { onMove(e.clientX, e.clientY); });
        document.addEventListener('mouseup', function (e) {
            if (dragging && !dragMoved) {
                // Là click bình thường → toggle panel
                window.aiBotToggle();
            }
            onEnd();
        });

        // Touch
        trigger.addEventListener('touchstart', function (e) {
            var t = e.touches[0];
            onStart(t.clientX, t.clientY);
        }, { passive: true });
        document.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            var t = e.touches[0];
            onMove(t.clientX, t.clientY);
        }, { passive: true });
        document.addEventListener('touchend', function () {
            if (dragging && !dragMoved) window.aiBotToggle();
            onEnd();
        });

        window.addEventListener('resize', function () {
            if (!widget.style.top || widget.style.top === '' || widget.style.top === 'auto') {
                initPos();
            }
            snapToSide(currentSide);
        });

        window.addEventListener('scroll', function () {
            avoidOverlapWithBackToTop();
        }, { passive: true });

        // Xóa onclick gốc để không bị gọi 2 lần sau khi drag
        trigger.removeAttribute('onclick');

        initPos();
        snapToSide('right');
    }());

    // Đồng bộ DB.json về localStorage khi widget được tải.
    syncAiSnapshotToLocalStore();
})();
</script>
