<?php

require_once 'Models/ReceiptData.php';
require_once 'Models/BienLai.php';

class ReceiptController
{
    private $receiptDataModel;
    private $bienLaiModel;

    public function __construct($database)
    {
        $this->receiptDataModel = new ReceiptData($database);
        $this->bienLaiModel = new BienLai();
    }

    /**
     * Lấy dữ liệu yêu cầu cho biên lai
     */
    public function getReceiptData()
    {
        try {
            $examId = $_GET['exam_id'] ?? '';
            
            if (empty($examId)) {
                echo json_encode(['success' => false, 'message' => 'ID phiếu khám không hợp lệ']);
                return;
            }

            $requests = $this->receiptDataModel->getAllRequests($examId);
            $medications = $this->receiptDataModel->getMedications($examId);
            
            echo json_encode([
                'success' => true,
                'data' => $requests,
                'medications' => $medications
            ]);
            
        } catch (Exception $e) {
            error_log('ReceiptController getReceiptData error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Lấy mã biên lai theo ID phiếu khám
     */
    public function getReceiptCode()
    {
        try {
            $examId = $_GET['exam_id'] ?? '';
            
            if (empty($examId)) {
                echo json_encode(['success' => false, 'message' => 'ID phiếu khám không hợp lệ']);
                return;
            }

            $bienLai = $this->bienLaiModel->getByExamId($examId);
            
            if ($bienLai) {
                echo json_encode([
                    'success' => true,
                    'ma_bien_lai' => $bienLai['ma_bien_lai']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Chưa có biên lai'
                ]);
            }
            
        } catch (Exception $e) {
            error_log('ReceiptController getReceiptCode error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Tính toán thanh toán dựa trên BHYT
     */
    public function calculatePayment($requests, $hasBHYT)
    {
        $totalBasePrice = 0;
        $totalBhytAmount = 0;
        $totalPatientAmount = 0;
        
        $calculatedRequests = [];
        
        foreach ($requests as $request) {
            $basePrice = (int)$request['price'];
            $totalBasePrice += $basePrice;
            
            $bhytAmount = 0;
            $patientAmount = $basePrice;
            
            if ($hasBHYT) {
                // Có BHYT: giảm 80%
                $bhytAmount = round($basePrice * 0.8);
                $patientAmount = $basePrice - $bhytAmount;
            }
            
            $totalBhytAmount += $bhytAmount;
            $totalPatientAmount += $patientAmount;
            
            $calculatedRequests[] = [
                'type' => $request['type'],
                'content' => $request['content'],
                'base_price' => $basePrice,
                'bhyt_amount' => $bhytAmount,
                'patient_amount' => $patientAmount
            ];
        }
        
        return [
            'requests' => $calculatedRequests,
            'total_base_price' => $totalBasePrice,
            'total_bhyt_amount' => $totalBhytAmount,
            'total_patient_amount' => $totalPatientAmount
        ];
    }

    /**
     * Chuyển đổi số thành chữ
     */
    public function numberToWords($num)
    {
        $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
        
        if ($num === 0) return 'không';
        if ($num < 10) return $ones[$num];
        if ($num < 20) {
            if ($num === 10) return 'mười';
            if ($num === 11) return 'mười một';
            if ($num === 12) return 'mười hai';
            if ($num === 13) return 'mười ba';
            if ($num === 14) return 'mười bốn';
            if ($num === 15) return 'mười lăm';
            if ($num === 16) return 'mười sáu';
            if ($num === 17) return 'mười bảy';
            if ($num === 18) return 'mười tám';
            if ($num === 19) return 'mười chín';
            return 'mười ' . ($ones[$num - 10] ?: '');
        }
        if ($num < 100) {
            $ten = floor($num / 10);
            $one = $num % 10;
            if ($ten === 1) {
                return 'mười' . ($one > 0 ? ' ' . $ones[$one] : '');
            }
            return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
        }
        if ($num < 1000) {
            $hundred = floor($num / 100);
            $remainder = $num % 100;
            return $ones[$hundred] . ' trăm' . ($remainder > 0 ? ' ' . $this->numberToWords($remainder) : '');
        }
        if ($num < 1000000) {
            $thousand = floor($num / 1000);
            $remainder = $num % 1000;
            return $this->numberToWords($thousand) . ' ngàn' . ($remainder > 0 ? ' ' . $this->numberToWords($remainder) : '');
        }
        if ($num < 1000000000) {
            $million = floor($num / 1000000);
            $remainder = $num % 1000000;
            $millionText = $this->numberToWords($million);
            return $millionText . ' triệu' . ($remainder > 0 ? ' ' . $this->numberToWords($remainder) : '');
        }
        return $num;
    }

    /**
     * In biên lai viện phí
     */
    public function printReceiptForm()
    {
        try {
            $receiptId = $_GET['id'] ?? '';
            if (empty($receiptId)) {
                echo "ID biên lai không hợp lệ";
                return;
            }

            // Include the print template - let template handle the logic
            include 'Views/doctor/print_receipt_form.php';
            
        } catch (Exception $e) {
            error_log('ReceiptController printReceiptForm error: ' . $e->getMessage());
            echo "Lỗi hệ thống khi in biên lai";
        }
    }

    /**
     * Lưu biên lai viện phí
     */
    public function saveReceipt()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }
            
            // Validate required fields
            $requiredFields = ['id_phieu_kham_benh', 'tong_tien_co_ban', 'tong_quy_bhyt', 'tong_nguoi_benh'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field])) {
                    echo json_encode(['success' => false, 'message' => "Thiếu trường bắt buộc: $field"]);
                    return;
                }
            }
            
            // Kiểm tra biên lai đã tồn tại chưa
            $existingReceipt = $this->bienLaiModel->getByExamId($input['id_phieu_kham_benh']);
            $isUpdate = $existingReceipt !== false;
            
            if ($isUpdate) {
                // Cập nhật biên lai đã tồn tại
                $receiptData = [
                    'id' => $existingReceipt['id'],
                    'tong_tien_co_ban' => $input['tong_tien_co_ban'],
                    'tong_quy_bhyt' => $input['tong_quy_bhyt'],
                    'tong_nguoi_benh' => $input['tong_nguoi_benh'],
                    'nguoi_lap' => $_SESSION['user_name'] ?? 'Bác sĩ',
                    'ghi_chu' => $input['ghi_chu'] ?? '',
                    'chi_tiet' => $input['chi_tiet'] ?? []
                ];
                
                $bienLaiId = $this->bienLaiModel->updateReceipt($receiptData);
                $maBienLai = $existingReceipt['ma_bien_lai'];
            } else {
                // Tạo biên lai mới
                $maBienLai = 'BL' . date('Ymd') . rand(1000, 9999);
                
                $receiptData = [
                    'ma_bien_lai' => $maBienLai,
                    'id_phieu_kham_benh' => $input['id_phieu_kham_benh'],
                    'tong_tien_co_ban' => $input['tong_tien_co_ban'],
                    'tong_quy_bhyt' => $input['tong_quy_bhyt'],
                    'tong_nguoi_benh' => $input['tong_nguoi_benh'],
                    'ngay_lap' => date('Y-m-d H:i:s'),
                    'nguoi_lap' => $_SESSION['user_name'] ?? 'Bác sĩ',
                    'trang_thai' => 'Chưa thanh toán',
                    'ghi_chu' => $input['ghi_chu'] ?? '',
                    'chi_tiet' => $input['chi_tiet'] ?? []
                ];
                
                $bienLaiId = $this->bienLaiModel->saveReceipt($receiptData);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $isUpdate ? 'Cập nhật biên lai thành công' : 'Lưu biên lai thành công',
                'bien_lai_id' => $bienLaiId,
                'ma_bien_lai' => $maBienLai,
                'is_update' => $isUpdate
            ]);
            
        } catch (Exception $e) {
            error_log('Error saving receipt: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi lưu biên lai']);
        }
    }
}
