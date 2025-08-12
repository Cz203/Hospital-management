# Layout System - Hệ thống Quản lý Bệnh viện

## Tổng quan

Hệ thống layout được thiết kế để cung cấp giao diện nhất quán cho tất cả các trang trong ứng dụng, với sidebar và header riêng biệt cho từng role (Admin, Doctor, Patient).

## Cấu trúc thư mục

```
Views/layouts/
├── header.php              # Header chung cho tất cả các role
├── footer.php              # Footer chung
├── main_layout.php         # Layout chính kết hợp header + sidebar + content
├── admin_sidebar.php       # Sidebar cho Admin
├── doctor_sidebar.php      # Sidebar cho Doctor
├── patient_sidebar.php     # Sidebar cho Patient
├── layout_helper.php       # Helper functions
└── README.md              # File này
```

## Cách sử dụng

### 1. Sử dụng Layout hoàn chỉnh (Khuyến nghị)

```php
<?php
require_once 'Views/layouts/layout_helper.php';

// Tạo content cho trang
$content = '
<div class="container">
    <h1>Dashboard</h1>
    <p>Nội dung trang...</p>
</div>
';

// Render layout với content
renderLayout($content, 'Dashboard - Admin');
?>
```

### 2. Sử dụng từng component riêng lẻ

```php
<?php
require_once 'Views/layouts/layout_helper.php';

// Render header
renderHeader();

// Render sidebar cho role cụ thể
renderSidebar('admin');

// Nội dung trang
echo '<div class="main-content">';
echo '<h1>Dashboard</h1>';
echo '<p>Nội dung trang...</p>';
echo '</div>';

// Render footer
renderFooter();
?>
```

### 3. Sử dụng trong Controller

```php
public function dashboard() {
    $this->requireAuth('admin');

    $content = '
    <div class="container">
        <h1>Admin Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số bệnh nhân</h5>
                        <p class="card-text">150</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ';

    renderLayout($content, 'Admin Dashboard');
}
```

## Tính năng

### Responsive Design

- Sidebar tự động ẩn trên mobile
- Toggle button để mở/đóng sidebar
- Layout thích ứng với mọi kích thước màn hình

### Role-based Navigation

- Menu khác nhau cho từng role
- Màu sắc và icon phù hợp với từng role
- User info hiển thị role badge

### Alert System

- Hiển thị thông báo thành công/lỗi
- Tự động ẩn sau 5 giây
- Styling nhất quán

### User Menu

- Dropdown menu với avatar
- Thông tin user và role
- Link đăng xuất

## Customization

### Thay đổi màu sắc

Các biến CSS có thể được tùy chỉnh trong file `main_layout.php`:

```css
.role-admin {
  background-color: #dc3545;
}
.role-doctor {
  background-color: #198754;
}
.role-patient {
  background-color: #0d6efd;
}
```

### Thêm menu item

Thêm menu item vào sidebar tương ứng:

```php
<li class="nav-item">
    <a class="nav-link text-white py-3 px-3 d-flex align-items-center" href="#">
        <i class="fas fa-icon me-3"></i>
        <span>Menu Item</span>
    </a>
</li>
```

### Active Menu

Sử dụng helper function để set active menu:

```php
<a class="nav-link <?php echo isActiveMenu('dashboard', 'dashboard'); ?>" href="#">
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Dependencies

- Bootstrap 5.1.3
- Font Awesome 6.0.0
- jQuery (optional, cho một số tính năng nâng cao)

## Troubleshooting

### Sidebar không hiển thị

- Kiểm tra session đã được start
- Đảm bảo user đã đăng nhập
- Kiểm tra role trong session

### Layout bị vỡ trên mobile

- Kiểm tra viewport meta tag
- Đảm bảo CSS responsive được load
- Test trên thiết bị thật

### Menu không hoạt động

- Kiểm tra Bootstrap JS đã được load
- Đảm bảo không có conflict với CSS khác
- Kiểm tra console để tìm lỗi JavaScript
