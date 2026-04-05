<!DOCTYPE html>
<html lang="en">

<head>
	<title>Giỏ Hàng - Coffee Choy's</title>
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
			<a class="navbar-brand" href="/">Coffee<br><span>Choy's</span></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="oi oi-menu"></span> Menu
			</button>
			<div class="collapse navbar-collapse" id="ftco-nav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item"><a href="/" class="nav-link">Trang Chủ</a></li>
					<li class="nav-item"><a href="/menu" class="nav-link">Menu</a></li>
					<li class="nav-item"><a href="/about" class="nav-link">Về Chúng Tôi</a></li>
					<li class="nav-item active"><a href="/cart" class="nav-link">Giỏ Hàng</a></li>
					@if(Auth::check())
						
						<li class="nav-item">
							<span class="nav-link">Hello, {{ Auth::user()->name }}</span>
						</li>
						<li class="nav-item">
							<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display:none;">
								@csrf
							</form>
							<a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="cursor:pointer;">Đăng xuất</a>
						</li>
						<li class="nav-item cart"><a href="/cart" class="nav-link"><span
									class="icon icon-shopping_cart"></span><span
									class="bag d-flex justify-content-center align-items-center"><small id="cart-count">{{ $cartCount ?? 0 }}</small></span></a>
						</li>
					@else
						<li class="nav-item"><a href="{{ url('/login') }}" class="nav-link">Login</a></li>
						<li class="nav-item cart"><a href="/cart" class="nav-link"><span
									class="icon icon-shopping_cart"></span><span
									class="bag d-flex justify-content-center align-items-center"><small>1</small></span></a>
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
					<h1 class="text-center mb-5">Giỏ Hàng Của Bạn</h1>
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
										<button class="btn btn-primary btn-block mt-3">Thanh Toán</button>
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
                    <h2>☕ CoffeeChoy's</h2>
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
                    <h4>Liên hệ</h4>
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
            <p>&copy; 2026 CoffeeChoy's. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</footer>

<style>
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