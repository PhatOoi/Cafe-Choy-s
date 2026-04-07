<!DOCTYPE html>
<html lang="en">

<head>
	<title>Coffee Choy's</title>
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

	<link rel="stylesheet" href="css/bootstrap-datepicker.css">
	<link rel="stylesheet" href="css/jquery.timepicker.css">


	<link rel="stylesheet" href="css/flaticon.css">
	<link rel="stylesheet" href="css/icomoon.css">
	<link rel="stylesheet" href="css/style.css">
	
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
		<div class="container">
			<a class="navbar-brand" href="{{ url('/login') }}">Coffee<small>Choy's</small></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
				aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="oi oi-menu"></span> Menu
			</button>
			<div class="collapse navbar-collapse" id="ftco-nav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item active"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
					<li class="nav-item"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
					
					<li class="nav-item"><a href="contact.html" class="nav-link">Liên hệ</a></li>
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
						<li class="nav-item"><a href="{{ url('/login') }}" class="nav-link">Đăng nhập</a></li>
						<li class="nav-item cart"><a href="/cart" class="nav-link"><span
									class="icon icon-shopping_cart"></span><span
									class="bag d-flex justify-content-center align-items-center"><small id="cart-count">{{ $cartCount ?? 0 }}</small></span></a>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>
	<!-- END nav -->

	<section class="home-slider owl-carousel">
		<div class="slider-item" style="background-image: url(images/bg_1.jpg);">
			<div class="overlay"></div>
			<div class="container">
				<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

					<div class="col-md-8 col-sm-12 text-center ftco-animate">
						<span class="subheading">Welcome</span>
						<h1 class="mb-4">The Best Coffee Testing Experience</h1>
						<p class="mb-4 mb-md-5">A small river named Duden flows by their place and supplies it with the
							necessary regelialia.</p>
						<p><a href="/menu"
								class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">View Menu</a></p>
					</div>

				</div>
			</div>
		</div>

		<div class="slider-item" style="background-image: url(images/bg_2.jpg);">
			<div class="overlay"></div>
			<div class="container">
				<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

					<div class="col-md-8 col-sm-12 text-center ftco-animate">
						<span class="subheading">Welcome</span>
						<h1 class="mb-4">Amazing Taste &amp; Beautiful Place</h1>
						<p class="mb-4 mb-md-5">A small river named Duden flows by their place and supplies it with the
							necessary regelialia.</p>
						<p><a href="#" class="btn btn-primary p-3 px-xl-4 py-xl-3">Order Now</a> <a href="#"
								class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">View Menu</a></p>
					</div>

				</div>
			</div>
		</div>

		<div class="slider-item" style="background-image: url(images/bg_3.jpg);">
			<div class="overlay"></div>
			<div class="container">
				<div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

					<div class="col-md-8 col-sm-12 text-center ftco-animate">
						<span class="subheading">Welcome</span>
						<h1 class="mb-4">Creamy Hot and Ready to Serve</h1>
						<p class="mb-4 mb-md-5">A small river named Duden flows by their place and supplies it with the
							necessary regelialia.</p>
						<p><a href="#" class="btn btn-primary p-3 px-xl-4 py-xl-3">Order Now</a> <a href="#"
								class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">View Menu</a></p>
					</div>

				</div>
			</div>
		</div>
	</section>

	<section class="ftco-intro">
		<div class="container-wrap">
			<div class="wrap d-md-flex align-items-xl-end">
				<div class="info">
					<div class="row no-gutters">
						<div class="col-md-4 d-flex ftco-animate">
							<div class="icon"><span class="icon-phone"></span></div>
							<div class="text">
								<h3>190099</h3>
								<p>Liên hệ với chúng tôi để được hỗ trợ.</p>
							</div>
						</div>
						<div class="col-md-4 d-flex ftco-animate">
							<div class="icon"><span class="icon-my_location"></span></div>
							<div class="text">
								<h3>Tòa nhà JOVE</h3>
								<p> Quốc lộ 1A,Trung Mỹ Tây,TP. HCM</p>
							</div>
						</div>
						<div class="col-md-4 d-flex ftco-animate">
							<div class="icon"><span class="icon-clock-o"></span></div>
							<div class="text">
								<h3>Thứ Ba - Chủ Nhật</h3>
								<p>8:00am - 9:00pm</p>
							</div>
						</div>
					</div>
				</div>
				{{-- <div class="book p-4">
					<h3>Book a Table</h3>
					<form action="#" class="appointment-form">
						<div class="d-md-flex">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="First Name">
							</div>
							<div class="form-group ml-md-4">
								<input type="text" class="form-control" placeholder="Last Name">
							</div>
						</div>
						<div class="d-md-flex">
							<div class="form-group">
								<div class="input-wrap">
									<div class="icon"><span class="ion-md-calendar"></span></div>
									<input type="text" class="form-control appointment_date" placeholder="Date">
								</div>
							</div>
							<div class="form-group ml-md-4">
								<div class="input-wrap">
									<div class="icon"><span class="ion-ios-clock"></span></div>
									<input type="text" class="form-control appointment_time" placeholder="Time">
								</div>
							</div>
							<div class="form-group ml-md-4">
								<input type="text" class="form-control" placeholder="Phone">
							</div>
						</div>
						<div class="d-md-flex">
							<div class="form-group">
								<textarea name="" id="" cols="30" rows="2" class="form-control"
									placeholder="Message"></textarea>
							</div>
							<div class="form-group ml-md-4">
								<input type="submit" value="Appointment" class="btn btn-white py-3 px-4">
							</div>
						</div>
					</form>
				</div> --}}
			</div>
		</div>
	</section>

	<section class="ftco-about d-md-flex">
		<div class="one-half img" style="background-image: url(images/about.jpg);"></div>
		<div class="one-half ftco-animate">
			<div class="overlap">
				<div class="heading-section ftco-animate ">
					<span class="subheading">Choy's Coffee</span>
					<h2 class="mb-4">Niềm tự hào của chúng tôi</h2>
				</div>
				<div>
					<p>Niềm tự hào của quán chúng tôi không chỉ nằm ở cà phê, mà còn ở sự đa dạng trong từng loại thức
						uống. Từ cà phê đậm đà, trà thanh mát đến các loại nước trái cây tươi ngon – tất cả đều được pha
						chế kỹ lưỡng từ nguyên liệu chất lượng cao.Chúng tôi luôn không ngừng sáng tạo để mang đến cho
						khách hàng nhiều lựa chọn phong phú, phù hợp với mọi sở thích và nhu cầu. Mỗi ly nước không chỉ
						là một thức uống giải khát, mà còn là sự kết hợp của hương vị, cảm xúc và trải nghiệm..</p>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section ftco-services">
		<div class="container">
			<div class="row">
				<div class="col-md-4 ftco-animate">
					<div class="media d-block text-center block-6 services">
						<div class="icon d-flex justify-content-center align-items-center mb-5">
							<span class="flaticon-choices"></span>
						</div>
						<div class="media-body">
							<h3 class="heading">Dễ Dàng Đặt Hàng</h3>
							<p>Mang đến trải nghiệm đặt hàng nhanh chóng và tiện lợi, giúp bạn dễ dàng chọn món yêu
								thích chỉ trong vài bước đơn giản.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 ftco-animate">
					<div class="media d-block text-center block-6 services">
						<div class="icon d-flex justify-content-center align-items-center mb-5">
							<span class="flaticon-delivery-truck"></span>
						</div>
						<div class="media-body">
							<h3 class="heading">Giao Hàng Nhanh Chóng</h3>
							<p>Cam kết giao hàng nhanh chóng và đảm bảo chất lượng, giúp bạn nhận được món uống yêu
								thích chỉ trong thời gian ngắn nhất.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 ftco-animate">
					<div class="media d-block text-center block-6 services">
						<div class="icon d-flex justify-content-center align-items-center mb-5">
							<span class="flaticon-coffee-bean"></span>
						</div>
						<div class="media-body">
							<h3 class="heading">Chất Lượng Sản Phẩm</h3>
							<p>Cung cấp những sản phẩm chất lượng cao, được chọn lọc kỹ lưỡng từ nguyên liệu tốt nhất.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-md-6 pr-md-5">
					<div class="heading-section text-md-right ftco-animate">
						<span class="subheading">Khám Phá</span>
						<h2 class="mb-4">Our Menu</h2>
						<p class="mb-4">Mỗi món nước đều được pha chế tỉ mỉ, kết hợp hương vị độc đáo nhằm mang đến cho
							bạn trải nghiệm mới mẻ và đầy cảm hứng. Hãy để mỗi lần ghé quán là một hành trình khám phá
							hương vị thú vị.</p>
						<p><a href="/menu" class="btn btn-primary btn-outline-primary px-4 py-3">View Full Menu</a></p>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="col-md-6">
							<div class="menu-entry">
								<a href="#" class="img" style="background-image: url(images/menu-1.jpg);"></a>
							</div>
						</div>
						<div class="col-md-6">
							<div class="menu-entry mt-lg-4">
								<a href="#" class="img" style="background-image: url(images/menu-2.jpg);"></a>
							</div>
						</div>
						<div class="col-md-6">
							<div class="menu-entry">
								<a href="#" class="img" style="background-image: url(images/menu-3.jpg);"></a>
							</div>
						</div>
						<div class="col-md-6">
							<div class="menu-entry mt-lg-4">
								<a href="#" class="img" style="background-image: url(images/menu-4.jpg);"></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-counter ftco-bg-dark img" id="section-counter" style="background-image: url(images/bg_2.jpg);"
		data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-10">
					<div class="row">
						<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
							<div class="block-18 text-center">
								<div class="text">
									<div class="icon"><span class="flaticon-coffee-cup"></span></div>
									<strong class="number" data-number="56">0</strong>
									<span>Số Chi Nhánh Quán</span>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
							<div class="block-18 text-center">
								<div class="text">
									<div class="icon"><span class="flaticon-coffee-cup"></span></div>
									<strong class="number" data-number="21">0</strong>
									<span>Giải Thưởng</span>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
							<div class="block-18 text-center">
								<div class="text">
									<div class="icon"><span class="flaticon-coffee-cup"></span></div>
									<strong class="number" data-number="10567">0</strong>
									<span>Khách hàng hạnh phúc</span>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
							<div class="block-18 text-center">
								<div class="text">
									<div class="icon"><span class="flaticon-coffee-cup"></span></div>
									<strong class="number" data-number="254">0</strong>
									<span>Nhân Viên</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	{{-- <section class="ftco-section">
		<div class="container">
			<div class="row justify-content-center mb-5 pb-3">
				<div class="col-md-7 heading-section ftco-animate text-center">
					<span class="subheading">Khám phá</span>
					<h2 class="mb-4">Thực đơn bán chạy nhất</h2>
					<p>Khách hàng thấy tuyệt vời khi dùng các thức uống này.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="menu-entry">
						<a href="#" class="img" style="background-image: url(images/menu-1.jpg);"></a>
						<div class="text text-center pt-4">
							<h3><a href="#">Coffee Capuccino</a></h3>
							<p>Đang chỉnh</p>
							<p class="price"><span>$5.90</span></p>
							<p><a href="#" class="btn btn-primary btn-outline-primary">Add to Cart</a></p>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="menu-entry">
						<a href="#" class="img" style="background-image: url(images/menu-2.jpg);"></a>
						<div class="text text-center pt-4">
							<h3><a href="#">Coffee Capuccino</a></h3>
							<p>Đang Chỉnh</p>
							<p class="price"><span>$5.90</span></p>
							<p><a href="#" class="btn btn-primary btn-outline-primary">Add to Cart</a></p>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="menu-entry">
						<a href="#" class="img" style="background-image: url(images/menu-3.jpg);"></a>
						<div class="text text-center pt-4">
							<h3><a href="#">Coffee Capuccino</a></h3>
							<p>Đang chỉnh</p>
							<p class="price"><span>$5.90</span></p>
							<p><a href="#" class="btn btn-primary btn-outline-primary">Add to Cart</a></p>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="menu-entry">
						<a href="#" class="img" style="background-image: url(images/menu-4.jpg);"></a>
						<div class="text text-center pt-4">
							<h3><a href="#">Coffee Capuccino</a></h3>
							<p>Đang chỉnh</p>
							<p class="price"><span>$5.90</span></p>
							<p><a href="#" class="btn btn-primary btn-outline-primary">Add to Cart</a></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section> --}}

	{{-- <section class="ftco-gallery">
		<div class="container-wrap">
			<div class="row no-gutters">
				<div class="col-md-3 ftco-animate">
					<a href="gallery.html" class="gallery img d-flex align-items-center"
						style="background-image: url(images/gallery-1.jpg);">
						<div class="icon mb-4 d-flex align-items-center justify-content-center">
							<span class="icon-search"></span>
						</div>
					</a>
				</div>
				<div class="col-md-3 ftco-animate">
					<a href="gallery.html" class="gallery img d-flex align-items-center"
						style="background-image: url(images/gallery-2.jpg);">
						<div class="icon mb-4 d-flex align-items-center justify-content-center">
							<span class="icon-search"></span>
						</div>
					</a>
				</div>
				<div class="col-md-3 ftco-animate">
					<a href="gallery.html" class="gallery img d-flex align-items-center"
						style="background-image: url(images/gallery-3.jpg);">
						<div class="icon mb-4 d-flex align-items-center justify-content-center">
							<span class="icon-search"></span>
						</div>
					</a>
				</div>
				<div class="col-md-3 ftco-animate">
					<a href="gallery.html" class="gallery img d-flex align-items-center"
						style="background-image: url(images/gallery-4.jpg);">
						<div class="icon mb-4 d-flex align-items-center justify-content-center">
							<span class="icon-search"></span>
						</div>
					</a>
				</div>
			</div>
		</div>
	</section> --}}

	{{-- <section class="ftco-menu">
		<div class="container">
			<div class="row justify-content-center mb-5">
				<div class="col-md-7 heading-section text-center ftco-animate">
					<span class="subheading">Discover</span>
					<h2 class="mb-4">Our Products</h2>
					<p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there
						live the blind texts.</p>
				</div>
			</div>
			<div class="row d-md-flex">
				<div class="col-lg-12 ftco-animate p-md-5">
					<div class="row">
						<div class="col-md-12 nav-link-wrap mb-5">
							<div class="nav ftco-animate nav-pills justify-content-center" id="v-pills-tab"
								role="tablist" aria-orientation="vertical">
								<a class="nav-link active" id="v-pills-1-tab" data-toggle="pill" href="#v-pills-1"
									role="tab" aria-controls="v-pills-1" aria-selected="true">Main Dish</a>

								<a class="nav-link" id="v-pills-2-tab" data-toggle="pill" href="#v-pills-2" role="tab"
									aria-controls="v-pills-2" aria-selected="false">Drinks</a>

								<a class="nav-link" id="v-pills-3-tab" data-toggle="pill" href="#v-pills-3" role="tab"
									aria-controls="v-pills-3" aria-selected="false">Desserts</a>
							</div>
						</div>
						<div class="col-md-12 d-flex align-items-center">

							<div class="tab-content ftco-animate" id="v-pills-tabContent">

								<div class="tab-pane fade show active" id="v-pills-1" role="tabpanel"
									aria-labelledby="v-pills-1-tab">
									<div class="row">
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/dish-1.jpg);"></a>
												<div class="text">
													<h3><a href="#">Grilled Beef</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/dish-2.jpg);"></a>
												<div class="text">
													<h3><a href="#">Grilled Beef</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/dish-3.jpg);"></a>
												<div class="text">
													<h3><a href="#">Grilled Beef</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="tab-pane fade" id="v-pills-2" role="tabpanel"
									aria-labelledby="v-pills-2-tab">
									<div class="row">
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/drink-1.jpg);"></a>
												<div class="text">
													<h3><a href="#">Lemonade Juice</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/drink-2.jpg);"></a>
												<div class="text">
													<h3><a href="#">Pineapple Juice</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/drink-3.jpg);"></a>
												<div class="text">
													<h3><a href="#">Soda Drinks</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="tab-pane fade" id="v-pills-3" role="tabpanel"
									aria-labelledby="v-pills-3-tab">
									<div class="row">
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/dessert-1.jpg);"></a>
												<div class="text">
													<h3><a href="#">Hot Cake Honey</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/dessert-2.jpg);"></a>
												<div class="text">
													<h3><a href="#">Hot Cake Honey</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<div class="menu-wrap">
												<a href="#" class="menu-img img mb-4"
													style="background-image: url(images/dessert-3.jpg);"></a>
												<div class="text">
													<h3><a href="#">Hot Cake Honey</a></h3>
													<p>Far far away, behind the word mountains, far from the countries
														Vokalia and Consonantia.</p>
													<p class="price"><span>$2.90</span></p>
													<p><a href="#" class="btn btn-primary btn-outline-primary">Add to
															cart</a></p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section> --}}
{{-- 
	<section class="ftco-section img" id="ftco-testimony" style="background-image: url(images/bg_1.jpg);"
		data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row justify-content-center mb-5">
				<div class="col-md-7 heading-section text-center ftco-animate">
					<span class="subheading">Testimony</span>
					<h2 class="mb-4">Customers Says</h2>
					<p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there
						live the blind texts.</p>
				</div>
			</div>
		</div>
		<div class="container-wrap">
			<div class="row d-flex no-gutters">
				<div class="col-lg align-self-sm-end ftco-animate">
					<div class="testimony">
						<blockquote>
							<p>&ldquo;Even the all-powerful Pointing has no control about the blind texts it is an
								almost unorthographic life One day however a small.&rdquo;</p>
						</blockquote>
						<div class="author d-flex mt-4">
							<div class="image mr-3 align-self-center">
								<img src="images/person_1.jpg" alt="">
							</div>
							<div class="name align-self-center">Louise Kelly <span class="position">Illustrator
									Designer</span></div>
						</div>
					</div>
				</div>
				<div class="col-lg align-self-sm-end">
					<div class="testimony overlay">
						<blockquote>
							<p>&ldquo;Even the all-powerful Pointing has no control about the blind texts it is an
								almost unorthographic life One day however a small line of blind text by the name of
								Lorem Ipsum decided to leave for the far World of Grammar.&rdquo;</p>
						</blockquote>
						<div class="author d-flex mt-4">
							<div class="image mr-3 align-self-center">
								<img src="images/person_2.jpg" alt="">
							</div>
							<div class="name align-self-center">Louise Kelly <span class="position">Illustrator
									Designer</span></div>
						</div>
					</div>
				</div>
				<div class="col-lg align-self-sm-end ftco-animate">
					<div class="testimony">
						<blockquote>
							<p>&ldquo;Even the all-powerful Pointing has no control about the blind texts it is an
								almost unorthographic life One day however a small line of blind text by the name.
								&rdquo;</p>
						</blockquote>
						<div class="author d-flex mt-4">
							<div class="image mr-3 align-self-center">
								<img src="images/person_3.jpg" alt="">
							</div>
							<div class="name align-self-center">Louise Kelly <span class="position">Illustrator
									Designer</span></div>
						</div>
					</div>
				</div>
				<div class="col-lg align-self-sm-end">
					<div class="testimony overlay">
						<blockquote>
							<p>&ldquo;Even the all-powerful Pointing has no control about the blind texts it is an
								almost unorthographic life One day however.&rdquo;</p>
						</blockquote>
						<div class="author d-flex mt-4">
							<div class="image mr-3 align-self-center">
								<img src="images/person_2.jpg" alt="">
							</div>
							<div class="name align-self-center">Louise Kelly <span class="position">Illustrator
									Designer</span></div>
						</div>
					</div>
				</div>
				<div class="col-lg align-self-sm-end ftco-animate">
					<div class="testimony">
						<blockquote>
							<p>&ldquo;Even the all-powerful Pointing has no control about the blind texts it is an
								almost unorthographic life One day however a small line of blind text by the name.
								&rdquo;</p>
						</blockquote>
						<div class="author d-flex mt-4">
							<div class="image mr-3 align-self-center">
								<img src="images/person_3.jpg" alt="">
							</div>
							<div class="name align-self-center">Louise Kelly <span class="position">Illustrator
									Designer</span></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section> --}}

	{{-- <section class="ftco-section">
		<div class="container">
			<div class="row justify-content-center mb-5 pb-3">
				<div class="col-md-7 heading-section ftco-animate text-center">
					<h2 class="mb-4">Recent from blog</h2>
					<p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there
						live the blind texts.</p>
				</div>
			</div>
			<div class="row d-flex">
				<div class="col-md-4 d-flex ftco-animate">
					<div class="blog-entry align-self-stretch">
						<a href="blog-single.html" class="block-20"
							style="background-image: url('images/image_1.jpg');">
						</a>
						<div class="text py-4 d-block">
							<div class="meta">
								<div><a href="#">Sept 10, 2018</a></div>
								<div><a href="#">Admin</a></div>
								<div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
							</div>
							<h3 class="heading mt-2"><a href="#">The Delicious Pizza</a></h3>
							<p>A small river named Duden flows by their place and supplies it with the necessary
								regelialia.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 d-flex ftco-animate">
					<div class="blog-entry align-self-stretch">
						<a href="blog-single.html" class="block-20"
							style="background-image: url('images/image_2.jpg');">
						</a>
						<div class="text py-4 d-block">
							<div class="meta">
								<div><a href="#">Sept 10, 2018</a></div>
								<div><a href="#">Admin</a></div>
								<div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
							</div>
							<h3 class="heading mt-2"><a href="#">The Delicious Pizza</a></h3>
							<p>A small river named Duden flows by their place and supplies it with the necessary
								regelialia.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 d-flex ftco-animate">
					<div class="blog-entry align-self-stretch">
						<a href="blog-single.html" class="block-20"
							style="background-image: url('images/image_3.jpg');">
						</a>
						<div class="text py-4 d-block">
							<div class="meta">
								<div><a href="#">Sept 10, 2018</a></div>
								<div><a href="#">Admin</a></div>
								<div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
							</div>
							<h3 class="heading mt-2"><a href="#">The Delicious Pizza</a></h3>
							<p>A small river named Duden flows by their place and supplies it with the necessary
								regelialia.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section> --}}

