<!DOCTYPE html>
<html lang="en">

<head>
    <title>Giỏ Hàng - Choy's Cafe</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    @php
        $hasPendingQrOrder = session()->has('pending_qr_order_id');
        $initialCartTotal = collect($cart)->sum(fn ($item) => ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0)));
    @endphp
    <script>
        window.hasPendingQrOrder = {{ $hasPendingQrOrder ? 'true' : 'false' }};
        window.initialCartState = @json($cart);
        window.initialCartTotal = {{ json_encode($initialCartTotal) }};
    </script>
	 <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">

            <!-- LOGO -->
            <a class="navbar-brand mr-3" href="{{ url('/') }}">
                <img src="/images/logo.png" style="height:72px;width:auto;object-fit:contain;">
            </a>

            @include('components.search-bar')

            <!-- MOBILE BUTTON -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
                <span class="oi oi-menu"></span> Menu
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">

                    <!-- MENU ITEMS -->
                    <li class="nav-item"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                    @if(!(auth()->check() && auth()->user()->isStaff()))
                        <li class="nav-item"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
                    @endif
                    @auth
                        @if(!auth()->user()->isStaff())
                            <li class="nav-item"><a href="{{ route('orders.history') }}" class="nav-link">Lịch sử đơn hàng</a></li>
                        @endif
                    @endauth
                    @guest
                        <li class="nav-item">
                            <a href="{{ url('/login') }}" class="nav-link">Đăng nhập</a>
                        </li>
                    @endguest
                    <!-- SPACER -->
                    <li class="nav-item flex-spacer"></li>

                    <!-- CART -->
                    <li class="nav-item cart">
                        <a href="/cart" class="nav-link">
                            <span class="icon icon-shopping_cart"></span>
                            <span class="bag">
                                <small id="cart-count">{{ $cartCount ?? 0 }}</small>
                            </span>
                        </a>
                    </li>

                    <!-- USER DROPDOWN -->
                    @if(Auth::check())
                                    <li class="nav-item user-dropdown-wrapper">
                                        <div class="user-dropdown-container">

                                            <button class="user-avatar-btn" type="button" id="userMenuBtn">
                                                @if(Auth::user()->avatar)
                                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="user-avatar">
                                                @else
                                                    <img src="{{ asset('images/user.jpg') }}" class="user-avatar">
                                                @endif
                                            </button>

                                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                                <div class="dropdown-header-info">
                                                    <img src="{{ Auth::user()->avatar
                        ? asset('storage/' . Auth::user()->avatar)
                        : asset('images/user.jpg') }}" class="dropdown-avatar">

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

                                                <div class="dropdown-divider"></div>

                                                <a href="/profile" class="dropdown-link">Hồ sơ</a>

                                                @if(Auth::user()->role === 'admin')
                                                    <a href="/admin" class="dropdown-link">Quản trị</a>
                                                @endif

                                                <div class="dropdown-divider"></div>

                                                <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                                                    @csrf
                                                </form>

                                                <a href="#" class="dropdown-link logout-link"
                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    Đăng xuất
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                    @endif

                </ul>
            </div>
        </div>
    </nav>

    <section class="ftco-section" style="padding: 100px 0;">
        <div class="container cart-section-container">
			<div class="row justify-content-center">
                <div class="col-lg-12 cart-page-wrap">
					<h1 class="text-center mb-5">Đơn hàng của bạn</h1>
                    <div class="toast-wrap" id="toastWrap">
                        <div class="toast-icon">
                            <svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="#1a110d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="2 6 5 9 10 3" />
                            </svg>
                        </div>
                        <span id="toastMsg"></span>
                    </div>
                    <button type="button" class="back-to-top-btn" id="backToTopBtn" aria-label="Trở về đầu trang">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 19V5" />
                            <path d="m5 12 7-7 7 7" />
                        </svg>
                    </button>
                    <div class="table-responsive cart-table-wrap">
                        <table class="table table-striped cart-table">
							<thead>
                                <tr style="font-size:15px;">
                                    <th class="cart-image-col" style="font-size:15px; font-weight:600;">Hình Ảnh</th>
                                    <th class="cart-product-col" style="font-size:15px; font-weight:600;">Sản Phẩm</th>
                                    <th style="font-size:15px; font-weight:600;">Giá</th>
                                    <th class="cart-qty-col" style="font-size:15px; font-weight:600;">Số Lượng</th>
                                    <th style="font-size:15px; font-weight:600;">Tổng Cộng</th>
                                    <th style="font-size:15px; font-weight:600;"></th>
                                </tr>
							</thead>
                            <tbody id="cart-table-body">
								@if(count($cart) > 0)
									@foreach($cart as $key => $item)
										@php
											$itemTotal = $item['price'] * $item['qty'];
											$itemMeta = array_values(array_filter([
												!empty($item['size']) ? 'Kích cỡ: ' . $item['size'] : null,
												!empty($item['sugar']) ? 'Đường: ' . $item['sugar'] : null,
												!empty($item['ice']) ? 'Đá: ' . $item['ice'] : null,
												!empty($item['toppings']) && count($item['toppings']) > 0 ? 'Topping: ' . implode(', ', $item['toppings']) : null,
												!empty($item['note']) ? 'Ghi chú: ' . $item['note'] : null,
											]));
										@endphp
										<tr>
                                            <td class="cart-image-cell">
                                                <img src="{{ isset($item['image_url']) ? asset('images/' . $item['image_url']) : asset('images/no-image.png') }}" alt="{{ $item['name'] }}" class="cart-product-image">
                                            </td>
                                            <td class="cart-product-cell">
                                                <div class="cart-product-details">
                                                    <strong class="cart-product-name">{{ $item['name'] }}</strong><br>
                                                    @if(!empty($itemMeta))
                                                        <small class="text-muted cart-product-meta">
                                                            @foreach($itemMeta as $index => $metaLine)
                                                                @if($index > 0)<br>@endif{{ $metaLine }}
                                                            @endforeach
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
											<td>{{ number_format($item['price']) }} đ</td>
											<td class="cart-qty-cell">
												<div class="input-group input-group-sm cart-qty-group">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-outline-secondary btn-qty" data-action="decrease" data-key="{{ $key }}" type="button">-</button>
                                                    </div>
													<input type="text" class="form-control text-center cart-qty-input" value="{{ $item['qty'] }}" data-key="{{ $key }}" readonly>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary btn-qty" data-action="increase" data-key="{{ $key }}" type="button">+</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="item-total" data-key="{{ $key }}">{{ number_format($itemTotal) }} đ</td>
                                            <td>
                                                <button class="btn btn-sm btn-remove-item" data-key="{{ $key }}">Xóa</button>
                                            </td>
										</tr>
									@endforeach
								@else
                                    <tr id="cart-empty-row">
                                        <td colspan="6" class="text-center">Giỏ hàng của bạn đang trống</td>
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
                        <div class="row mt-5" id="cart-summary-section">
							<div class="col-md-6 offset-md-6">
								<div class="card">
									<div class="card-body">
                                           <h5 class="card-title">Tổng Cộng</h5>
                                           <h3 class="text-danger" id="cart-total">{{ number_format($total) }} đ</h3>
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
                                                                <img id="qr-code-image" src="{{ $qrApi }}" alt="QR code" style="width:220px;max-width:100%;border:2px solid #eee;padding:8px;background:#fff;">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="font-weight-bold mb-2" style="font-size:16px;">Cách 2: Chuyển khoản <b>thủ công</b> theo thông tin</div>
                                                            <div class="card p-3" style="background:#fff;border:1px solid #e0e0e0;">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <span style="font-weight:bold;font-size:18px;color:#1f9d55;">Vietcombank</span>
                                                                    </div>
                                                                    <div style="font-size:15px;">
                                                                        <div><b>Ngân hàng:</b> <span style="color:#1f9d55;">Vietcombank</span></div>
                                                                        <div><b>Thụ hưởng:</b> TRAN QUOC LONG</div>
                                                                        <div><b>Số tài khoản:</b> 1042131375</div>
                                                                        <div><b>Số tiền:</b> <span id="qr-amount">{{ number_format($qrAmount) }} đ</span></div>
                                                                        <div><b>Nội dung CK:</b> <span id="qr-note">{{ $qrNote }}</span></div>
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
                                                    <div class="text-center" style="font-size:13px;color:#111;">Tòa JOVE, Trung Mỹ Tây, Quận 12<br>0904xxxxxx</div>
                                                    <div class="text-center mt-2 mb-2" style="font-size:16px;font-weight:bold; color:#111;">HÓA ĐƠN <span id="bill-code"></span></div>
                                                    <div class="d-flex justify-content-between mb-1" style="font-size:13px;color:#111;">
                                                        <span style="color:#111;">Thời gian</span>
                                                        <span style="color:#111;"><span id="bill-time"></span> <span id="bill-date"></span></span>
                                                    </div>
                                                    <div style="display:flex; font-size:13px; font-weight:bold; margin-bottom:4px; margin-top:6px; text-align:left; color:#111; border-bottom:1px dashed #ddd; padding-bottom:6px;">
                                                        <div style="width:46%; padding-right:8px;">Tên sản phẩm</div>
                                                        <div style="width:18%; text-align:right;">Giá tiền</div>
                                                        <div style="width:10%; text-align:center;">SL</div>
                                                        <div style="width:26%; text-align:right;">Tổng cộng</div>
                                                    </div>
                                                        <div id="bill-items" style="color:#111;">
                                                        @foreach($cart as $item)
                                                            @php
                                                                $billMeta = array_values(array_filter([
                                                                    !empty($item['size']) ? '- Size: ' . $item['size'] : null,
                                                                    !empty($item['sugar']) ? '- Đường: ' . $item['sugar'] : null,
                                                                    !empty($item['ice']) ? '- Đá: ' . $item['ice'] : null,
                                                                    !empty($item['toppings']) && count($item['toppings']) > 0 ? '- Topping: ' . implode(', ', $item['toppings']) : null,
                                                                ]));
                                                            @endphp
                                                            <div style="padding:6px 0 8px; border-bottom:1px dashed #efefef;">
                                                                <div style="display:flex; align-items:flex-start; font-size:12px; color:#111;">
                                                                    <div style="width:46%; padding-right:8px; text-align:left;">
                                                                        <div style="font-size:12px; font-weight:600; line-height:1.35; word-break:break-word;">{{ $item['name'] }}</div>
                                                                        @if(!empty($billMeta))
                                                                            <div style="font-size:10px; color:#888; font-style:italic; margin-top:2px; line-height:1.4;">
                                                                                @foreach($billMeta as $metaLine)
                                                                                    <div>{{ $metaLine }}</div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div style="width:18%; text-align:right; white-space:nowrap;">{{ number_format($item['price']) }}</div>
                                                                    <div style="width:10%; text-align:center; white-space:nowrap;">{{ $item['qty'] }}</div>
                                                                    <div style="width:26%; text-align:right; white-space:nowrap;">{{ number_format($item['price'] * $item['qty']) }}</div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <!-- Đã xóa phần bảng tên sản phẩm phía dưới theo yêu cầu -->
                                                    <div class="d-flex justify-content-between" style="font-size:13px;color:#111;">
                                                        <span style="color:#111;">Tổng dịch vụ</span>
                                                            <span id="bill-service-total" style="color:#111;">{{ number_format($total) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1 mb-1" style="font-size:16px;font-weight:bold;color:#111;">
                                                        <span style="color:#111;">Thanh toán</span>
                                                            <span id="bill-grand-total" style="font-size:20px;color:#111;">{{ number_format($total) }}</span>
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
                                                        <button type="button" class="btn btn-success btn-sm px-4" onclick="confirmCashPayment()">Xác nhận thanh toán</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Đặt toàn bộ JS ở cuối file, sau tất cả HTML -->
                                    <script>
                                    function syncCartCount(cartCount) {
                                        var cartCountElement = document.getElementById('cart-count');
                                        if (cartCountElement && typeof cartCount !== 'undefined') {
                                            cartCountElement.textContent = cartCount;
                                        }
                                    }

                                    function getPaymentAudioContext() {
                                        var AudioContextClass = window.AudioContext || window.webkitAudioContext;
                                        if (!AudioContextClass) {
                                            return null;
                                        }

                                        if (!window.paymentAudioContext) {
                                            window.paymentAudioContext = new AudioContextClass();
                                        }

                                        return window.paymentAudioContext;
                                    }

                                    function primePaymentAudio() {
                                        var audioContext = getPaymentAudioContext();
                                        if (audioContext && audioContext.state === 'suspended') {
                                            audioContext.resume();
                                        }
                                    }

                                    function playPaymentSuccessSound() {
                                        var audioContext = getPaymentAudioContext();
                                        if (!audioContext) {
                                            return;
                                        }

                                        if (audioContext.state === 'suspended') {
                                            audioContext.resume().then(function() {
                                                playPaymentSuccessSound();
                                            }).catch(function() {});
                                            return;
                                        }

                                        var masterGain = audioContext.createGain();
                                        masterGain.connect(audioContext.destination);
                                        masterGain.gain.setValueAtTime(0.0001, audioContext.currentTime);
                                        masterGain.gain.exponentialRampToValueAtTime(0.18, audioContext.currentTime + 0.01);
                                        masterGain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + 0.9);

                                        var firstTone = audioContext.createOscillator();
                                        var secondTone = audioContext.createOscillator();

                                        firstTone.type = 'sine';
                                        secondTone.type = 'sine';

                                        firstTone.frequency.setValueAtTime(1318.51, audioContext.currentTime);
                                        secondTone.frequency.setValueAtTime(1760, audioContext.currentTime + 0.18);

                                        firstTone.connect(masterGain);
                                        secondTone.connect(masterGain);

                                        firstTone.start(audioContext.currentTime);
                                        firstTone.stop(audioContext.currentTime + 0.22);

                                        secondTone.start(audioContext.currentTime + 0.18);
                                        secondTone.stop(audioContext.currentTime + 0.48);
                                    }

                                    function showToast(message) {
                                        var toastMessage = document.getElementById('toastMsg');
                                        var toastWrap = document.getElementById('toastWrap');

                                        if (!toastMessage || !toastWrap) {
                                            return;
                                        }

                                        toastMessage.textContent = message;
                                        toastWrap.classList.add('show');

                                        clearTimeout(window.cartToastTimeout);
                                        window.cartToastTimeout = setTimeout(function() {
                                            toastWrap.classList.remove('show');
                                        }, 2800);
                                    }

                                    function escapeHtml(value) {
                                        return String(value ?? '')
                                            .replace(/&/g, '&amp;')
                                            .replace(/</g, '&lt;')
                                            .replace(/>/g, '&gt;')
                                            .replace(/"/g, '&quot;')
                                            .replace(/'/g, '&#39;');
                                    }

                                    function formatMoney(value, withCurrency) {
                                        var amount = Number(value || 0);
                                        var formatted = amount.toLocaleString('vi-VN');
                                        return withCurrency === false ? formatted : formatted + ' đ';
                                    }

                                    function getCartEntries(cart) {
                                        return Object.entries(cart || {});
                                    }

                                    function getCartMetaLines(item, prefix) {
                                        var lines = [];

                                        if (item.size) {
                                            lines.push((prefix || '') + 'Size: ' + item.size);
                                        }

                                        if (item.sugar) {
                                            lines.push((prefix || '') + 'Đường: ' + item.sugar);
                                        }

                                        if (item.ice) {
                                            lines.push((prefix || '') + 'Đá: ' + item.ice);
                                        }

                                        if (Array.isArray(item.toppings) && item.toppings.length > 0) {
                                            lines.push((prefix || '') + 'Topping: ' + item.toppings.join(', '));
                                        }

                                        if (item.note) {
                                            lines.push((prefix || '') + 'Ghi chú: ' + item.note);
                                        }

                                        return lines;
                                    }

                                    function calculateCartTotalFromState(cart) {
                                        return getCartEntries(cart).reduce(function(sum, entry) {
                                            var item = entry[1] || {};
                                            return sum + (Number(item.price || 0) * Number(item.qty || 0));
                                        }, 0);
                                    }

                                    function syncCartTableEmptyState(cart) {
                                        var tbody = document.getElementById('cart-table-body');
                                        if (!tbody) {
                                            return;
                                        }

                                        var hasItems = getCartEntries(cart).length > 0;
                                        var emptyRow = document.getElementById('cart-empty-row');

                                        if (hasItems) {
                                            if (emptyRow) {
                                                emptyRow.remove();
                                            }
                                            return;
                                        }

                                        if (!emptyRow) {
                                            emptyRow = document.createElement('tr');
                                            emptyRow.id = 'cart-empty-row';
                                            emptyRow.innerHTML = '<td colspan="6" class="text-center">Giỏ hàng của bạn đang trống</td>';
                                            tbody.appendChild(emptyRow);
                                        }
                                    }

                                    function syncSummaryVisibility(cart) {
                                        var hasItems = getCartEntries(cart).length > 0;
                                        var summarySection = document.getElementById('cart-summary-section');

                                        if (summarySection) {
                                            summarySection.style.display = hasItems ? '' : 'none';
                                        }

                                        if (!hasItems && window.jQuery) {
                                            $('#paymentMethodModal').modal('hide');
                                            $('#billModal').modal('hide');
                                            $('#qrPaymentModal').modal('hide');
                                        }
                                    }

                                    function syncBillItems(cart) {
                                        var billItems = document.getElementById('bill-items');
                                        if (!billItems) {
                                            return;
                                        }

                                        var entries = getCartEntries(cart);

                                        if (entries.length === 0) {
                                            billItems.innerHTML = '<div style="padding:10px 0; text-align:center; font-size:12px; color:#666;">Giỏ hàng của bạn đang trống</div>';
                                            return;
                                        }

                                        billItems.innerHTML = entries.map(function(entry) {
                                            var item = entry[1] || {};
                                            var metaLines = getCartMetaLines(item, '- ');
                                            var metaHtml = metaLines.length > 0
                                                ? '<div style="font-size:10px; color:#888; font-style:italic; margin-top:2px; line-height:1.4;">' + metaLines.map(function(line) {
                                                    return '<div>' + escapeHtml(line) + '</div>';
                                                }).join('') + '</div>'
                                                : '';
                                            var itemTotal = Number(item.price || 0) * Number(item.qty || 0);

                                            return '<div style="padding:6px 0 8px; border-bottom:1px dashed #efefef;">'
                                                + '<div style="display:flex; align-items:flex-start; font-size:12px; color:#111;">'
                                                + '<div style="width:46%; padding-right:8px; text-align:left;">'
                                                + '<div style="font-size:12px; font-weight:600; line-height:1.35; word-break:break-word;">' + escapeHtml(item.name || '') + '</div>'
                                                + metaHtml
                                                + '</div>'
                                                + '<div style="width:18%; text-align:right; white-space:nowrap;">' + formatMoney(item.price, false) + '</div>'
                                                + '<div style="width:10%; text-align:center; white-space:nowrap;">' + escapeHtml(item.qty || 0) + '</div>'
                                                + '<div style="width:26%; text-align:right; white-space:nowrap;">' + formatMoney(itemTotal, false) + '</div>'
                                                + '</div>'
                                                + '</div>';
                                        }).join('');
                                    }

                                    function syncPaymentSummary(cart, total) {
                                        var resolvedTotal = typeof total === 'number' ? total : calculateCartTotalFromState(cart);
                                        var serviceTotal = document.getElementById('bill-service-total');
                                        var grandTotal = document.getElementById('bill-grand-total');
                                        var cartTotal = document.getElementById('cart-total');
                                        var qrAmount = document.getElementById('qr-amount');
                                        var qrNote = document.getElementById('qr-note');
                                        var qrImage = document.getElementById('qr-code-image');

                                        syncBillItems(cart);

                                        if (serviceTotal) {
                                            serviceTotal.textContent = formatMoney(resolvedTotal, false);
                                        }

                                        if (grandTotal) {
                                            grandTotal.textContent = formatMoney(resolvedTotal, false);
                                        }

                                        if (cartTotal) {
                                            cartTotal.textContent = formatMoney(resolvedTotal, true);
                                        }

                                        if (qrAmount) {
                                            qrAmount.textContent = formatMoney(resolvedTotal, true);
                                        }

                                        if (qrImage) {
                                            var noteText = qrNote ? qrNote.textContent : '';
                                            qrImage.src = 'https://img.vietqr.io/image/vietcombank-1042131375-print.png?amount=' + Math.round(resolvedTotal) + '&addInfo=' + encodeURIComponent(noteText) + '&accountName=' + encodeURIComponent('TRAN QUOC LONG');
                                        }
                                    }

                                    function syncCartState(cart, total) {
                                        window.currentCartState = cart || {};
                                        window.currentCartTotal = typeof total === 'number' ? total : calculateCartTotalFromState(window.currentCartState);

                                        syncCartTableEmptyState(window.currentCartState);
                                        syncSummaryVisibility(window.currentCartState);
                                        syncPaymentSummary(window.currentCartState, window.currentCartTotal);
                                    }

                                    // Ajax cập nhật số lượng
                                    document.addEventListener('DOMContentLoaded', function() {
                                        window.currentCartState = window.initialCartState || {};
                                        window.currentCartTotal = Number(window.initialCartTotal || 0);
                                        syncCartState(window.currentCartState, window.currentCartTotal);

                                        document.querySelectorAll('.btn-qty').forEach(function(btn) {
                                            btn.addEventListener('click', function() {
                                                var key = this.getAttribute('data-key');
                                                var action = this.getAttribute('data-action');
                                                var input = document.querySelector('.cart-qty-input[data-key="' + key + '"]');
                                                var currentQty = parseInt(input.value);
                                                var newQty = action === 'increase' ? currentQty + 1 : currentQty - 1;
                                                if (newQty < 0) return;
                                                fetch('/cart/update/' + key, {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                                    },
                                                    body: JSON.stringify({ qty: newQty })
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        // Nếu sản phẩm đã bị xóa khỏi cart (qty=0), ẩn dòng đó
                                                        if (!data.cart || !data.cart[key]) {
                                                            var row = input.closest('tr');
                                                            if (row) row.remove();
                                                        } else {
                                                            // Cập nhật số lượng
                                                            input.value = newQty;
                                                            // Cập nhật tổng từng dòng
                                                            var item = data.cart[key];
                                                            var itemTotal = item.price * item.qty;
                                                            var itemTotalCell = document.querySelector('.item-total[data-key="' + key + '"]');
                                                            if (itemTotalCell) {
                                                                itemTotalCell.textContent = itemTotal.toLocaleString('vi-VN') + ' đ';
                                                            }
                                                        }

                                                        syncCartState(data.cart || {}, Number(data.total || 0));

                                                        syncCartCount(data.cart_count);
                                                    }
                                                });
                                            });
                                        });
                                    });
                                    // Ajax xóa sản phẩm khỏi giỏ hàng
                                    document.querySelectorAll('.btn-remove-item').forEach(function(btn) {
                                        btn.addEventListener('click', function() {
                                            var key = this.getAttribute('data-key');
                                            var row = this.closest('tr');
                                            fetch('/cart/update/' + key, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                                },
                                                body: JSON.stringify({ qty: 0 })
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    if (row) row.remove();

                                                        syncCartState(data.cart || {}, Number(data.total || 0));

                                                    syncCartCount(data.cart_count);
                                                }
                                            });
                                        });
                                    });
                                    var qrStatusPoller = null;

                                    function stopQrStatusPolling() {
                                        if (qrStatusPoller) {
                                            window.clearInterval(qrStatusPoller);
                                            qrStatusPoller = null;
                                        }
                                    }

                                    function startQrStatusPolling() {
                                        stopQrStatusPolling();

                                        qrStatusPoller = window.setInterval(function() {
                                            fetch('{{ route('cart.qr-status') }}', {
                                                headers: {
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                credentials: 'same-origin'
                                            })
                                            .then(function(res) {
                                                return res.json();
                                            })
                                            .then(function(data) {
                                                if (!data.success) {
                                                    return;
                                                }

                                                syncCartCount(data.cart_count || 0);

                                                if (data.paid) {
                                                    stopQrStatusPolling();
                                                    showToast(data.message || 'Đơn hàng đã được xác nhận thanh toán.');

                                                    window.setTimeout(function() {
                                                        window.location.href = data.redirect_url || '{{ route('orders.history') }}';
                                                    }, 900);
                                                } else if (!data.has_pending_qr) {
                                                    stopQrStatusPolling();
                                                }
                                            })
                                            .catch(function() {});
                                        }, 3000);
                                    }

                                    function confirmPayment() {
                                        primePaymentAudio();

                                        fetch('/cart/checkout/qr', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify({
                                                qr_note: (document.getElementById('qr-note') || {}).textContent || ''
                                            })
                                        })
                                        .then(function(res) {
                                            return res.json();
                                        })
                                        .then(function(data) {
                                            if (!data.success) {
                                                showToast(data.message || 'Không thể gửi xác nhận thanh toán.');
                                                return;
                                            }

                                            $('#qrPaymentModal').modal('hide');
                                            syncCartCount(data.cart_count);
                                            window.hasPendingQrOrder = true;
                                            startQrStatusPolling();

                                            setTimeout(function() {
                                                playPaymentSuccessSound();
                                                showToast(data.message || 'Đã gửi xác nhận thanh toán.');
                                            }, 400);
                                        })
                                        .catch(function() {
                                            showToast('Không thể gửi xác nhận thanh toán. Vui lòng thử lại.');
                                        });
                                    }
                                    function handleCashPayment() {
                                        if (getCartEntries(window.currentCartState).length === 0) {
                                            showToast('Giỏ hàng đang trống.');
                                            return;
                                        }

                                        $('#paymentMethodModal').modal('hide');
                                        setTimeout(function() { $('#billModal').modal('show'); }, 400);
                                    }
                                    function confirmCashPayment() {
                                        primePaymentAudio();
                                        fetch('/cart/checkout/cash', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify({})
                                        })
                                        .then(function(res) {
                                            return res.json();
                                        })
                                        .then(function(data) {
                                            if (!data.success) {
                                                showToast(data.message || 'Không thể thanh toán.');
                                                return;
                                            }

                                            $('#billModal').modal('hide');
                                            syncCartCount(data.cart_count);

                                            setTimeout(function() {
                                                playPaymentSuccessSound();
                                                showToast(data.message || 'Thanh toán thành công!');
                                                setTimeout(function() {
                                                    window.location.href = data.redirect_url || '/profile#order-history';
                                                }, 1200);
                                            }, 400);
                                        })
                                        .catch(function() {
                                            showToast('Không thể thanh toán. Vui lòng thử lại.');
                                        });
                                    }
                                    function showQRModal() {
                                        if (getCartEntries(window.currentCartState).length === 0) {
                                            showToast('Giỏ hàng đang trống.');
                                            return;
                                        }

                                        $('#paymentMethodModal').modal('hide');
                                        setTimeout(function() { $('#qrPaymentModal').modal('show'); }, 400);
                                    }
                                    // Hiển thị ngày giờ và mã hóa đơn trong bill
                                    document.addEventListener('DOMContentLoaded', function() {
                                        if (window.hasPendingQrOrder) {
                                            startQrStatusPolling();
                                        }

                                        $('#billModal').on('show.bs.modal', function () {
                                            var now = new Date();
                                            var date = now.toLocaleDateString('vi-VN');
                                            var time = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                                            var code = Math.floor(1000 + Math.random() * 9000);
                                            document.getElementById('bill-date').textContent = date;
                                            document.getElementById('bill-time').textContent = time;
                                            document.getElementById('bill-code').textContent = code;
                                            document.getElementById('bill-code-2').textContent = code;
                                            syncPaymentSummary(window.currentCartState || {}, window.currentCartTotal || 0);
                                        });
                                    });
                                    </script>
                                    </div>
                                    </div>
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

	  <footer class="coffee-footer">
        <!-- Newsletter Section -->

        <!-- Main Footer -->
        <div class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <!-- Brand -->
                    <div class="footer-brand">
                        <div class="brand-header"></div>
                        <h2 class="footer-logo">
                            <img src="/images/logo.png" alt="logo">Choy's Cafe
                        </h2>
                        <p> Hân hạnh đồng hành cùng quý khách!.</p>
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
                <p>&copy; 2026 Choy's Cafe. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

