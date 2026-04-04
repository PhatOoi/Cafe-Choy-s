<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực mã</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="login-form">
                <h2>Xác thực mã</h2>
                <form action="{{ route('forgot-password.verify-code') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div class="input-box">
                        <label for="code">Mã xác thực</label>
                        <input type="text" name="code" maxlength="6" placeholder="Nhập mã 6 số" required>
                    </div>
                    <button class="btn" type="submit">Xác nhận</button>
                </form>
                @if ($errors->any())
                    <div style="color:red; margin-top:10px;">
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
                <h1>VERIFY<br>CODE</h1>
                <p>Nhập mã xác thực đã gửi về email của bạn.</p>
            </div>
        </div>
    </div>
</body>
</html>