{{-- gg map --}}
	<section class="ftco-appointment">
		<div class="overlay"></div>
		<div class="container-wrap">
			<div class="row no-gutters d-md-flex align-items-center">
				<div class="col-md-6 d-flex align-self-stretch">
					<iframe
						src="https://www.google.com/maps?q=Cao%20Đẳng%20Kỹ%20Thuật%20Du%20Lịch%20Sài%20Gòn&hl=vi&z=16&output=embed"
						width="100%" 
						height="400" 
						style="border:0;" 
						allowfullscreen="" 
						loading="lazy">
					</iframe>
				</div>
				<div class="col-md-6 appointment ftco-animate">
					<h3 class="mb-3">Book a Table</h3>
					<form action="#" class="appointment-form">
						<div class="d-md-flex">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="First Name">
							</div>
							<div class="form-group ml-md-4">
								<input type="text" class="form-control" placeholder="Last Name">
							</div>
						</div>
						<div class="d-md-flex">
							<div class="form-group">
								<div class="input-wrap">
									<div class="icon"><span class="ion-md-calendar"></span></div>
									<input type="text" class="form-control appointment_date" placeholder="Date">
								</div>
							</div>
							<div class="form-group ml-md-4">
								<div class="input-wrap">
									<div class="icon"><span class="ion-ios-clock"></span></div>
									<input type="text" class="form-control appointment_time" placeholder="Time">
								</div>
							</div>
							<div class="form-group ml-md-4">
								<input type="text" class="form-control" placeholder="Phone">
							</div>
						</div>
						<div class="d-md-flex">
							<div class="form-group">
								<textarea name="" id="" cols="30" rows="2" class="form-control"
									placeholder="Message"></textarea>
							</div>
							<div class="form-group ml-md-4">
								<input type="submit" value="Appointment" class="btn btn-primary py-3 px-4">
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>

	<footer class="coffee-footer">
    <!-- Newsletter Section -->
    <div class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h3>Nhận ưu đãi đặc biệt</h3>
                <p>Đăng ký để nhận thông tin về cà phê mới và ưu đãi độc quyền</p>
                <div class="newsletter-form">
                    <input type="email" placeholder="Nhập email của bạn" id="emailInput">
                    <button onclick="subscribeNewsletter()">Đăng ký</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Footer -->
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

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    function subscribeNewsletter() {
        const emailInput = document.getElementById('emailInput');
        const email = emailInput.value.trim();
        const button = emailInput.nextElementSibling;
        
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            button.style.background = '#ef4444';
            setTimeout(() => button.style.background = '', 500);
            return;
        }
        
        button.textContent = 'Đã đăng ký!';
		
        button.style.background = '#10b981';
        emailInput.value = '';
        
        setTimeout(() => {
            button.textContent = 'Đăng ký';
            button.style.background = 'linear-gradient(45deg, #ff6b00, #ff8c42)';
        }, 2000);
    }

    // Enter key
    document.getElementById('emailInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') subscribeNewsletter();
    });
</script>


	<!-- loader -->
	<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
			<circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
			<circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
				stroke="#F96D00" />
		</svg></div>

	
	<script src="js/footer.js"></script>
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
	<script
		src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
	<script src="js/google-map.js"></script>
	<script src="js/main.js"></script>

</body>

</html>