<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="login-form">
                <h2>Đặt lại mật khẩu</h2>
                <form action="{{ route('forgot-password.reset-password') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div class="input-box">
                        <label for="password">Mật khẩu mới</label>
                        <input type="password" name="password" placeholder="Nhập mật khẩu mới" required>
                    </div>
                    <div class="input-box">
                        <label for="password_confirmation">Nhập lại mật khẩu</label>
                        <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                    </div>
                    <button class="btn" type="submit">Đổi mật khẩu</button>
                </form>
                @if ($errors->any())
                    <div class="auth-error-list">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <p class="toggle-text" style="margin-top:20px;">
                    <a href="/login" style="color:#3498db;">Quay lại đăng nhập</a>
                </p>
            </div>
        </div>
        <div class="right">
            <div class="right-container">
                <h1>NEW<br>PASSWORD</h1>
                <p>Nhập mật khẩu mới cho tài khoản của bạn.</p>
            </div>
        </div>
    </div>
</body>
</html>
