<?php
require_once 'config/vonage.php';

// Sử dụng Vonage SDK
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class SMSController
{
    private $vonageConfig;
    private $client;

    public function __construct()
    {
        $this->vonageConfig = new VonageConfig();

        // Khởi tạo Vonage client
        if ($this->vonageConfig->isConfigured()) {
            try {
                $this->client = new Client(new Basic(
                    $this->vonageConfig->getApiKey(),
                    $this->vonageConfig->getApiSecret()
                ));
            } catch (\Exception $e) {
                error_log("Vonage Client initialization failed: " . $e->getMessage());
                $this->client = null;
            }
        }
    }

    // Tạo mã OTP ngẫu nhiên 6 số
    private function generateOTP()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Chuẩn hóa số điện thoại
    public function normalizePhoneNumber($phone_number)
    {
        // Loại bỏ tất cả ký tự không phải số
        $phone = preg_replace('/\D/', '', $phone_number);

        // Nếu bắt đầu bằng 0, bỏ số 0 đầu
        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }

        // Nếu chưa có 84, thêm vào
        if (strpos($phone, '84') !== 0) {
            $phone = '84' . $phone;
        }

        return $phone;
    }

    private function sendSMS($to_number, $message)
    {
        try {
            // Kiểm tra client đã được khởi tạo chưa
            if (!$this->client) {
                return [
                    'success' => false,
                    'message' => 'Vonage client chưa được khởi tạo. Vui lòng kiểm tra cấu hình API.'
                ];
            }

            // Sử dụng số điện thoại mặc định hoặc để trống
            $from_number = $this->vonageConfig->getFromNumber();
            if (empty($from_number) || $from_number === 'your_from_number') {
                $from_number = 'Vonage'; // Sử dụng tên mặc định
            }

            $response = $this->client->sms()->send(
                new SMS($to_number, $from_number, $message)
            );

            $message = $response->current();

            if ($message->getStatus() == 0) {
                return ['success' => true, 'message' => 'SMS sent successfully'];
            } else {
                return [
                    'success' => false,
                    'message' => 'SMS sending failed: ' . $message->getStatus()
                ];
            }
        } catch (\Exception $e) {
            error_log("Vonage SMS Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    // Gửi OTP qua SMS
    public function sendOTP($phone_number, $check_database = false, $for_registration = false)
    {
        // Chuẩn hóa số điện thoại
        $normalized_phone = $this->normalizePhoneNumber($phone_number);

        // Kiểm tra số điện thoại trong database nếu được yêu cầu
        if ($check_database) {
            require_once 'Models/Patient.php';
            require_once 'Models/Doctor.php';
            require_once 'Models/Admin.php';

            $patient = new Patient();
            $doctor = new Doctor();
            $admin = new Admin();

            // Kiểm tra trong tất cả các bảng
            $exists_in_patient = $patient->phoneExists($normalized_phone);
            $exists_in_doctor = $doctor->phoneExists($normalized_phone);
            $exists_in_admin = $admin->phoneExists($normalized_phone);

            $exists = $exists_in_patient || $exists_in_doctor || $exists_in_admin;

            if ($for_registration) {
                // Cho đăng ký: số điện thoại KHÔNG được tồn tại
                if ($exists) {
                    return [
                        'success' => false,
                        'message' => 'Số điện thoại đã được sử dụng. Vui lòng chọn số khác.'
                    ];
                }
                // Nếu số điện thoại chưa tồn tại, tiếp tục tạo OTP
            } else {
                // Cho quên mật khẩu: số điện thoại PHẢI tồn tại
                if (!$exists) {
                    return [
                        'success' => false,
                        'message' => 'Số điện thoại chưa được đăng ký.'
                    ];
                }
                // Nếu số điện thoại tồn tại, tiếp tục tạo OTP
            }
        }

        // Kiểm tra cấu hình Vonage
        if (!$this->vonageConfig->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS service chưa được cấu hình. Vui lòng liên hệ admin.'
            ];
        }

        // Tạo mã OTP
        $otp_code = $this->generateOTP();

        // Thời gian hết hạn (5 phút)
        $expires_at = time() + (5 * 60); // 5 phút

        // Lưu OTP vào session với số đã chuẩn hóa
        $_SESSION['otp_data'] = [
            'phone_number' => $normalized_phone,
            'otp_code' => $otp_code,
            'expires_at' => $expires_at,
            'attempts' => 0 // Số lần thử
        ];

        // Kiểm tra balance trước khi gửi SMS
        try {
            if ($this->client) {
                $balance = $this->client->account()->getBalance();
                $balanceAmount = $balance->getBalance();

                if ($balanceAmount <= 0) {
                    // Balance âm hoặc 0, trả về OTP để test
                    return [
                        'success' => true,
                        'message' => 'Mã OTP đã được tạo (TEST MODE - Balance: €' . number_format($balanceAmount, 2) . '). Mã OTP: ' . $otp_code,
                        'otp_code' => $otp_code, // Chỉ trả về trong test mode
                        'test_mode' => true
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log("Error checking Vonage balance: " . $e->getMessage());
            // Nếu không check được balance, vẫn thử gửi SMS
        }

        // Gửi SMS qua Vonage SDK
        $message = "Ma OTP cua ban la: $otp_code. Ma co hieu luc trong 5 phut. Khong chia se ma nay voi ai.";

        $result = $this->sendSMS($normalized_phone, $message);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Mã OTP đã được gửi đến số điện thoại của bạn.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể gửi SMS. Vui lòng thử lại sau.'
            ];
        }
    }

    // Gửi SMS qua Vonage SDK


    // Xác thực OTP
    public function verifyOTP($phone_number, $otp_code)
    {
        // Kiểm tra session có OTP data không
        if (!isset($_SESSION['otp_data'])) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy mã OTP. Vui lòng gửi lại OTP.'
            ];
        }

        $otp_data = $_SESSION['otp_data'];

        // Chuẩn hóa số điện thoại để so sánh
        $normalized_phone = $this->normalizePhoneNumber($phone_number);

        // Kiểm tra số điện thoại có khớp không
        if ($otp_data['phone_number'] !== $normalized_phone) {
            return [
                'success' => false,
                'message' => 'Số điện thoại không khớp với OTP đã gửi.'
            ];
        }

        // Kiểm tra thời gian hết hạn
        if (time() > $otp_data['expires_at']) {
            // Xóa OTP đã hết hạn
            unset($_SESSION['otp_data']);
            return [
                'success' => false,
                'message' => 'Mã OTP đã hết hạn. Vui lòng gửi lại OTP.'
            ];
        }

        // Kiểm tra số lần thử (tối đa 5 lần)
        if ($otp_data['attempts'] >= 5) {
            // Xóa OTP sau khi thử quá nhiều lần
            unset($_SESSION['otp_data']);
            return [
                'success' => false,
                'message' => 'Bạn đã thử quá nhiều lần. Vui lòng gửi lại OTP.'
            ];
        }

        // Tăng số lần thử
        $_SESSION['otp_data']['attempts']++;

        // Kiểm tra OTP code
        if ($otp_data['otp_code'] === $otp_code) {
            // Xác thực thành công, đánh dấu đã verify nhưng không xóa OTP
            $_SESSION['otp_data']['verified'] = true;
            return [
                'success' => true,
                'message' => 'Mã OTP hợp lệ.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Mã OTP không đúng. Còn ' . (5 - $_SESSION['otp_data']['attempts']) . ' lần thử.'
            ];
        }
    }

    // Xóa OTP sau khi đăng ký thành công
    public function clearOTPAfterRegistration()
    {
        if (isset($_SESSION['otp_data'])) {
            unset($_SESSION['otp_data']);
        }
    }
}