<?php

require_once 'Models/ReceiptData.php';

class ReceiptController
{
    private $receiptDataModel;

    public function __construct($database)
    {
        $this->receiptDataModel = new ReceiptData($database);
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
        if ($num < 20) return $num === 10 ? 'mười' : 'mười ' . $ones[$num - 10];
        if ($num < 100) {
            $ten = floor($num / 10);
            $one = $num % 10;
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
        return $num;
    }
}
