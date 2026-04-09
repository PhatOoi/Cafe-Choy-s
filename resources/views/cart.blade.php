<!DOCTYPE html>
<html lang="en">

<head>
    <title>Giỏ Hàng - Choy's Cafe</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Great+Vibes" rel="stylesheet">

	<link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
	<link rel="stylesheet" href="css/animate.css">
	<link rel="stylesheet" href="css/owl.carousel.min.css">
	<link rel="stylesheet" href="css/owl.theme.default.min.css">
	<link rel="stylesheet" href="css/magnific-popup.css">
	<link rel="stylesheet" href="css/aos.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/jquery.timepicker.css">
	<link rel="stylesheet" href="css/flaticon.css">
	<link rel="stylesheet" href="css/icomoon.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
		<div class="container">
            <a class="navbar-brand mr-3" href="/">
                <img src="/images/logo.png" alt="Choy's Cafe" style="height:72px;width:auto;max-width:none;object-fit:contain;display:block;padding:0;margin:0;background:transparent;">
            </a>
            @include('components.search-bar')
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="oi oi-menu"></span> Menu
			</button>
			<div class="collapse navbar-collapse" id="ftco-nav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item"><a href="/" class="nav-link">Trang Chủ</a></li>
					<li class="nav-item"><a href="/menu" class="nav-link">Menu</a></li>

					<!-- SPACER -->
					<li class="nav-item flex-spacer"></li>

					<!-- CART -->
					<li class="nav-item cart"><a href="/cart" class="nav-link"><span
								class="icon icon-shopping_cart"></span><span
								class="bag d-flex justify-content-center align-items-center"><small id="cart-count">{{ $cartCount ?? 0 }}</small></span></a>
					</li>

					<!-- USER DROPDOWN -->
					@if(Auth::check())
					<li class="nav-item user-dropdown-wrapper">
						<div class="user-dropdown-container">
							<!-- USER AVATAR BUTTON -->
							<button class="user-avatar-btn" type="button" id="userMenuBtn">
								@if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
									<img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
										 alt="{{ Auth::user()->name }}"
										 class="user-avatar">
								@else
									<img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ff6b00&color=fff&size=48"
										 alt="{{ Auth::user()->name }}"
										 class="user-avatar">
								@endif
							</button>

							<!-- USER DROPDOWN MENU -->
							<div class="user-dropdown-menu" id="userDropdownMenu">
								<!-- Header Info -->
								<div class="dropdown-header-info">
									@if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
										<img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
											 alt="{{ Auth::user()->name }}"
											 class="dropdown-avatar">
									@else
										<img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ff6b00&color=fff&size=60"
											 alt="{{ Auth::user()->name }}"
											 class="dropdown-avatar">
									@endif
									<div class="user-details">
										<p class="user-name">{{ Auth::user()->name }}</p>
										<p class="user-role">
											@if(Auth::user()->role === 'admin')
												<span class="badge-admin">Admin</span>
											@else
												<span class="badge-customer">Khách hàng</span>
											@endif
										</p>
									</div>
								</div>

								<!-- Divider -->
								<div class="dropdown-divider"></div>

								<!-- Menu Items -->
								<a href="/profile" class="dropdown-link">
									<i class="fas fa-user"></i>
									<span>Hồ sơ cá nhân</span>
								</a>


								@if(Auth::user()->role === 'admin')
								<a href="/admin" class="dropdown-link">
									<i class="fas fa-cog"></i>
									<span>Quản trị</span>
								</a>
								@endif

								<!-- Divider -->
								<div class="dropdown-divider"></div>

								<!-- Logout -->
								<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display:none;">
									@csrf
								</form>
								<a href="#" class="dropdown-link logout-link" 
								   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
									<i class="fas fa-sign-out-alt"></i>
									<span>Đăng xuất</span>
								</a>
							</div>
						</div>
					</li>
					@else
					<li class="nav-item">
						<a href="{{ url('/login') }}" class="nav-link btn-login">
							<i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập
						</a>
					</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>

	<section class="ftco-section" style="padding: 100px 0;">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-8">
					<h1 class="text-center mb-5">Đơn hàng của bạn</h1>
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Sản Phẩm</th>
									<th>Giá</th>
									<th>Số Lượng</th>
									<th>Tổng Cộng</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@if(count($cart) > 0)
									@foreach($cart as $key => $item)
										@php
											$itemTotal = $item['price'] * $item['qty'];
										@endphp
										<tr>
											<td>
												<strong>{{ $item['name'] }}</strong>
												<br>
												<small class="text-muted">
													Kích cỡ: {{ $item['size'] }}<br>
													Đường: {{ $item['sugar'] }}<br>
													Đá: {{ $item['ice'] }}
													@if(!empty($item['toppings']) && count($item['toppings']) > 0)
														<br>Topping: {{ implode(', ', $item['toppings']) }}
													@endif
													@if(!empty($item['note']))
														<br>Ghi chú: {{ $item['note'] }}
													@endif
												</small>
											</td>
											<td>{{ number_format($item['price']) }} đ</td>
											<td>{{ $item['qty'] }}</td>
											<td>{{ number_format($itemTotal) }} đ</td>
											<td>
												<a href="/cart/remove/{{ $key }}" class="btn btn-sm btn-danger">Xóa</a>
											</td>
										</tr>
									@endforeach
								@else
									<tr>
										<td colspan="5" class="text-center">Giỏ hàng của bạn đang trống</td>
									</tr>
								@endif
							</tbody>
						</table>
					</div>
					
					@if(count($cart) > 0)
						@php
											$total = 0;
											foreach($cart as $item) {
												$total += $item['price'] * $item['qty'];
											}
										@endphp
						<div class="row mt-5">
							<div class="col-md-6 offset-md-6">
								<div class="card">
									<div class="card-body">
                                           <h5 class="card-title">Tổng Cộng</h5>
                                           <h3 class="text-danger">{{ number_format($total) }} đ</h3>
                                           <button class="btn btn-primary btn-block mt-3" type="button" data-toggle="modal" data-target="#paymentMethodModal">Thanh Toán</button>
                                    </div>
                                    <!-- Modal chọn hình thức thanh toán -->
                                    <div class="modal fade" id="paymentMethodModal" tabindex="-1" role="dialog" aria-labelledby="paymentMethodModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header border-0" style="display:block;">
                                                    <div class="w-100 text-center font-weight-bold" style="font-size:18px; margin-bottom:12px;">Vui lòng lựa chọn hình thức thanh toán</div>
                                                    <button type="button" class="close position-absolute" style="right:16px;top:16px;" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body d-flex flex-column align-items-center gap-3" style="gap: 16px;">
                                                    <button class="btn btn-primary w-100 mb-2" type="button" onclick="handleCashPayment()">Thanh toán bằng tiền mặt</button>
                                                    <button class="btn btn-primary w-100" type="button" onclick="showQRModal()">Thanh toán QR code</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal QR Code Payment -->
                                    <div class="modal fade" id="qrPaymentModal" tabindex="-1" role="dialog" aria-labelledby="qrPaymentModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title w-100 text-center font-weight-bold" id="qrPaymentModalLabel">Thanh toán qua chuyển khoản ngân hàng</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="background:#fafbfc;">
                                                    <div class="row">
                                                        <div class="col-md-6 text-center border-right">
                                                            <div class="font-weight-bold mb-2" style="font-size:16px;">Cách 1: Mở app ngân hàng/ Ví và <b>quét mã QR</b></div>
                                                            @php
                                                                $qrBank = 'Vietcombank';
                                                                $qrName = 'TRAN QUOC LONG';
                                                                $qrAccount = '1042131375';
                                                                $qrAmount = $total ?? 0;
                                                                $qrNote = 'DH' . (isset($cart) ? rand(1000,9999) : 'XXXX');
                                                                $qrApi = 'https://img.vietqr.io/image/' . 'vietcombank' . '-' . $qrAccount . '-print.png?amount=' . $qrAmount . '&addInfo=' . $qrNote . '&accountName=' . urlencode($qrName);
                                                            @endphp
                                                            <img src="{{ $qrApi }}" alt="QR code" style="width:220px;max-width:100%;border:2px solid #eee;padding:8px;background:#fff;">
                                                            <div class="mt-2">
                                                                <a class="btn btn-primary btn-sm" href="{{ $qrApi }}" download="qr-vietcombank.png">Tải ảnh QR</a>
                                                            </div>
                                                            <div class="mt-2" style="font-size:13px;color:#888;">Trạng thái: <span>Chờ thanh toán...</span></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="font-weight-bold mb-2" style="font-size:16px;">Cách 2: Chuyển khoản <b>thủ công</b> theo thông tin</div>
                                                            <div class="card p-3" style="background:#fff;border:1px solid #e0e0e0;">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <span style="font-weight:bold;font-size:18px;">Vietcombank</span>
                                                                    </div>
                                                                    <div style="font-size:15px;">
                                                                        <div><b>Ngân hàng:</b> Vietcombank</div>
                                                                        <div><b>Thụ hưởng:</b> TRAN QUOC LONG</div>
                                                                        <div><b>Số tài khoản:</b> 1042131375 <button class="btn btn-link btn-sm py-0 px-1" onclick="copyToClipboard('1042131375')">📋</button></div>
                                                                        <div><b>Số tiền:</b> <span id="qr-amount">{{ number_format($qrAmount) }} đ</span> <button class="btn btn-link btn-sm py-0 px-1" onclick="copyToClipboard('{{ $qrAmount }}')">📋</button></div>
                                                                        <div><b>Nội dung CK:</b> <span id="qr-note">{{ $qrNote }}</span> <button class="btn btn-link btn-sm py-0 px-1" onclick="copyToClipboard('{{ $qrNote }}')">📋</button></div>
                                                                    </div>
                                                                    <div class="alert alert-warning mt-2 mb-0 p-2" style="font-size:13px;">
                                                                        <b>Lưu ý:</b> Vui lòng giữ nguyên nội dung chuyển khoản <b>{{ $qrNote }}</b> để xác nhận thanh toán tự động.
                                                                    </div>
                                                                    <div class="text-center mt-3 mb-2">
                                                                        <button class="btn btn-success" onclick="confirmPayment()">Xác nhận thanh toán</button>
                                                                    </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal Bill Thanh Toán Tiền Mặt -->
                                    <div class="modal fade" id="billModal" tabindex="-1" role="dialog" aria-labelledby="billModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document" style="max-width: 370px;">
                                            <div class="modal-content" style="font-family: Arial, sans-serif;">
                                                <div class="modal-body p-3" style="color:#111;">
                                                    <div class="text-center" style="margin-bottom:4px;">
                                                        <img src="/images/logo.png" alt="Choy's Cafe Logo" style="height:90px; object-fit:contain;" />
                                                    </div>
                                                    <div class="text-center" style="font-size:13px;color:#111;">toà JOVE, Trung Mỹ Tây, Quận 12<br>0904xxxxxx</div>
                                                    <div class="text-center mt-2 mb-2" style="font-size:16px;font-weight:bold; color:#111;">HÓA ĐƠN <span id="bill-code"></span></div>
                                                    <div class="d-flex justify-content-between mb-1" style="font-size:13px;color:#111;">
                                                        <span style="color:#111;">Thời gian</span>
                                                        <span style="color:#111;"><span id="bill-time"></span> <span id="bill-date"></span></span>
                                                    </div>
                                                    <div style="display:flex; font-size:13px; font-weight:bold; margin-bottom:2px; margin-top:6px; text-align:left;color:#111;">
                                                        <div style="width:160px; padding:0 2px 0 0;">Tên sản phẩm</div>
                                                        <div style="width:60px; text-align:left; padding:0 0 0 2px;">Giá tiền</div>
                                                        <div style="width:40px; text-align:left; padding:0 0 0 2px;">SL</div>
                                                        <div style="width:70px; text-align:left; padding:0 0 0 2px;">Tổng cộng</div>
                                                    </div>
                                                    <div style="display:flex;color:#111;">
                                                        <div style="width:160px;">
                                                            @foreach($cart as $item)
                                                                <div style="margin-bottom:1px; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                                    <div style="font-size:12px;">{{ $item['name'] }}</div>
                                                                    <div style="font-size:10px; color:#888; font-style:italic; margin-top:-2px; line-height:1.4;">
                                                                        <div>- Size: {{ $item['size'] ?? '-' }}</div>
                                                                        <div>- Đường: {{ $item['sugar'] ?? '-' }}</div>
                                                                        <div>- Đá: {{ $item['ice'] ?? '-' }}</div>
                                                                        @if(!empty($item['toppings']) && count($item['toppings']) > 0)
                                                                            <div>- Topping: {{ implode(', ', $item['toppings']) }}</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div style="width:60px; text-align:left; padding-left:0;">
                                                            @foreach($cart as $item)
                                                                <div style="font-size:12px; margin-bottom:1px; text-align:left;">{{ number_format($item['price']) }}</div>
                                                            @endforeach
                                                        </div>
                                                        <div style="width:40px; text-align:left; padding-left:0;">
                                                            @foreach($cart as $item)
                                                                <div style="font-size:12px; margin-bottom:1px; text-align:left;">{{ $item['qty'] }}</div>
                                                            @endforeach
                                                        </div>
                                                        <div style="width:70px; text-align:left; padding-left:0;">
                                                            @foreach($cart as $item)
                                                                <div style="font-size:12px; margin-bottom:1px; text-align:left;">{{ number_format($item['price'] * $item['qty']) }}</div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <!-- Đã xóa phần bảng tên sản phẩm phía dưới theo yêu cầu -->
                                                    <div class="d-flex justify-content-between" style="font-size:13px;color:#111;">
                                                        <span style="color:#111;">Tổng dịch vụ</span>
                                                        <span style="color:#111;">{{ number_format($total) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1 mb-1" style="font-size:16px;font-weight:bold;color:#111;">
                                                        <span style="color:#111;">Thanh toán</span>
                                                        <span style="font-size:20px;color:#111;">{{ number_format($total) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between" style="font-size:13px;color:#111;">
                                                        <span style="color:#111;">Mã hóa đơn</span>
                                                        <span id="bill-code-2" style="color:#111;"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between" style="font-size:13px;color:#111;">
                                                        <span style="color:#111;">Tên khách hàng</span>
                                                        <span style="color:#111;">{{ Auth::user()->name ?? '-' }}</span>
                                                    </div>
                                                    <div class="text-center mt-2" style="font-size:13px;color:#111;">
                                                        Quý khách vui lòng kiểm tra lại hóa đơn trước khi thanh toán<br>
                                                        Xin cảm ơn quý khách.<br>
                                                        Hẹn gặp lại quý khách lần sau
                                                    </div>
                                                    <div class="text-center mt-2" style="color:#111;">
                                                        <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Đóng</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Đặt toàn bộ JS ở cuối file, sau tất cả HTML -->
                                    <script>
                                    function confirmPayment() {
                                        $('#qrPaymentModal').modal('hide');
                                        setTimeout(function() {
                                            alert('Cảm ơn bạn! Đơn hàng sẽ được xác nhận sau khi thanh toán thành công.');
                                        }, 400);
                                    }
                                    function handleCashPayment() {
                                        $('#paymentMethodModal').modal('hide');
                                        setTimeout(function() { $('#billModal').modal('show'); }, 400);
                                    }
                                    function showQRModal() {
                                        $('#paymentMethodModal').modal('hide');
                                        setTimeout(function() { $('#qrPaymentModal').modal('show'); }, 400);
                                    }
                                    function copyToClipboard(text) {
                                        var temp = document.createElement('input');
                                        document.body.appendChild(temp);
                                        temp.value = text;
                                        temp.select();
                                        document.execCommand('copy');
                                        document.body.removeChild(temp);
                                        alert('Đã sao chép: ' + text);
                                    }
                                    // Hiển thị ngày giờ và mã hóa đơn trong bill
                                    document.addEventListener('DOMContentLoaded', function() {
                                        $('#billModal').on('show.bs.modal', function () {
                                            var now = new Date();
                                            var date = now.toLocaleDateString('vi-VN');
                                            var time = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                                            var code = Math.floor(1000 + Math.random() * 9000);
                                            document.getElementById('bill-date').textContent = date;
                                            document.getElementById('bill-time').textContent = time;
                                            document.getElementById('bill-code').textContent = code;
                                            document.getElementById('bill-code-2').textContent = code;
                                        });
                                    });
                                    </script>
                                    </div>
                                    </div>
                                    </body>
                                    <!-- Đã loại bỏ đoạn script gán bill-code không tồn tại để tránh lỗi JS. -->
									</div>
								</div>
							</div>
						</div>
					@endif
				</div>
			</div>
		</div>
	</section>

	<div class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div class="footer-brand">
                    <h2>Choy's Cafe</h2>
                    <p>Hân hạnh đồng hành cùng quý khách!.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>Khám phá</h4>
                    <ul>
                        <li><a href="#">Menu</a></li>
                        <li><a href="#">Cửa hàng</a></li>
                        <li><a href="#">Đặt hàng online</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-links">
                    <h4>Dịch vụ</h4>
                    <ul>
                        <li><a href="#">Ship tận nơi</a></li>
                        <li><a href="#">Catering</a></li>
                        <li><a href="#">Thẻ thành viên</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-contact">
                    <!-- Liên hệ đã bị xóa theo yêu cầu -->
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+190099</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>8:00 - 21:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="copyright">
        <div class="container">
            <p>&copy; 2026 Choy's Cafe. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</footer>

<style>
    .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ff6b00;
    transition: 0.3s;
}

.user-avatar:hover {
    transform: scale(1.1);
}

/* DROPDOWN */
.user-dropdown {
    position: relative;
}

.user-dropdown .dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 120%;
    background: #1a1a1a;
    border-radius: 12px;
    min-width: 230px;
    padding: 10px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* SHOW HOVER */
.user-dropdown:hover .dropdown-menu {
    display: block;
    animation: fadeIn 0.3s ease;
}

/* USER INFO */
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
}

.user-info img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
}

.user-info p {
    margin: 0;
    font-size: 12px;
    color: #aaa;
}

/* ITEM */
.dropdown-item {
    color: #fff;
    padding: 10px 12px;
    border-radius: 8px;
    transition: 0.3s;
}

.dropdown-item:hover {
    background: #ff6b00;
    color: #fff;
}

/* ANIMATION */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
    /* === COFFEE FOOTER STYLES === */
    .coffee-footer {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #ffffff;
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
        line-height: 1.6;
        margin-top: 100px; /* Khoảng cách với nội dung chính */
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Newsletter */
    .newsletter-section {
        background: rgba(255, 107, 0, 0.1);
        padding: 60px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .newsletter-content {
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .newsletter-content h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 16px;
        background: linear-gradient(45deg, #ffffff, #ff6b00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .newsletter-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 32px;
    }

    .newsletter-form {
        display: flex;
        max-width: 400px;
        margin: 0 auto;
        gap: 12px;
    }

    .newsletter-form input {
        flex: 1;
        padding: 16px 20px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        font-size: 1rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .newsletter-form input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .newsletter-form input:focus {
        outline: none;
        border-color: #ff6b00;
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
    }

    .newsletter-form button {
        padding: 16px 28px;
        background: linear-gradient(45deg, #ff6b00, #ff8c42);
        border: none;
        border-radius: 50px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(255, 107, 0, 0.3);
    }

    .newsletter-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(255, 107, 0, 0.4);
    }

    /* Main Footer */
    .main-footer {
        padding: 60px 0 40px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 40px;
    }

    .footer-brand h2 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 16px;
        background: linear-gradient(45deg, #ffffff, #ff6b00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .footer-brand p {
        opacity: 0.8;
        margin-bottom: 24px;
    }

    .social-links {
        display: flex;
        gap: 16px;
    }

    .social-links a {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .social-links a:hover {
        background: #ff6b00;
        transform: translateY(-3px);
    }

    /* Links */
    .footer-links h4,
    .footer-contact h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
        position: relative;
    }

    .footer-links h4::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        width: 30px;
        height: 2px;
        background: #ff6b00;
    }

    .footer-links ul {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .footer-links a:hover {
        color: #ff6b00;
        padding-left: 6px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        opacity: 0.9;
    }

    .contact-item i {
        color: #ff6b00;
        width: 20px;
    }

    /* Copyright */
    .copyright {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px 0;
        text-align: center;
    }

    .copyright p {
        opacity: 0.7;
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .newsletter-form {
            flex-direction: column;
        }
        
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 30px;
            text-align: center;
        }
        
        .newsletter-content h3 {
            font-size: 1.8rem;
        }
    }
</style>

<style>
    /* Xóa background, border, shadow của phần thanh toán */
    .card {
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
</style>
    <script>
		document.addEventListener('DOMContentLoaded', function() {
			const userMenuBtn = document.getElementById('userMenuBtn');
			const userDropdownMenu = document.getElementById('userDropdownMenu');
			const dropdownContainer = document.querySelector('.user-dropdown-container');

			if (userMenuBtn && userDropdownMenu) {
				// Show dropdown on click
				userMenuBtn.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					userDropdownMenu.classList.toggle('active');
					userMenuBtn.classList.toggle('active');
				});

				// Keep dropdown open when hovering
				dropdownContainer.addEventListener('mouseenter', function() {
					userDropdownMenu.classList.add('active');
					userMenuBtn.classList.add('active');
				});

				dropdownContainer.addEventListener('mouseleave', function() {
					userDropdownMenu.classList.remove('active');
					userMenuBtn.classList.remove('active');
				});

				// Close dropdown when clicking outside
				document.addEventListener('click', function(e) {
					if (!dropdownContainer.contains(e.target)) {
						userDropdownMenu.classList.remove('active');
						userMenuBtn.classList.remove('active');
					}
				});

				// Close dropdown when clicking on a link
				const links = userDropdownMenu.querySelectorAll('.dropdown-link:not(.logout-link)');
				links.forEach(link => {
					link.addEventListener('click', function() {
						userDropdownMenu.classList.remove('active');
						userMenuBtn.classList.remove('active');
					});
				});
			}
		});
	</script>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-migrate-3.0.1.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.easing.1.3.js"></script>
	<script src="js/jquery.waypoints.min.js"></script>
	<script src="js/jquery.stellar.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/jquery.magnific-popup.min.js"></script>
	<script src="js/aos.js"></script>
	<script src="js/jquery.animateNumber.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/jquery.timepicker.min.js"></script>
	<script src="js/scrollax.min.js"></script>
	<script src="js/main.js"></script>
</body>

</html>