<style>
     .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            align-items: flex-start;
        }

        
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .brand-header img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .footer-brand h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        
        .footer-links,
        .footer-contact {
            padding-top: 10px;
        }

       
        .footer-links ul li {
            line-height: 1.8;
        }

        
        .social-links {
            margin-top: 10px;
        }

      
        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .brand-header {
                justify-content: center;
            }
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-logo img {
            height: 70px;
            width: auto;
            object-fit: contain;
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

    .cart-section-container {
        max-width: 1280px;
    }

    .cart-page-wrap {
        max-width: 1240px;
        flex: 0 0 100%;
    }

    .cart-table-wrap {
        overflow-x: visible;
    }

    .cart-table {
        table-layout: auto;
        width: 100%;
        min-width: 0;
    }

    .toast-wrap {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(-90px);
        background: #1a110d;
        color: #f0e6d0;
        padding: 10px 20px;
        border-radius: 30px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
        z-index: 99999;
        transition: transform .38s cubic-bezier(.25, .8, .25, 1);
        pointer-events: none;
    }

    .toast-wrap.show {
        transform: translateX(-50%) translateY(0);
    }

    .toast-icon {
        width: 20px;
        height: 20px;
        background: #c9a96e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .back-to-top-btn {
        position: fixed;
        right: 24px;
        bottom: 24px;
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, #c9a96e, #8a5b2f);
        color: #fff7ed;
        box-shadow: 0 14px 30px rgba(26, 17, 13, .22);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transform: translateY(16px);
        transition: opacity .25s ease, transform .25s ease, visibility .25s ease, box-shadow .25s ease;
        z-index: 9998;
    }

    .back-to-top-btn.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .back-to-top-btn:hover {
        box-shadow: 0 18px 34px rgba(26, 17, 13, .28);
        transform: translateY(-3px);
    }

    .back-to-top-btn:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(201, 169, 110, .25), 0 14px 30px rgba(26, 17, 13, .22);
    }

    .cart-image-col,
    .cart-image-cell {
        width: 130px;
        min-width: 130px;
        text-align: left;
        vertical-align: top;
    }

    .cart-product-col,
    .cart-product-cell {
        width: 36%;
        min-width: 0;
    }

    .cart-product-col {
        vertical-align: top;
        padding-top: 12px !important;
        text-align: center;
    }

    .cart-qty-col,
    .cart-qty-cell {
        text-align: center;
        vertical-align: middle;
    }

    .cart-product-cell {
        vertical-align: top;
    }

    .cart-table th:not(.cart-product-col),
    .cart-table td:not(.cart-product-cell) {
        white-space: nowrap;
        vertical-align: middle;
    }

    .cart-product-image {
        width: 104px;
        height: 104px;
        flex-shrink: 0;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #eee;
        margin-top: 2px;
    }

    .cart-product-details {
        min-width: 0;
        padding-top: 0;
    }

    .cart-product-name {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 17px;
        line-height: 1.45;
    }

    .cart-product-meta {
        display: block;
        font-size: 14px;
        line-height: 1.65;
        white-space: normal;
        word-break: break-word;
    }

    .cart-qty-group {
        max-width: 132px;
        flex-wrap: nowrap;
        align-items: center;
        margin: 0 auto;
    }

    .cart-qty-group .input-group-prepend,
    .cart-qty-group .input-group-append {
        display: flex;
    }

    .cart-qty-group .btn-qty {
        width: 34px;
        height: 34px;
        min-width: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
        line-height: 1;
        font-size: 16px;
        font-weight: 700;
        color: #111;
        background: #c49b63;
        border-color: #c49b63;
        box-shadow: none;
    }

    .cart-qty-group .btn-qty:hover,
    .cart-qty-group .btn-qty:focus,
    .cart-qty-group .btn-qty:active {
        color: #111;
        background: #b6894f;
        border-color: #b6894f;
        box-shadow: none;
    }

    .cart-qty-group .cart-qty-input {
        width: 52px;
        min-width: 52px;
        height: 34px;
        padding: 0;
        text-align: center;
        border-left: 0;
        border-right: 0;
        box-shadow: none;
    }

    .btn-remove-item {
        color: #111 !important;
        background: #c49b63;
        border-color: #c49b63;
        font-weight: 600;
    }

    .btn-remove-item:hover,
    .btn-remove-item:focus,
    .btn-remove-item:active {
        color: #111 !important;
        background: #b6894f;
        border-color: #b6894f;
        box-shadow: none;
    }

    @media (max-width: 991.98px) {
        .cart-page-wrap {
            max-width: 100%;
        }

        .cart-table-wrap {
            overflow-x: auto;
        }

        .cart-table {
            min-width: 820px;
        }
    }

    @media (max-width: 767.98px) {
        .cart-product-col {
            padding-left: 112px !important;
        }

        .cart-product-col,
        .cart-product-cell {
            width: 34%;
            min-width: 260px;
        }

        .cart-image-col,
        .cart-image-cell {
            width: 110px;
            min-width: 110px;
        }

        .cart-product-image {
            width: 84px;
            height: 84px;
        }

        .cart-product-name {
            font-size: 16px;
        }
    }

    .user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 999px;
    object-fit: cover;
    border: none;
    box-shadow: none;
    display: block;
    transition: 0.3s;
}

    .user-avatar-btn {
    margin-left: 8px;
    padding: 0;
    border: none;
    background: transparent;
    box-shadow: none;
    appearance: none;
    border-radius: 999px;
    overflow: hidden;
}

    .dropdown-avatar {
    width: 52px;
    height: 52px;
    border-radius: 999px;
    object-fit: cover;
    border: none;
    box-shadow: none;
    display: block;
    flex-shrink: 0;
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

@media (max-width: 767.98px) {
    .back-to-top-btn {
        right: 16px;
        bottom: 18px;
        width: 48px;
        height: 48px;
    }
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
	
	<!-- User Dropdown JS -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const userMenuBtn = document.getElementById('userMenuBtn');
			const userDropdownMenu = document.getElementById('userDropdownMenu');
			const dropdownContainer = document.querySelector('.user-dropdown-container');
            const backToTopBtn = document.getElementById('backToTopBtn');

            function toggleBackToTopButton() {
                if (!backToTopBtn) {
                    return;
                }

                if (window.scrollY > 320) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            }

            if (backToTopBtn) {
                toggleBackToTopButton();
                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                window.addEventListener('scroll', toggleBackToTopButton, { passive: true });
            }

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
</body>

</html>