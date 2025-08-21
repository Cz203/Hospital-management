 // Không cần xử lý chọn role vì chỉ có patient

    // Validation password
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;

        if (password !== confirmPassword) {
            this.setCustomValidity('Mật khẩu xác nhận không khớp!');
        } else {
            this.setCustomValidity('');
        }
    });