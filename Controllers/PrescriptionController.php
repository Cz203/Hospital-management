<?php
require_once 'Models/Prescription.php';
require_once 'Models/Medication.php';

class PrescriptionController {
    private $prescriptionModel;
    private $medicationModel;
    
    public function __construct() {
        $this->prescriptionModel = new Prescription();
        $this->medicationModel = new Medication();
    }
    
    
    /**
     * Lưu đơn thuốc mới
     */
    public function savePrescription($data) {
        try {
            // Validate required fields
            if (empty($data['ma_don_thuoc']) || empty($data['ho_ten_benh_nhan'])) {
                return [
                    'success' => false,
                    'message' => 'Thiếu thông tin bắt buộc'
                ];
            }
            
            // Resolve doctor id from session if not provided
            $doctorIdFromSession = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            if (empty($data['ma_bac_si']) && $doctorIdFromSession) {
                $data['ma_bac_si'] = $doctorIdFromSession;
            }

            // Resolve MaBenhNhan: accept either numeric id or patient code, map code -> id
            if (!empty($data['ma_benh_nhan'])) {
                $maBn = trim($data['ma_benh_nhan']);
                if (!ctype_digit((string)$maBn)) {
                    // Look up by patient code
                    require_once 'config/database.php';
                    $pdo = (new Database())->getConnection();
                    $stmt = $pdo->prepare("SELECT id FROM benh_nhan WHERE ma_benh_nhan = ? LIMIT 1");
                    $stmt->execute([$maBn]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row && isset($row['id'])) {
                        $data['ma_benh_nhan'] = (int)$row['id'];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Không tìm thấy bệnh nhân với mã: ' . $maBn
                        ];
                    }
                } else {
                    // numeric id is ok
                    $data['ma_benh_nhan'] = (int)$maBn;
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Thiếu MaBenhNhan'
                ];
            }
            if (empty($data['ma_bac_si'])) {
                return [
                    'success' => false,
                    'message' => 'Thiếu MaBacSi'
                ];
            }

            // Validate MaBacSi exists in bac_si table; if not, try to resolve from exam/appointment id
            require_once 'config/database.php';
            $pdoCheck = (new Database())->getConnection();
            $stmtDoc = $pdoCheck->prepare("SELECT id FROM bac_si WHERE id = ? LIMIT 1");
            $stmtDoc->execute([(int)$data['ma_bac_si']]);
            $docRow = $stmtDoc->fetch(PDO::FETCH_ASSOC);
            if (!$docRow) {
                // Try resolve via phieu_kham_benh
                $resolvedDoctorId = null;
                if (!empty($data['id_phieu_kham_benh'])) {
                    // Try phieu_kham_benh
                    try {
                        $stmtPKB = $pdoCheck->prepare("SELECT bac_si_id FROM phieu_kham_benh WHERE id = ? LIMIT 1");
                        $stmtPKB->execute([(int)$data['id_phieu_kham_benh']]);
                        $rowPKB = $stmtPKB->fetch(PDO::FETCH_ASSOC);
                        if ($rowPKB && !empty($rowPKB['bac_si_id'])) {
                            $resolvedDoctorId = (int)$rowPKB['bac_si_id'];
                        }
                    } catch (Exception $e) { /* ignore */ }
                    
                    // If not found in PKB, try lich_hen
                    if (!$resolvedDoctorId) {
                        try {
                            $stmtLH = $pdoCheck->prepare("SELECT bac_si_id FROM lich_hen WHERE id = ? LIMIT 1");
                            $stmtLH->execute([(int)$data['id_phieu_kham_benh']]);
                            $rowLH = $stmtLH->fetch(PDO::FETCH_ASSOC);
                            if ($rowLH && !empty($rowLH['bac_si_id'])) {
                                $resolvedDoctorId = (int)$rowLH['bac_si_id'];
                            }
                        } catch (Exception $e) { /* ignore */ }
                    }
                }
                
                if ($resolvedDoctorId) {
                    // Validate resolved id exists
                    $stmtDoc2 = $pdoCheck->prepare("SELECT id FROM bac_si WHERE id = ? LIMIT 1");
                    $stmtDoc2->execute([$resolvedDoctorId]);
                    if ($stmtDoc2->fetch(PDO::FETCH_ASSOC)) {
                        $data['ma_bac_si'] = $resolvedDoctorId;
                    } else {
                        return [
                            'success' => false,
                            'message' => 'MaBacSi không hợp lệ (không tồn tại trong bảng bac_si)'
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'MaBacSi không hợp lệ (không tồn tại trong bảng bac_si)'
                    ];
                }
            }

            // Start transaction
            $this->prescriptionModel->beginTransaction();
            
            // Upsert prescription: if MaDonThuoc exists -> update, else insert
            $pdo = (new Database())->getConnection();
            $stmtExists = $pdo->prepare("SELECT 1 FROM don_thuoc WHERE MaDonThuoc = ? LIMIT 1");
            $stmtExists->execute([$data['ma_don_thuoc']]);
            $exists = (bool)$stmtExists->fetchColumn();

            if ($exists) {
                $this->prescriptionModel->updatePrescription([
                    'MaDonThuoc' => $data['ma_don_thuoc'],
                    'MaBenhNhan' => $data['ma_benh_nhan'] ?? null,
                    'MaBacSi' => $data['ma_bac_si'] ?? null,
                    'id_phieu_kham_benh' => $data['id_phieu_kham_benh'] ?? null,
                    'NgayKe' => $data['ngay_ke'] ?? date('Y-m-d'),
                    'ChanDoan' => $data['chan_doan'] ?? '',
                    'GhiChu' => ($data['loi_dan'] ?? ($data['ghi_chu'] ?? '')),
                    'TrangThai' => 'Chưa lấy thuốc'
                ]);
                $prescriptionId = $data['ma_don_thuoc'];
                // Replace medication details: first restore stock from old details, then delete
                $oldDetails = $this->prescriptionModel->getMedicationDetails($data['ma_don_thuoc']);
                foreach ($oldDetails as $od) {
                    if (!empty($od['MaThuoc']) && !empty($od['SoLuong'])) {
                        $this->prescriptionModel->increaseStock($od['MaThuoc'], (int)$od['SoLuong']);
                    }
                }
                $this->prescriptionModel->deleteMedicationDetails($data['ma_don_thuoc']);
            } else {
                $prescriptionId = $this->prescriptionModel->savePrescription([
                    'MaDonThuoc' => $data['ma_don_thuoc'],
                    'MaBenhNhan' => $data['ma_benh_nhan'] ?? null,
                    'MaBacSi' => $data['ma_bac_si'] ?? null,
                    'id_phieu_kham_benh' => $data['id_phieu_kham_benh'] ?? null,
                    'NgayKe' => $data['ngay_ke'] ?? date('Y-m-d'),
                    'ChanDoan' => $data['chan_doan'] ?? '',
                    'GhiChu' => ($data['loi_dan'] ?? ($data['ghi_chu'] ?? '')),
                    'TrangThai' => 'Chưa lấy thuốc'
                ]);
            }
            
            // Save medication details and reduce stock
            if (!empty($data['medications'])) {
                foreach ($data['medications'] as $medication) {
                    if (!empty($medication['ma_thuoc'])) {
                        $this->prescriptionModel->saveMedicationDetail([
                            'MaDonThuoc' => $data['ma_don_thuoc'],
                            'MaThuoc' => $medication['ma_thuoc'],
                            'TenThuoc' => $medication['ten_thuoc'] ?? null,
                            'HoatChat' => ($medication['hoat_chất'] ?? ($medication['hoat_chat'] ?? null)),
                            'SoLuong' => $medication['so_luong'] ?? 1,
                            'DonViTinh' => $medication['don_vi_tinh'] ?? '',
                            'LieuDung' => $medication['cach_dung'] ?? '',
                            'GhiChu' => $medication['ghi_chu'] ?? ''
                        ]);
                        // Reduce stock
                        $this->prescriptionModel->reduceStock($medication['ma_thuoc'], (int)($medication['so_luong'] ?? 1));
                    }
                }
            }
            
            // Commit transaction
            $this->prescriptionModel->commit();
            
            return [
                'success' => true,
                'message' => 'Lưu đơn thuốc thành công',
                'prescription_id' => $prescriptionId
            ];
            
        } catch (Exception $e) {
            // Rollback transaction
            $this->prescriptionModel->rollback();
            
            return [
                'success' => false,
                'message' => 'Lỗi khi lưu đơn thuốc: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lấy thông tin đơn thuốc theo ID
     */
    public function getPrescription($prescriptionId) {
        try {
            $prescription = $this->prescriptionModel->getPrescriptionById($prescriptionId);
            
            if (!$prescription) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy đơn thuốc'
                ];
            }
            
            // Get medication details
            $medications = $this->prescriptionModel->getMedicationDetails($prescriptionId);
            $prescription['medications'] = $medications;
            
            return [
                'success' => true,
                'prescription' => $prescription
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin đơn thuốc: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy đơn thuốc theo id_phieu_kham_benh (đơn mới nhất)
     */
    public function getPrescriptionByExamId() {
        try {
            $examId = $_GET['exam_id'] ?? '';
            if (empty($examId)) {
                echo json_encode(['success' => false, 'message' => 'Thiếu exam_id']);
                exit();
            }

            // Lấy đơn thuốc mới nhất theo id_phieu_kham_benh
            $sql = "SELECT * FROM don_thuoc WHERE id_phieu_kham_benh = ? ORDER BY NgayTao DESC LIMIT 1";
            $pdo = (new Database())->getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([(int)$examId]);
            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prescription) {
                echo json_encode(['success' => true, 'prescription' => null]);
                exit();
            }

            // Lấy chi tiết thuốc
            $medications = $this->prescriptionModel->getMedicationDetails($prescription['MaDonThuoc']);
            $prescription['medications'] = $medications;

            echo json_encode(['success' => true, 'prescription' => $prescription]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Trang in đơn thuốc (giống pattern in siêu âm): ?action=print_prescription&code=MaDonThuoc
     */
    // print page functionality removed
    
    /**
     * Cập nhật trạng thái đơn thuốc
     */
    public function updatePrescriptionStatus($prescriptionId, $status) {
        try {
            $result = $this->prescriptionModel->updateStatus($prescriptionId, $status);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể cập nhật trạng thái'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lấy lịch sử đơn thuốc của bệnh nhân
     */
    public function getPatientPrescriptionHistory($patientId) {
        try {
            $prescriptions = $this->prescriptionModel->getPatientPrescriptions($patientId);
            
            return [
                'success' => true,
                'prescriptions' => $prescriptions
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy lịch sử đơn thuốc: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Tìm kiếm thuốc theo tên hoặc mã
     */
    public function searchMedications($keyword) {
        try {
            $medications = $this->medicationModel->searchMedications($keyword);
            
            return [
                'success' => true,
                'medications' => $medications
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm thuốc: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Xóa đơn thuốc
     */
    public function deletePrescription($prescriptionId) {
        try {
            $result = $this->prescriptionModel->deletePrescription($prescriptionId);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Xóa đơn thuốc thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể xóa đơn thuốc'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi xóa đơn thuốc: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * In đơn thuốc
     */
    public function printPrescription($prescriptionId) {
        try {
            $prescription = $this->getPrescription($prescriptionId);
            
            if (!$prescription['success']) {
                return $prescription;
            }
            
            // Generate PDF or return HTML for printing
            return [
                'success' => true,
                'prescription' => $prescription['prescription']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi in đơn thuốc: ' . $e->getMessage()
            ];
        }
    }
}
?>
