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
            
            // Start transaction
            $this->prescriptionModel->beginTransaction();
            
            // Save prescription
            $prescriptionId = $this->prescriptionModel->savePrescription([
                'MaDonThuoc' => $data['ma_don_thuoc'],
                'MaBenhNhan' => $data['ma_benh_nhan'] ?? null,
                'MaBacSi' => $data['ma_bac_si'] ?? null,
                'id_phieu_kham_benh' => $data['id_phieu_kham_benh'] ?? null,
                'NgayKe' => $data['ngay_ke'] ?? date('Y-m-d'),
                'ChanDoan' => $data['chan_doan'] ?? '',
                'GhiChu' => $data['ghi_chu'] ?? '',
                'TrangThai' => 'Chưa lấy thuốc'
            ]);
            
            // Save medication details
            if (!empty($data['medications'])) {
                foreach ($data['medications'] as $medication) {
                    if (!empty($medication['ma_thuoc'])) {
                        $this->prescriptionModel->saveMedicationDetail([
                            'MaDonThuoc' => $data['ma_don_thuoc'],
                            'MaThuoc' => $medication['ma_thuoc'],
                            'SoLuong' => $medication['so_luong'] ?? 1,
                            'DonViTinh' => $medication['don_vi_tinh'] ?? '',
                            'LieuDung' => $medication['cach_dung'] ?? '',
                            'GhiChu' => $medication['ghi_chu'] ?? ''
                        ]);
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
