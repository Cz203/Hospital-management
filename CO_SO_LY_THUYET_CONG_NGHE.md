# CƠ cỞ LÝ THUYẾT CÁC CÔNG NGHỆ cỬ DỤNG TRONG HỆ THỐNG QUẢN LÝ PHÒNG KHÁM

## Theoretical Foundation of Technologies Used in Clinic Management System

---

## 📋 MỤC LỤC / TABLE OF CONTENTS

1. [Kiến trúc tổng quan / Overall Architecture](#1-kiến-trúc-tổng-quan--overall-architecture)
2. [PHP - Backend Framework](#2-php---backend-framework)
3. [MySQL Database](#3-mysql-database)
4. [Javarript & jQuery](#4-javascript--jquery)
5. [Node.js & Socket.IO](#5-nodejs--socketio)
6. [Composer - PHP Dependency Manager](#6-composer---php-dependency-manager)
7. [NPM - Node Package Manager](#7-npm---node-package-manager)
8. [Các thư viện và dịch vụ bên thứ ba](#8-các-thư-viện-và-dịch-vụ-bên-thứ-ba)
9. [Bootstrap CSS Framework](#9-bootstrap-css-framework)
10. [Mô hình MVC](#10-mô-hình-mvc)

---

## 1. KIẾN TRÚC TỔNG QUAN / OVERALL ARCHITECTURE

### 1.1. Kiến trúc hệ thống / System Architecture

Hệ thống quản lý phòng khám được xây dựng theo mô hình **3-tier architecture** (Kiến trúc 3 tầng):

```
┌─────────────────────────────────────────┐
│   PRESENTATION LAYER (Frontend)        │
│   - HTML/CSS/JavaScript                 │
│   - Bootstrap UI Framework              │
│   - jQuery & AJAX                       │
└─────────────────────────────────────────┘
                  ↕
┌─────────────────────────────────────────┐
│   APPLICATION LAYER (Backend)          │
│   - PHP (MVC Pattern)                  │
│   - Controllers                         │
│   - Models                              │
│   - Services                           │
│   - Node.js Socket Server              │
└─────────────────────────────────────────┘
                  ↕
┌─────────────────────────────────────────┐
│   DATA LAYER (Database)                 │
│   - MySQL Database                      │
│   - PDO (PHP Data Objects)              │
└─────────────────────────────────────────┘
```

### 1.2. Công nghệ chính / Main Technologies

- **Backend**: PHP (Procedural + OOP)
- **Database**: MySQL với PDO
- **Frontend**: HTML5, CSS3, JavaScript (ES5/ES6), jQuery
- **Real-time**: Node.js + Socket.IO
- **Payment**: VNPay Integration
- **SMS**: Vonage API
- **Email**: PHPMailer
- **Authentication**: JWT (JSON Web Tokens)
- **HTTP Client**: Guzzle

---

## 2. PHP - BACKEND FRAMEWORK

### 2.1. Giới thiệu / Introduction

**PHP (Hypertext Preprocessor)** là ngôn ngữ lập trình server-side được sử dụng để xây dựng backend của hệ thống.

### 2.2. Đặc điểm trong dự án / Features in Project

#### 2.2.1. Kiến trúc MVC (Model-View-Controller)

Dự án sử dụng mô hình MVC để tổ chức code:

- **Models** (`Models/`): Đại diện cho dữ liệu và logic nghiệp vụ

  - `Patient.php`, `Doctor.php`, `Appointment.php`, `MedicalRecord.php`, etc.
  - Tương tác trực tiếp với database thông qua PDO

- **Views** (`Views/`): Giao diện người dùng

  - Template PHP với HTML/CSS/JavaScript
  - Tách biệt logic hiển thị khỏi business logic

- **Controllers** (`Controllers/`): Xử lý request và điều phối
  - `AuthController.php`: Xử lý đăng nhập/đăng ký
  - `DoctorController.php`: Logic nghiệp vụ bác sĩ
  - `PatientController.php`: Logic nghiệp vụ bệnh nhân
  - `ReceptionController.php`: Logic nghiệp vụ lễ tân
  - `AdminController.php`: Logic quản trị

#### 2.2.2. Routing System

Hệ thống routing đơn giản dựa trên query parameter `action`:

```php
// index.php
$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'login':
        $auth->loginPatient();
        break;
    case 'doctor_dashboard':
        $auth->requireAuth('doctor');
        include 'Views/doctor/dashboard.php';
        break;
    // ... more routes
}
```

#### 2.2.3. Session Management

- Sử dụng PHP sessions để quản lý authentication
- `session_start()` được gọi trong `index.php`
- Session data lưu trữ: `user_id`, `user_role`, `user_name`, `last_activity`

#### 2.2.4. Object-Oriented Programming

- Classes và inheritance: `Patient extends User`, `Doctor extends User`
- Encapsulation với private/protected/public methods
- Dependency injection trong Services

### 2.3. Các tính năng PHP được sử dụng / PHP Features Used

- **PDO (PHP Data Objects)**: Kết nối và truy vấn database an toàn
- **Namespaces**: Tổ chức code (PSR-4 autoloading)
- **Error Handling**: Try-catch blocks, exception handling
- **File Upload**: Xử lý upload hình ảnh (X-ray, siêu âm)
- **Date/Time**: `date_default_timezone_set('Asia/Ho_Chi_Minh')`

---

## 3. MYSQL DATABASE

### 3.1. Giới thiệu / Introduction

**MySQL** là hệ quản trị cơ sở dữ liệu quan hệ (RDBMS) được sử dụng để lưu trữ dữ liệu của hệ thống.

### 3.2. Kết nối Database / Database Connection

#### 3.2.1. PDO (PHP Data Objects)

PDO cung cấp interface thống nhất để truy cập database:

```php
// config/database.php
$conn = new PDO(
    "mysql:host=$host;dbname=$db_name",
    $username,
    $password
);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

**Ưu điểm của PDO:**

- Prepared statements (chống SQL injection)
- Hỗ trợ nhiều database (MySQL, PostgreSQL, SQLite)
- Error handling tốt hơn
- Transaction support

#### 3.2.2. Prepared Statements

Ví dụ sử dụng prepared statements:

```php
$stmt = $db->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->bindValue(':id', $patientId, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
```

### 3.3. Cấu trúc Database / Database Structure

Các bảng chính trong hệ thống:

- `users`: Thông tin người dùng (bệnh nhân, bác sĩ, admin, lễ tân)
- `doctors`: Thông tin bác sĩ
- `patients`: Thông tin bệnh nhân
- `appointments`: Lịch hẹn khám
- `medical_records`: Hồ sơ bệnh án
- `prescriptions`: Đơn thuốc
- `lab_tests`: Xét nghiệm
- `xray_requests`: Yêu cầu chụp X-Quang
- `ultrasound_requests`: Yêu cầu siêu âm
- `receipts`: Biên lai viện phí
- `notifications`: Thông báo
- `schedules`: Lịch làm việc

### 3.4. Timezone Configuration

Hệ thống cấu hình timezone cho MySQL:

```php
// Set MySQL timezone về giờ Việt Nam
$conn->exec("SET time_zone = '+07:00'");
date_default_timezone_set('Asia/Ho_Chi_Minh');
```

---

## 4. JAVASCRIPT & JQUERY

### 4.1. JavaScript ES5/ES6

JavaScript được sử dụng cho:

- **DOM Manipulation**: Thao tác với HTML elements
- **Event Handling**: Xử lý sự kiện người dùng
- **AJAX Requests**: Giao tiếp với server không đồng bộ
- **Form Validation**: Kiểm tra dữ liệu trước khi submit
- **Dynamic Content**: Cập nhật nội dung không cần reload

### 4.2. jQuery Library

jQuery đơn giản hóa JavaScript và cung cấp:

#### 4.2.1. DOM Selection & Manipulation

```javascript
// Select elements
$("#patient-form").hide();
$(".appointment-item").addClass("active");

// Manipulate content
$("#patient-name").text("Nguyễn Văn A");
$("#appointment-list").html("<div>New content</div>");
```

#### 4.2.2. AJAX Requests

```javascript
// Tạo server HTTP
const http = require("http");
const server = http.createServer();

const { Server } = require("socket.io");
const io = new Server(server);

io.on("connection", (socket) => {
  console.log("Client connected:", socket.id);

  socket.on("chat", (msg) => {
    console.log("Client says:", msg);
    io.emit("chat", msg);
  });
});

// Chạy server
server.listen(3000, () => console.log("Socket.IO running on port 3000"));

$.ajax({
  url: "index.php?action=get_appointments",
  method: "POST",
  data: { patientId: 123 },
  dataType: "json",
  success: function (response) {
    // Handle response
  },
  error: function (xhr, status, error) {
    // Handle error
  },
});
```

#### 4.2.3. Event Handling

```javascript
$("#submit-btn").on("click", function (e) {
  e.preventDefault();
  // Handle click event
});

$("#form-input").on("change", function () {
  // Handle input change
});
```

### 4.3. Các file JavaScript trong dự án / JavaScript Files in Project

- `appointment.js`: Xử lý đặt lịch hẹn
- `examination.js`: Form khám bệnh
- `prescription.js`: Quản lý đơn thuốc
- `payment.js`: Xử lý thanh toán
- `socket-client.js`: Kết nối Socket.IO
- `schedule_management.js`: Quản lý lịch làm việc
- `reception_patient_create.js`: Tạo bệnh nhân mới (lễ tân)

### 4.4. Socket.IO Client

Kết nối với Node.js Socket server để nhận thông báo real-time:

```javascript
const socket = io("http://localhost:3001", {
  auth: {
    token: jwtToken,
  },
});

socket.on("appointment_notification", (data) => {
  // Handle notification
});
```

---

## 5. NODE.JS & SOCKET.IO

### 5.1. Node.js

**Node.js** là JavaScript runtime environment chạy trên server, được sử dụng để xây dựng Socket.IO server cho real-time communication.

#### 5.1.1. Đặc điểm / Features

- **Event-driven**: Xử lý sự kiện không đồng bộ
- **Non-blocking I/O**: Hiệu suất cao với nhiều kết nối đồng thời
- **NPM Ecosystem**: Quản lý packages dễ dàng

### 5.2. Socket.IO

**Socket.IO** là thư viện cho phép giao tiếp real-time giữa client và server sử dụng WebSocket.

#### 5.2.1. Kiến trúc / Architecture

```
┌─────────────┐         WebSocket         ┌─────────────┐
│   Client    │ ←──────────────────────→ │   Server    │
│  (Browser)  │                          │  (Node.js)  │
└─────────────┘                          └─────────────┘
```

#### 5.2.2. Các tính năng trong dự án / Features in Project

**1. Authentication với JWT:**

```javascript
// socket_server.js
io.use((socket, next) => {
  const token = socket.handshake.auth.token;
  const payload = jwt.verify(token, SOCKET_JWT_SECRET);
  socket.userId = payload.sub;
  socket.role = payload.role;
  next();
});
```

**2. Room Management:**

```javascript
// Join rooms based on role
socket.join(`doctor_${doctorId}`);
socket.join("all_doctors");
socket.join("all_receptionists");
```

**3. Event Broadcasting:**

```javascript
// Emit to specific user
io.to(`doctor_${doctorId}`).emit("appointment_notification", data);

// Broadcast to all users in room
io.to("all_receptionists").emit("queue_update", data);
```

**4. HTTP Endpoint cho PHP:**

```javascript
// PHP có thể gửi notification qua HTTP POST
app.post("/emit", (req, res) => {
  // Validate API key
  // Emit event to connected clients
});
```

#### 5.2.3. Các sự kiện được xử lý / Events Handled

- `new_appointment`: Thông báo lịch hẹn mới
- `appointment_cancelled`: Hủy lịch hẹn
- `appointment_status_changed`: Thay đổi trạng thái lịch hẹn
- `queue_update`: Cập nhật hàng đợi
- `appointment_update`: Cập nhật lịch hẹn

### 5.3. Express.js Framework

Express.js được sử dụng để tạo HTTP server cho Socket.IO:

```javascript
const express = require("express");
const app = express();
const server = http.createServer(app);

// Middleware
app.use(express.json());

// Routes
app.post("/emit", (req, res) => {
  // Handle notification from PHP
});
```

---

## 6. COMPOSER - PHP DEPENDENCY MANAGER

### 6.1. Giới thiệu / Introduction

**Composer** là công cụ quản lý dependencies cho PHP, tương tự như NPM cho Node.js.

### 6.2. File composer.json

```json
{
  "name": "viet/clinic_management",
  "require": {
    "vonage/client": "^4.2",
    "firebase/php-jwt": "^6.11",
    "guzzlehttp/guzzle": "^7.9",
    "phpmailer/phpmailer": "^6.10",
    "vlucas/phpdotenv": "5.6"
  }
}
```

### 6.3. Autoloading (PSR-4)

```json
{
  "autoload": {
    "psr-4": {
      "Viet\\clinic_management\\": "src/"
    }
  }
}
```

Tự động load classes khi sử dụng:

```php
require_once 'vendor/autoload.php';
// Classes được tự động load
```

### 6.4. Các packages được sử dụng / Packages Used

- **vonage/client**: Gửi SMS qua Vonage API
- **firebase/php-jwt**: Tạo và verify JWT tokens
- **guzzlehttp/guzzle**: HTTP client để gọi API
- **phpmailer/phpmailer**: Gửi email
- **vlucas/phpdotenv**: Load environment variables từ file .env

---

## 7. NPM - NODE PACKAGE MANAGER

### 7.1. Giới thiệu / Introduction

**NPM (Node Package Manager)** quản lý dependencies cho Node.js project.

### 7.2. File package.json

```json
{
  "name": "hospital-management-socket",
  "dependencies": {
    "express": "^4.21.2",
    "socket.io": "^4.8.1",
    "jsonwebtoken": "^9.0.0",
    "dotenv": "^16.6.1",
    "ajv": "^8.12.0"
  }
}
```

### 7.3. Các packages được sử dụng / Packages Used

- **express**: Web framework cho Node.js
- **socket.io**: Real-time bidirectional communication
- **jsonwebtoken**: Tạo và verify JWT tokens
- **dotenv**: Load environment variables
- **ajv**: JSON schema validator cho payload validation

---

## 8. CÁC THƯ VIỆN VÀ DỊCH VỤ BÊN THỨ BA / THIRD-PARTY LIBRARIES & SERVICES

### 8.1. Vonage API (SMS Service)

**Vonage** (trước đây là Nexmo) cung cấp dịch vụ SMS để gửi OTP và thông báo.

#### 8.1.1. Cách hoạt động / How it Works

```php
// Services/SMSController.php
use Vonage\Client;
use Vonage\SMS\Message\SMS;

$client = new Client(new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret));
$response = $client->sms()->send(
    new SMS($to, $from, $message)
);


function sendOTP($phone, $otp) {
    $basic  = new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET);
    $client = new \Vonage\Client($basic);

    $response = $client->sms()->send(
        new \Vonage\SMS\Message\SMS($phone, "Clinic", "Ma OTP cua ban: $otp")
    );

    $message = $response->current();

    if ($message->getStatus() == 0) {
        return true;
    } else {
        return false;
    }
}
```

#### 8.1.2. Use Cases trong dự án

- Gửi OTP khi đăng ký/đăng nhập
- Gửi thông báo xác nhận lịch hẹn
- Gửi thông báo hủy lịch hẹn

### 8.2. VNPay (Payment Gateway)

**VNPay** là cổng thanh toán trực tuyến tại Việt Nam.

#### 8.2.1. Quy trình thanh toán / Payment Flow

```
1. User chọn thanh toán
2. Tạo payment URL với VNPay
3. Redirect user đến VNPay
4. User thanh toán trên VNPay
5. VNPay redirect về return URL
6. Verify payment và cập nhật database
```

#### 8.2.2. Hash Security

VNPay sử dụng HMAC SHA512 để bảo mật:

```php
// Services/VNPayService.php
$vnp_HashSecret = $config['hash_secret'];
$vnp_SecureHash = hash_hmac('sha512', $queryString, $vnp_HashSecret);
```

### 8.3. PHPMailer (Email Service)

**PHPMailer** là thư viện PHP để gửi email.

#### 8.3.1. Cấu hình / Configuration

```php
// Services/MailService.php
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = $email;
$mail->Password = $password;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

### 8.4. Firebase JWT (JSON Web Tokens)

**JWT** được sử dụng cho authentication trong Socket.IO.

#### 8.4.1. Tạo Token (PHP)

```php
use Firebase\JWT\JWT;

$payload = [
    'sub' => $userId,
    'role' => $role,
    'name' => $userName,
    'iat' => time(),
    'exp' => time() + 3600
];

$token = JWT::encode($payload, $secret, 'HS256');
```

#### 8.4.2. Verify Token (Node.js)

```javascript
const jwt = require("jsonwebtoken");

const payload = jwt.verify(token, SOCKET_JWT_SECRET);
socket.userId = payload.sub;
socket.role = payload.role;
```

### 8.5. Guzzle HTTP Client

**Guzzle** là HTTP client để gọi API bên ngoài.

```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->request('POST', 'https://api.example.com/endpoint', [
    'json' => ['data' => 'value']
]);
```

---

## 9. BOOTSTRAP CSS FRAMEWORK

### 9.1. Giới thiệu / Introduction

**Bootstrap** là CSS framework phổ biến để xây dựng responsive web applications.

### 9.2. Tính năng được sử dụng / Features Used

- **Grid System**: Responsive layout với 12 columns
- **Components**: Buttons, forms, modals, cards, tables
- **Utilities**: Spacing, colors, typography
- **JavaScript Plugins**: Modal, dropdown, tooltip

### 9.3. Responsive Design

Bootstrap giúp website hiển thị tốt trên mọi thiết bị:

```html
<div class="container">
  <div class="row">
    <div class="col-md-6 col-sm-12">
      <!-- Content -->
    </div>
  </div>
</div>
```

---

## 10. MÔ HÌNH MVC / MVC PATTERN

### 10.1. Model (Mô hình dữ liệu)

**Models** đại diện cho dữ liệu và business logic:

```php
// Models/Patient.php
class Patient extends User
{
    public function findByPhone($phone) {
        // Query database
    }

    public function create($data) {
        // Insert into database
    }
}
```

### 10.2. View (Giao diệ8n)

**Viewc** là template hiể8 thị dữ liệu:

```php
// Views/patient/dashboard.php
<?php include 'layouts/header.php'; ?>
<div class="container">
    <h1>Dashboard Bệnh nhân</h1>
    <!-- HTML content -->
</div>
<?php include 'layouts/footer.php'; ?>
```

### 10.3. Controller (Điều khiển)

**Controllers** xử lý request và điều phối giữa Model và View:

```php
// Controllers/PatientController.php
class PatientController
{
    public function dashboard() {
        $auth->requireAuth('patient');
        $patientModel = new Patient();
        $data = $patientModel->getPatientData($_SESSION['user_id']);
        include 'Views/patient/dashboard.php';
    }
}
```

### 10.4. Service Layer

**Services** chứa business logic phức tạp và tích hợp với third-party APIs:

- `CCCDService.php`: Xác thực CCCD
- `MailService.php`: Gửi email
- `VNPayService.php`: Xử lý thanh toán
- `SocketService.php`: Gửi notification qua Socket.IO
- `ZoomService.php`: Tích hợp Zoom (nếu có)

---

## 11. BẢO MẬT / SECURITY

### 11.1. Authentication & Authorization

- **Session-based Auth**: PHP sessions cho web
- **JWT Tokens**: Cho Socket.IO connections
- **Role-based Access Control**: Phân quyền theo role (patient, doctor, admin, reception)

### 11.2. SQL Injection Prevention

Sử dụng Prepared Statements với PDO:

```php
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
```

### 11.3. XSS Prevention

- Erape output khi hiển thị: `htmlspecialchars()`
- Validate input từ user

### 11.4. CSRF Protection

- Session tokens
- Verify requests từ authenticated users

### 11.5. Password Hashing

Sử dụng `password_hash()` và `password_verify()`:

```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
if (password_verify($inputPassword, $hashedPassword)) {
    // Login success
}
```

---

## 12. ENVIRONMENT CONFIGURATION

### 12.1. .env File

File `.env` chứa cấu hình nhạy cảm:

```env
DB_HOST=localhost
DB_NAME=clinic-management
DB_USER=root
DB_PASS=

API_KEY=vonage_api_key
API_SECRET=vonage_api_secret

VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret

SOCKET_PORT=3001
SOCKET_JWT_SECRET=jwt_secret_key
SOCKET_API_KEY=api_key_for_socket
```

### 12.2. Config Files

- `config/database.php`: Database configuration
- `config/mail.php`: Email configuration
- `config/vnpay.php`: VNPay configuration
- `config/vonage.php`: Vonage SMS configuration
- `config/socket.php`: Socket.IO configuration
- `config/security.php`: Security settings

---

## 13. FILE UPLOAD & STORAGE

### 13.1. Upload Directory Structure

```
uploads/
├── attendance/        # Ảnh chấm công
├── BS/               # Ảnh bác sĩ
├── face_samples/     # Mẫu khuôn mặt
├── sieuam/           # Ảnh siêu âm
└── xray/             # Ảnh X-Quang
```

### 13.2. Security Considerations

- Validate file types (chỉ cho phép image files)
- Validate file size
- Rename files để tránh conflict
- Store outside web root nếu có thể

---

## 14. REAL-TIME NOTIFICATIONS

### 14.1. Architecture

```
PHP Backend → HTTP POST → Node.js Socket Server → WebSocket → Browser Client
```

### 14.2. Flow

1. PHP xử lý business logic (ví dụ: tạo appointment)
2. PHP gọi SocketService để gửi notification
3. SocketService gửi HTTP POST đến Node.js server
4. Node.js server emit event qua Socket.IO
5. Browser client nhận notification real-time

### 14.3. Use Cases

- Thông báo lịch hẹn mới cho bác sĩ
- Xác nhận đặt lịch cho bệnh nhân
- Cập nhật trạng thái lịch hẹn
- Cập nhật hàng đợi (queue)

---

## 15. KẾT LUẬN / CONCLUSION

Hệ thống quản lý phòng khám được xây dựng với:

✅ **Backend mạnh mẽ**: PHP với mô hình MVC
✅ **Database an toàn**: MySQL với PDO prepared statements
✅ **Frontend hiện đại**: JavaScript, jQuery, Bootstrap
✅ **Real-time communication**: Node.js + Socket.IO
✅ **Tích hợp dịch vụ**: VNPay, Vonage SMS, Email
✅ **Bảo mật**: JWT, Session management, SQL injection prevention
✅ **Scalable**: Kiến trúc modular, dễ mở rộng

Hệ thống đáp ứng đầy đủ các yêu cầu của một ứng dụng quản lý phòng khám hiện đại với khả năng mở rộng và bảo mật cao.

---

## TÀI LIỆU THAM KHẢO / REFERENCES

- [PHP Official Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [jQuery API Documentation](https://api.jquery.com/)
- [Socket.IO Documentation](https://socket.io/docs/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Node.js Documentation](https://nodejs.org/docs/)
- [VNPay Integration Guide](https://sandbox.vnpayment.vn/apis/)
- [Vonage API Documentation](https://developer.vonage.com/)

---

**Tài liệu được tạo tự động dựa trên phân tích codebase của dự án Clinic Management System**
**Document generated automatically based on codebase analysis of Clinic Management System**
