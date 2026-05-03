// Set current year when the footer slot exists on current page.
var yearEl = document.getElementById('year');
if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
}

// Handle newsletter submit only when the form exists on current page.
var newsletterForm = document.querySelector('.coffee-newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var input = this.querySelector('.coffee-input');
        var email = input ? input.value : '';

        if (email) {
            alert('☕ Cảm ơn bạn đã tham gia Coffee Club!\nNhận ngay voucher 20% cho lần ghé thăm tiếp theo! 🎉');
            this.reset();
        }
    });
}