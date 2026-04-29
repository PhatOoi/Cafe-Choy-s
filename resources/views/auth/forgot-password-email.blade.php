<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="login-form">
                <h2>Quên mật khẩu</h2>
                <form action="{{ route('forgot-password.send-code') }}" method="POST">
                    @csrf
                    <div class="input-box">
                        <label for="email">Email</label>
                        <input type="email" name="email" placeholder="Nhập email của bạn" required>
                    </div>
                    <button class="btn" type="submit">Gửi mã xác thực</button>
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
                <h1>RESET<br>PASSWORD</h1>
                <p>Nhập email để nhận mã xác thực đổi mật khẩu.</p>
            </div>
        </div>
    </div>
</body>
</html>
