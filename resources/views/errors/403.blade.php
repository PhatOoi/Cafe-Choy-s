<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .container { text-align: center; padding: 2rem; }
        .code {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }
        .icon { font-size: 4rem; margin: 1rem 0; }
        h1 { font-size: 1.8rem; margin-bottom: 0.5rem; }
        p { color: rgba(255,255,255,0.7); margin-bottom: 2rem; font-size: 1rem; }
        .message {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin: 0.25rem;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">403</div>
        <div class="icon">🚫</div>
        <h1>Không có quyền truy cập</h1>
        <p>Bạn không được phép vào trang này.</p>

        @if(!empty($message))
        <div class="message">{{ $message }}</div>
        @endif

        <div>
            <a href="javascript:history.back()" class="btn btn-secondary">← Quay lại</a>
            <a href="/" class="btn btn-primary">🏠 Trang chủ</a>
        </div>
    </div>
</body>
</html>
