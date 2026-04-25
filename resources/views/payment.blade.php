<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thanh toán — Choy's Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f8f5f0; min-height: 100vh; padding: 40px 20px; }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h1 { font-size: 2rem; font-weight: 700; color: #1a110d; }
        .page-header p  { color: #8b7355; font-size: 14px; margin-top: 6px; }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 28px;
            max-width: 1000px;
            margin: 0 auto;
        }
        @media(max-width:900px){ .payment-grid{ grid-template-columns:1fr; } }

        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 20px rgba(0,0,0,.06);
            padding: 28px;
            border: none;
        }
        .card-title { font-size: 16px; font-weight: 700; color: #1a110d; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
        .card-title i { color: #c8773a; }

        /* Form */
        .form-group { margin-bottom: 16px; }
        label { font-size: 13px; font-weight: 600; color: #3d2b1f; margin-bottom: 6px; display:block; }
        input, textarea, select {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e8ddd2; border-radius: 10px;
            font-size: 13.5px; font-family: 'Poppins', sans-serif;
            color: #1a110d; background: #fdfaf7;
            outline: none; transition: border-color .16s;
        }
        input:focus, textarea:focus, select:focus { border-color: #c8773a; background: #fff; }
        textarea { resize: vertical; min-height: 80px; }

        /* Payment methods */
        .pay-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .pay-method {
            border: 2px solid #e8ddd2; border-radius: 14px;
            padding: 16px; text-align: center; cursor: pointer;
            transition: all .2s;
        }
        .pay-method:hover { border-color: #c8b8a8; background: #fdfaf7; }
        .pay-method.active { border-color: #c8773a; background: #fffaf6; }
        .pay-method i { font-size: 26px; display: block; margin-bottom: 8px; }
        .pay-method span { font-size: 13px; font-weight: 600; color: #1a110d; }
        .pay-method small { display: block; font-size: 11px; color: #8b7355; margin-top: 3px; }

        /* QR section */
        #qr-section {
            display: none; margin-top: 18px;
            background: #f8f5f0; border-radius: 14px;
            padding: 20px; text-align: center;
        }
        #qr-section img { width: 180px; border: 3px solid #e8ddd2; border-radius: 12px; padding: 8px; background: #fff; }
        .bank-info { text-align: left; margin-top: 16px; }
        .bank-info div { font-size: 13px; margin-bottom: 8px; color: #3d2b1f; }
        .bank-info b { color: #1a110d; }
        .copy-btn { border: none; background: transparent; color: #c8773a; cursor: pointer; font-size: 12px; padding: 2px 6px; }
        .copy-btn:hover { text-decoration: underline; }

        /* Order summary */
        .order-items { list-style: none; margin-bottom: 18px; }
        .order-items li {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid #f0ebe4; font-size: 13.5px;
        }
        .order-items li:last-child { border-bottom: none; }
        .item-name { color: #3d2b1f; }
        .item-price { font-weight: 600; color: #1a110d; }
        .order-total { display:flex; justify-content:space-between; font-size:17px; font-weight:700; color:#1a110d; border-top:2px solid #f0ebe4; padding-top:14px; margin-top:4px; }
        .order-total span:last-child { color: #c8773a; }

        /* Submit button */
        .btn-pay {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #c8773a, #a85f28);
            color: #fff; border: none; border-radius: 12px;
            font-size: 16px; font-weight: 600; cursor: pointer;
            font-family: 'Poppins', sans-serif; transition: all .2s;
            margin-top: 10px;
        }
        .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(200,119,58,.35); }
        .btn-back { display: block; text-align:center; margin-top:12px; color:#8b7355; font-size:13px; text-decoration:none; }
        .btn-back:hover { color: #c8773a; }

        /* Success state */
        .success-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:999; align-items:center; justify-content:center; }
        .success-card { background:#fff; border-radius:20px; padding:40px; text-align:center; max-width:380px; margin:20px; }
        .success-card i { font-size:56px; color:#27ae60; margin-bottom:16px; }
        .success-card h3 { font-size:20px; font-weight:700; color:#1a110d; margin-bottom:8px; }
        .success-card p { color:#8b7355; font-size:14px; }
    </style>
</head>
<body>

<div class="page-header">
    <h1><i class="fas fa-lock" style="color:#c8773a;margin-right:10px;"></i>Thanh toán đơn hàng</h1>
    <p>Hoàn tất thông tin để nhận đơn nhanh nhất</p>
</div>

<div class="payment-grid">

    <!-- LEFT: Form -->
    <div>
        <!-- Thông tin giao hàng -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-title"><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</div>
            <div class="form-group">
                <label>Họ và tên *</label>
                <input type="text" id="name" placeholder="Nguyễn Văn A"
                       value="{{ Auth::user()->name ?? '' }}">
            </div>
            <div class="form-group">
                <label>Số điện thoại *</label>
                <input type="tel" id="phone" placeholder="0901234567"
                       value="{{ Auth::user()->phone ?? '' }}">
            </div>
            <div class="form-group">
                <label>Địa chỉ giao hàng *</label>
                <input type="text" id="address" placeholder="Số nhà, tên đường, phường/xã...">
            </div>
            <div class="form-group">
                <label>Ghi chú thêm</label>
                <textarea id="note" placeholder="Ít đá, giao trước 10h..."></textarea>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="card">
            <div class="card-title"><i class="fas fa-credit-card"></i> Phương thức thanh toán</div>
            <div class="pay-methods">
                <div class="pay-method active" onclick="selectMethod('cash')" id="m-cash">
                    <i class="fas fa-money-bill-wave" style="color:#27ae60;"></i>
                    <span>Tiền mặt</span>
                    <small>Thanh toán khi nhận (COD)</small>
                </div>
                <div class="pay-method" onclick="selectMethod('qr')" id="m-qr">
                    <i class="fas fa-qrcode" style="color:#2563eb;"></i>
                    <span>QR / Chuyển khoản</span>
                    <small>VietQR — nhanh & tiện</small>
                </div>
                <div class="pay-method" onclick="selectMethod('momo')" id="m-momo">
                    <i class="fas fa-mobile-alt" style="color:#ae2070;"></i>
                    <span>MoMo</span>
                    <small>Ví điện tử MoMo</small>
                </div>
                <div class="pay-method" onclick="selectMethod('zalopay')" id="m-zalopay">
                    <i class="fas fa-wallet" style="color:#0068ff;"></i>
                    <span>ZaloPay</span>
                    <small>Ví ZaloPay / ATM</small>
                </div>
            </div>

            <!-- QR hiển thị khi chọn QR -->
            <div id="qr-section">
                <img src="https://img.vietqr.io/image/vietcombank-1042131375-print.png?amount=135000&addInfo=CHOYSCAFE1234&accountName=TRAN%20QUOC%20LONG"
                     alt="QR thanh toán" id="qr-img">
                <div class="bank-info">
                    <div><b>Ngân hàng:</b> Vietcombank</div>
                    <div><b>Số tài khoản:</b> 1042131375
                        <button class="copy-btn" onclick="copy('1042131375')">📋 Sao chép</button>
                    </div>
                    <div><b>Chủ tài khoản:</b> TRAN QUOC LONG</div>
                    <div><b>Số tiền:</b> <span id="qr-amount">—</span>
                        <button class="copy-btn" onclick="copyAmount()">📋 Sao chép</button>
                    </div>
                    <div><b>Nội dung CK:</b> <span id="qr-note">CHOYSCAFE{{ rand(1000,9999) }}</span>
                        <button class="copy-btn" onclick="copy(document.getElementById('qr-note').textContent)">📋 Sao chép</button>
                    </div>
                </div>
            </div>

            <button class="btn-pay" onclick="placeOrder()">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i>Đặt hàng ngay
            </button>
            <a href="/cart" class="btn-back"><i class="fas fa-arrow-left" style="margin-right:6px;"></i>Quay lại giỏ hàng</a>
        </div>
    </div>

    <!-- RIGHT: Order summary -->
    <div>
        <div class="card" style="position:sticky;top:20px;">
            <div class="card-title"><i class="fas fa-receipt"></i> Đơn hàng của bạn</div>
            <ul class="order-items">
                <li>
                    <span class="item-name">☕ Cà phê đen đá × 2</span>
                    <span class="item-price">50.000đ</span>
                </li>
                <li>
                    <span class="item-name">🧋 Trà sữa trân châu × 1</span>
                    <span class="item-price">45.000đ</span>
                </li>
                <li>
                    <span class="item-name">🧁 Croissant × 1</span>
                    <span class="item-price">35.000đ</span>
                </li>
            </ul>

            <div style="font-size:13px;color:#5c3d2e;margin-bottom:8px;display:flex;justify-content:space-between;">
                <span>Tạm tính</span><span>130.000đ</span>
            </div>
            <div style="font-size:13px;color:#5c3d2e;margin-bottom:8px;display:flex;justify-content:space-between;">
                <span>Phí giao hàng</span><span style="color:#27ae60;">Miễn phí</span>
            </div>
            <div class="order-total">
                <span>Tổng thanh toán</span>
                <span>130.000đ</span>
            </div>

            <!-- Security badges -->
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f0ebe4;display:flex;gap:16px;justify-content:center;">
                <div style="text-align:center;font-size:11px;color:#8b7355;">
                    <i class="fas fa-shield-alt" style="font-size:22px;color:#27ae60;display:block;margin-bottom:4px;"></i>
                    Bảo mật SSL
                </div>
                <div style="text-align:center;font-size:11px;color:#8b7355;">
                    <i class="fas fa-lock" style="font-size:22px;color:#2563eb;display:block;margin-bottom:4px;"></i>
                    Thanh toán an toàn
                </div>
                <div style="text-align:center;font-size:11px;color:#8b7355;">
                    <i class="fas fa-undo" style="font-size:22px;color:#c8773a;display:block;margin-bottom:4px;"></i>
                    Đổi trả dễ dàng
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success overlay -->
<div class="success-overlay" id="successOverlay" style="display:flex;display:none;">
    <div class="success-card">
        <i class="fas fa-check-circle"></i>
        <h3>Đặt hàng thành công!</h3>
        <p style="margin-bottom:20px;">Cảm ơn bạn đã tin tưởng Choy's Cafe. Chúng tôi sẽ liên hệ xác nhận đơn sớm nhất!</p>
        <a href="/" style="display:inline-block;background:#c8773a;color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:600;">Về trang chủ</a>
    </div>
</div>

<script>
let method = 'cash';

function selectMethod(m) {
    method = m;
    document.querySelectorAll('.pay-method').forEach(el => {
        el.classList.remove('active');
        el.style.border = '2px solid #e8ddd2';
        el.style.background = '#fff';
    });
    const el = document.getElementById('m-' + m);
    el.classList.add('active');
    el.style.border = '2px solid #c8773a';
    el.style.background = '#fffaf6';
    document.getElementById('qr-section').style.display = m === 'qr' ? 'block' : 'none';
}

function copy(text) {
    navigator.clipboard.writeText(text).then(() => {
        const t = document.createElement('div');
        t.textContent = '✅ Đã sao chép!';
        t.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#1a110d;color:#fff;padding:10px 18px;border-radius:10px;font-size:13px;z-index:9999;font-family:Poppins,sans-serif;';
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2000);
    });
}
function copyAmount() { copy(document.getElementById('qr-amount').textContent); }

function placeOrder() {
    const name    = document.getElementById('name').value.trim();
    const phone   = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();

    if (!name || !phone || !address) {
        alert('⚠️ Vui lòng điền đầy đủ thông tin giao hàng!');
        return;
    }
    if (!/^\d{8,11}$/.test(phone.replace(/\s/g,''))) {
        alert('⚠️ Số điện thoại không hợp lệ!');
        return;
    }

    document.getElementById('successOverlay').style.display = 'flex';
}
</script>
@include('components.ai-bot-widget')
</body>
</html>
