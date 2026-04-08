<form class="search-bar" action="{{ url('/search') }}" method="GET" role="search">
    <div class="search-bar-inner">
        <!-- Bỏ icon/logo bên trái -->
        <input type="text" name="q" class="search-bar-input" placeholder="Tìm kiếm đồ uống..." aria-label="Tìm kiếm">
        <button type="submit" class="search-bar-mic" tabindex="-1">
            <i class="fa fa-microphone"></i>
        </button>
    </div>
</form>
