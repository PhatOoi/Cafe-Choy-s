// Set current year
document.getElementById('year').textContent = new Date().getFullYear();

// Coffee Newsletter
document.querySelector('.coffee-newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('.coffee-input').value;
    if (email) {
        alert('☕ Cảm ơn bạn đã tham gia Coffee Club!\nNhận ngay voucher 20% cho lần ghé thăm tiếp theo! 🎉');
        this.reset();
    }
});