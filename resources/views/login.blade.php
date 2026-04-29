<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
   <link rel="stylesheet" href="css/login.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ececec;
            height: 100vh;
            padding: 20px;
            background: url('images/backgrlogin.jpg') no-repeat center center/cover;
        }
    </style>
</head>

<body>

    <!-- BẮT BUỘC PHẢI CÓ -->
    <input type="checkbox" id="toggle" {{ ($errors->any() && old('username')) ? 'checked' : '' }}>
    <div class="container">
        <div class="left">

            <!-- LOGIN -->
            <form class="login-form" method="POST" action="/login" autocomplete="off">
                @csrf
                <h2>Đăng nhập</h2>
                <div class="input-box">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Nhập email của bạn" required autocomplete="off">
                </div>
                <div class="input-box">
                    <label>Mật khẩu</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="login-password" placeholder="Nhập mật khẩu của bạn" required autocomplete="new-password" style="width:100%; padding-right:36px;">
                        <span id="toggle-login-password" style="position:absolute; right:16px; top:0; height:100%; display:flex; align-items:center; cursor:pointer;">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </span>
                    </div>
                </div>
                <div class="input-box">
                    <label style="display:flex; align-items:center; gap:6px; font-weight:normal;">
                        <input type="checkbox" name="remember" style="width:16px; height:16px; margin-right:6px;"> Ghi nhớ đăng nhập
                    </label>
                </div>
                <p style="margin-bottom: 10px;"><a href="/forgot-password" style="color:#3498db;">Quên mật khẩu?</a></p>
                <button class="btn" type="submit">Đăng nhập</button>
                @if (session('error'))
                    <div class="auth-alert auth-alert-error">{{ session('error') }}</div>
                @endif
                <p class="toggle-text">
                    Bạn chưa có tài khoản?
                    <label for="toggle">Tạo tài khoản mới</label>
                </p>
            </form>

            <!-- REGISTER -->
            <form class="register-form" method="POST" action="/register" autocomplete="off">
                @csrf
                <h1>Tạo tài khoản</h1>
                @if ($errors->has('register_error'))
                    <div class="auth-alert auth-alert-error">{{ $errors->first('register_error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="auth-error-list">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <div class="input-box">
                    <label>Họ và tên</label>
                    <input type="text" name="username" placeholder="Nhập họ và tên của bạn" required autocomplete="off" value="{{ old('username') }}">
                </div>
                <div class="input-box">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Nhập email của bạn" required autocomplete="off" value="{{ old('email') }}">
                </div>
                <div class="input-box">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" placeholder="Nhập số điện thoại của bạn" required autocomplete="off" value="{{ old('phone') }}">
                </div>
                <div class="input-box">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" id="register-password" placeholder="Tạo mật khẩu cho tài khoản" required autocomplete="new-password" oninput="checkPasswordStrength()">
                    <div id="password-strength-bar" style="height:6px; background:#eee; margin-top:4px; border-radius:3px; overflow:hidden;">
                        <div id="password-strength" style="height:100%; width:0; background:red; transition:width 0.3s;"></div>
                    </div>
                    <div id="password-strength-text" style="font-size:12px; color:#888; margin-top:2px;"></div>
                </div>
                <div class="input-box">
                    <label>Nhập lại mật khẩu</label>
                    <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required autocomplete="new-password">
                </div>
                <button class="btn" type="submit">Tạo tài khoản</button>
                <button class="btn" type="button" style="margin-top:10px; background:#222; color:#fff;" onclick="document.getElementById('toggle').checked=false;">Quay lại trang đăng nhập</button>
                @if (session('register_success'))
                    <div id="register-success-message" class="auth-alert auth-alert-success">{{ session('register_success') }}</div>
                    <script>
                        // Nếu đang ở form đăng ký, tự động chuyển sang form đăng nhập và reload để hiển thị thông báo
                        if (document.getElementById('toggle') && document.getElementById('toggle').checked) {
                            document.getElementById('toggle').checked = false;
                        }
                    </script>
                @endif
            </form>

        </div>

        <div class="right">
            <div class="right-container">
                <h2>CHÀO MỪNG ĐẾN VỚI CHOY'S CAFE!</h2>
                <p>Hãy đăng nhập hoặc tạo tài khoản để trải nghiệm dịch vụ tuyệt vời tại Choy's Cafe ☕</p>
            </div>
        </div>
    </div>

</body>

<script>
    const toggleLoginPassword = document.getElementById('toggle-login-password');
    if (toggleLoginPassword) {
        toggleLoginPassword.addEventListener('click', function() {
            const pwdInput = document.getElementById('login-password');
            const eyeIcon = document.getElementById('eye-icon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.292m3.087-2.938A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.293 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />';
            } else {
                pwdInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        });
    }
</script>
</html>