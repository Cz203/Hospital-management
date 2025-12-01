<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Trang không tồn tại</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #e0f0ff 0, #f8f9ff 45%, #eef3ff 100%);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .notfound-card {
            max-width: 720px;
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.15);
            padding: 32px 32px 28px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .notfound-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 60%),
                radial-gradient(circle at bottom right, rgba(25, 135, 84, 0.12), transparent 55%);
            opacity: 0.9;
            pointer-events: none;
        }

        .notfound-inner {
            position: relative;
            z-index: 1;
        }

        .notfound-icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: #dc3545;
            font-size: 32px;
        }

        .notfound-code {
            font-size: 3.5rem;
            font-weight: 800;
            color: #212529;
            letter-spacing: 4px;
            margin-bottom: 0.25rem;
        }

        .notfound-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #0d6efd;
        }

        .notfound-text {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }

        .btn-primary,
        .btn-outline-secondary {
            border-radius: 999px;
            padding-inline: 20px;
        }

        .notfound-footer {
            margin-top: 1.25rem;
            color: #adb5bd;
            font-size: 0.83rem;
        }
    </style>
</head>

<body>
    <div class="notfound-card">
        <div class="notfound-inner">
            <div class="mb-3">
                <div class="notfound-icon-wrap">
                    <i class="fas fa-location-dot-slash"></i>
                </div>
                <div class="notfound-code">404</div>
                <div class="notfound-title">Trang bạn yêu cầu không tồn tại</div>
                <p class="notfound-text">
                    Có thể đường dẫn đã bị thay đổi, trang đã bị xóa, hoặc bạn gõ sai địa chỉ.
                </p>
            </div>

            <div class="d-flex justify-content-center gap-2 mb-2">
                <a href="./home" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </button>
            </div>

            <div class="notfound-footer">
                <p class="mb-1">Nếu bạn nghĩ đây là lỗi hệ thống, vui lòng liên hệ quản trị viên.</p>
                <p class="mb-0">Mã lỗi: <code>404_NOT_FOUND</code></p>
            </div>
        </div>
    </div>
</body>

</html>