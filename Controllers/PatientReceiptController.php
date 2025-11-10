<?php

require_once 'Models/BienLai.php';

class PatientReceiptController
{
    private $bienLaiModel;

    public function __construct()
    {
        $this->bienLaiModel = new BienLai();
    }

    private function requirePatientAuth()
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'patient') {
            header("Location: ./login");
            exit();
        }
    }

    /**
     * Trang danh sách biên lai cho bệnh nhân
     */
    public function patientIndex()
    {
        $this->requirePatientAuth();
        include 'Views/patient/receipts.php';
    }

    /**
     * API: Lấy danh sách biên lai cho bệnh nhân (phân trang, lọc ngày)
     */
    public function getPatientReceipts()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $this->requirePatientAuth();

            $patientId = $_SESSION['user_id'] ?? null;
            if (empty($patientId)) {
                echo json_encode(['success' => false, 'message' => 'Không xác định được bệnh nhân']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $page = max(1, (int)($input['page'] ?? 1));
            $limit = max(1, min(100, (int)($input['limit'] ?? 15)));
            $selectedDate = trim($input['selected_date'] ?? '');
            $receiptCode = trim($input['receipt_code'] ?? '');
            $offset = ($page - 1) * $limit;

            $receipts = $this->bienLaiModel->getReceiptsByPatient($patientId, $limit, $offset, $selectedDate ?: null, $receiptCode ?: null);
            $total = $this->bienLaiModel->countReceiptsByPatient($patientId, $selectedDate ?: null, $receiptCode ?: null);
            $totalPages = max(1, (int)ceil($total / $limit));

            echo json_encode([
                'success' => true,
                'data' => $receipts,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages
            ]);
        } catch (Exception $e) {
            error_log('PatientReceiptController getPatientReceipts error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Render chi tiết biên lai cho modal bệnh nhân
     */
    public function renderPatientReceiptDetail()
    {
        $this->requirePatientAuth();

        $receiptId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($receiptId <= 0) {
            echo '<div class="alert alert-danger">ID biên lai không hợp lệ.</div>';
            return;
        }

        $patientId = $_SESSION['user_id'] ?? null;
        if (!$patientId) {
            echo '<div class="alert alert-danger">Không xác định được bệnh nhân.</div>';
            return;
        }

        $receipt = $this->bienLaiModel->getById($receiptId);
        if (!$receipt) {
            echo '<div class="alert alert-danger">Không tìm thấy biên lai.</div>';
            return;
        }

        $receiptPatientId = $receipt['benh_nhan_id'] ?? ($receipt['benh_nhan_id_thuc_te'] ?? null);
        if ((int)$receiptPatientId !== (int)$patientId) {
            echo '<div class="alert alert-danger">Bạn không có quyền xem biên lai này.</div>';
            return;
        }

        $receiptDetails = $this->bienLaiModel->getDetails($receiptId);
        include 'Views/patient/partials/receipt_detail.php';
    }
}
