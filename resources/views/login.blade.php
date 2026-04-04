<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
   <link rel="stylesheet" href="css/login.css">
    <style>
        
    </style>
</head>

<body>

    <!-- BẮT BUỘC PHẢI CÓ -->
    <input type="checkbox" id="toggle">

    <div class="container">
        <div class="left">

            <!-- LOGIN -->
            <div class="login-form">
                <h2>Login</h2>

                <div class="input-box">
                    <label>Email</label>
                    <input type="email" placeholder="Enter your email">
                </div>

                <div class="input-box">
                    <label>Password</label>
                    <input type="password" placeholder="Enter your Password">
                </div>
                <p style="margin-bottom: 10px;"><a href="/forgot-password" style="color:#3498db;">Quên mật khẩu?</a></p>

                <button class="btn">Login</button>

                <p class="toggle-text">
                    Don't have an account?
                    <label for="toggle">Signup</label>
                </p>
            </div>

            <!-- REGISTER -->
            <div class="register-form">
                <h1>Register</h1>

                <div class="input-box">
                    <label>Username</label>
                    <input type="text" placeholder="Enter your username">
                </div>

                <div class="input-box">
                    <label>Email</label>
                    <input type="email" placeholder="Enter your email">
                </div>

                <div class="input-box">
                    <label>Password</label>
                    <input type="password" placeholder="Enter your Password">
                </div>

                <button class="btn">Register</button>

                <p class="toggle-text">
                    Already have an account?
                    <label for="toggle">Login</label>
                </p>
            </div>

        </div>

        <div class="right">
            <div class="right-container">
                <h1>WELCOME<br>BACK!</h1>
                <p>Chào mừng bạn đến với Coffee Choy's ☕</p>
            </div>
        </div>
    </div>

</body>

</html>