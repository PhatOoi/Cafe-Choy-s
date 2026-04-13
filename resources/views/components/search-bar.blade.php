<form class="search-bar" action="{{ url('/search') }}" method="GET" role="search" autocomplete="off">
    <div class="search-bar-inner">
        <input type="text" name="q" class="search-bar-input" placeholder="Tìm kiếm đồ uống..." aria-label="Tìm kiếm" id="voice-search-input">
        <button type="button" class="search-bar-mic" id="voice-search-btn" tabindex="-1" type="button">
            <i class="fa fa-microphone"></i>
        </button>
    </div>
</form>
<script>
    // Voice search logic
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('voice-search-btn');
        const input = document.getElementById('voice-search-input');
        let recognition;
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.lang = 'vi-VN';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                recognition.start();
                btn.classList.add('listening');
            });
            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                input.value = transcript;
                btn.classList.remove('listening');
                input.focus();
            };
            recognition.onerror = function() {
                btn.classList.remove('listening');
            };
            recognition.onend = function() {
                btn.classList.remove('listening');
            };
        } else {
            btn.style.display = 'none';
        }
    });
</script>