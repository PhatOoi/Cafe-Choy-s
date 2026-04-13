<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>☕ Cafe Shop - Thanh toán</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:'Poppins',sans-serif;
    background:#0a0a0a;
    color:#fff;
    padding:20px;
}

.container{
    max-width:1100px;
    margin:auto;
    border-radius:25px;
    overflow:hidden;
    background:rgba(255,255,255,0.05);
}

.header{
    text-align:center;
    padding:40px;
    background:#111;
}

.logo{
    font-size:2em;
    font-weight:700;
}

.content{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:30px;
    padding:30px;
}

.section{
    background:rgba(255,255,255,0.05);
    padding:25px;
    border-radius:20px;
}

/* cart */
.cart-item{
    display:flex;
    align-items:center;
    margin-bottom:15px;
}
.cart-item img{
    width:60px;height:60px;border-radius:10px;margin-right:10px;
}
.item-price{margin-left:auto;font-weight:bold}

/* form */
input,textarea{
    width:100%;
    padding:12px;
    margin-top:5px;
    margin-bottom:15px;
    border-radius:10px;
    border:none;
    background:#222;
    color:#fff;
}

/* payment */
.payment-methods{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}
.payment-card{
    padding:15px;
    border:1px solid #444;
    border-radius:10px;
    text-align:center;
    cursor:pointer;
}
.payment-card.active{
    border:2px solid #fff;
    background:#333;
}

/* QR */
.qr-section{
    display:none;
    text-align:center;
    margin-top:20px;
}
.qr-section.active{display:block}

.qr-code{
    width:200px;
    margin:auto;
}

/* button */
.pay-button{
    width:100%;
    padding:15px;
    background:#000;
    color:#fff;
    border:1px solid #fff;
    border-radius:10px;
    cursor:pointer;
}

/* toast */
.toast{
    position:fixed;
    bottom:20px;
    right:20px;
    background:#000;
    padding:15px;
    border-radius:10px;
    border:1px solid #fff;
    opacity:0;
    transition:0.4s;
}
</style>
</head>

<body>

<div class="container">

<div class="header">
    <div class="logo">☕ Cafe Shop</div>
    <h2>Thanh toán</h2>
</div>

<div class="content">

<!-- CART -->
<div class="section">
    <h3>Giỏ hàng</h3>

    <div class="cart-item">
        <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085">
        <div>Cà phê đen đá</div>
        <div class="item-price">45k</div>
    </div>

    <div class="cart-item">
        <img src="https://images.unsplash.com/photo-1512568400610-42b9a8bc0e3f">
        <div>Bánh croissant</div>
        <div class="item-price">35k</div>
    </div>

    <div class="cart-item">
        <img src="https://images.unsplash.com/photo-1577968897966-f97b209163d6">
        <div>Latte</div>
        <div class="item-price">55k</div>
    </div>

    <h2>Tổng: 135k</h2>
</div>

<!-- PAYMENT -->
<div class="section">

<h3>Thông tin</h3>

<input id="name" placeholder="Tên">
<input id="phone" placeholder="SĐT">
<textarea id="address" placeholder="Địa chỉ / Bàn"></textarea>

<h3>Thanh toán</h3>

<div class="payment-methods">
    <div class="payment-card active" data-method="cash">Tiền mặt</div>
    <div class="payment-card" data-method="qr">QR</div>
</div>

<div class="qr-section" id="qr">
    <img class="qr-code" src="https://img.vietqr.io/image/VCB-1234567890-compact.png">
    <p>Ngân hàng: Vietcombank</p>
    <p>STK: 1234567890</p>
    <p id="content">Nội dung: ...</p>
</div>

<button class="pay-button" onclick="pay()">Thanh toán</button>

</div>

</div>
</div>

<div id="toast" class="toast"></div>

<script>
let method = "cash";

const cards = document.querySelectorAll(".payment-card");
const qr = document.getElementById("qr");

cards.forEach(c=>{
    c.onclick=()=>{
        cards.forEach(x=>x.classList.remove("active"));
        c.classList.add("active");
        method=c.dataset.method;

        if(method==="qr"){
            qr.classList.add("active");
        }else{
            qr.classList.remove("active");
        }
    }
});

function show(msg){
    let t=document.getElementById("toast");
    t.innerText=msg;
    t.style.opacity=1;
    setTimeout(()=>t.style.opacity=0,2000);
}

function pay(){
    let name=document.getElementById("name").value;
    let phone=document.getElementById("phone").value;
    let address=document.getElementById("address").value;

    if(!name||!phone||!address){
        show("⚠️ Nhập đầy đủ thông tin!");
        return;
    }

    document.getElementById("content").innerText=
        "Nội dung: "+name+" - Cafe";

    if(method==="qr"){
        show("📲 Quét QR để thanh toán");
    }else{
        show("✅ Đặt hàng thành công!");
    }
}
</script>

</body>
</html>