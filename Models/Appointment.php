<?php
require_once 'config/database.php';
require_once 'Models/User.php';

class Appointment extends User
{
    protected $table = 'lich_hen';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Tạo lịch hẹn mới
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (benh_nhan_id, bac_si_id, ngay_hen, gio_hen, ly_do, loai_lich, dia_chi_kham, link_tu_van, trang_thai, ghi_chu) 
                    VALUES (:benh_nhan_id, :bac_si_id, :ngay_hen, :gio_hen, :ly_do, :loai_lich, :dia_chi_kham, :link_tu_van, :trang_thai, :ghi_chu)";

            $stmt = $this->getConnection()->prepare($sql);
            $result = $stmt->execute([
                ':benh_nhan_id' => $data['benh_nhan_id'],
                ':bac_si_id' => $data['bac_si_id'],
                ':ngay_hen' => $data['ngay_hen'],
                ':gio_hen' => $data['gio_hen'],
                ':ly_do' => $data['ly_do'] ?? null,
                ':loai_lich' => $data['loai_lich'] ?? 'Trực tiếp',
                ':dia_chi_kham' => $data['dia_chi_kham'] ?? null,
                ':link_tu_van' => $data['link_tu_van'] ?? null,
                ':trang_thai' => $data['trang_thai'] ?? 'Chờ xác nhận',
                ':ghi_chu' => $data['ghi_chu'] ?? null
            ]);

            return $result ? $this->getConnection()->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Appointment create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tất cả lịch hẹn của bệnh nhân
     */
    public function getByPatientId($patientId)
    {
        try {
            $sql = "SELECT 
                lh.id,
                lh.ngay_hen,
                lh.gio_hen,
                lh.ly_do,
                lh.trang_thai,
                lh.ghi_chu,
                lh.loai_lich,
                lh.dia_chi_kham,
                lh.link_tu_van,
                lh.ngay_tao,
                lh.ngay_cap_nhat,
                bs.ten as ten_bac_si,
                bs.chuyen_khoa,
                bs.hinh_anh as avatar_bac_si,
                bs.so_nam_kinh_nghiem,
                bn.ten as ten_benh_nhan,
                bn.so_dien_thoai,
                bn.gioi_tinh
            FROM lich_hen lh
            JOIN bac_si bs ON lh.bac_si_id = bs.id
            JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
            WHERE bn.id = :patient_id
            ORDER BY lh.ngay_hen DESC, lh.gio_hen DESC";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':patient_id' => $patientId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            error_log("Appointment getByPatientId error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tất cả lịch hẹn của bác sĩ
     */
    public function getByDoctorId($doctorId)
    {
        try {
            $sql = "SELECT lh.*, bn.ten as ten_benh_nhan, bn.so_dien_thoai, bn.gioi_tinh
                    FROM {$this->table} lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    WHERE lh.bac_si_id = :doctor_id
                    ORDER BY lh.ngay_hen DESC, lh.gio_hen DESC";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':doctor_id' => $doctorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getByDoctorId error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy lịch hẹn theo ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT lh.*, bs.ten as ten_bac_si, bs.chuyen_khoa, bs.hinh_anh as avatar_bac_si,
                           bn.ten as ten_benh_nhan, bn.so_dien_thoai, bn.gioi_tinh
                    FROM {$this->table} lh
                    JOIN bac_si bs ON lh.bac_si_id = bs.id
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    WHERE lh.id = :id";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật trạng thái lịch hẹn
     */
    public function updateStatus($id, $status, $ghiChu = null)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET trang_thai = :trang_thai, ghi_chu = :ghi_chu, ngay_cap_nhat = CURRENT_TIMESTAMP
                    WHERE id = :id";

            $stmt = $this->getConnection()->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':trang_thai' => $status,
                ':ghi_chu' => $ghiChu
            ]);
        } catch (PDOException $e) {
            error_log("Appointment updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa lịch hẹn
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->getConnection()->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Appointment delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra xung đột lịch hẹn
     */
    public function checkConflict($doctorId, $ngayHen, $gioHen)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM {$this->table} 
                    WHERE bac_si_id = :doctor_id 
                    AND ngay_hen = :ngay_hen 
                    AND gio_hen = :gio_hen 
                    AND trang_thai IN ('Chờ xác nhận', 'Đã xác nhận')";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':ngay_hen' => $ngayHen,
                ':gio_hen' => $gioHen
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Appointment checkConflict error: " . $e->getMessage());
            return true; // Trả về true để an toàn
        }
    }

    /**
     * Lấy khung giờ khám có sẵn của bác sĩ trong ngày
     * Mỗi khung giờ 20 phút
     */
    public function getAvailableTimeSlots($doctorId, $ngayHen)
    {
        try {
            // Lấy lịch làm việc của bác sĩ trong ngày
            $dayOfWeek = $this->getDayOfWeek($ngayHen);

            $sql = "SELECT gio_bat_dau, gio_ket_thuc, loai_ca
                    FROM lich_lam_viec 
                    WHERE bac_si_id = :doctor_id 
                    AND thu_trong_tuan = :thu_trong_tuan 
                    AND trang_thai = 'active'";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':thu_trong_tuan' => $dayOfWeek
            ]);

            $workSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($workSchedules)) {
                return [];
            }

            // Lấy các lịch hẹn đã có trong ngày
            $sql = "SELECT gio_hen 
                    FROM {$this->table} 
                    WHERE bac_si_id = :doctor_id 
                    AND ngay_hen = :ngay_hen 
                    AND trang_thai IN ('Chờ xác nhận', 'Đã xác nhận')";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':ngay_hen' => $ngayHen
            ]);

            $bookedTimes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'gio_hen');

            // Tạo khung giờ 20 phút cho mỗi ca làm việc
            $availableSlots = [];

            foreach ($workSchedules as $schedule) {
                $startTime = new DateTime($schedule['gio_bat_dau']);
                $endTime = new DateTime($schedule['gio_ket_thuc']);

                // Tạo khung giờ 20 phút
                $currentTime = clone $startTime;
                while ($currentTime < $endTime) {
                    $timeSlot = $currentTime->format('H:i');
                    $nextSlot = clone $currentTime;
                    $nextSlot->add(new DateInterval('PT20M'));

                    // Kiểm tra xem khung giờ này có bị trùng không
                    if (!in_array($timeSlot, $bookedTimes) && $nextSlot <= $endTime) {
                        $availableSlots[] = [
                            'time' => $timeSlot,
                            'end_time' => $nextSlot->format('H:i'),
                            'shift' => $schedule['loai_ca'],
                            'display' => $timeSlot . ' - ' . $nextSlot->format('H:i')
                        ];
                    }

                    $currentTime->add(new DateInterval('PT20M'));
                }
            }

            return $availableSlots;
        } catch (Exception $e) {
            error_log("Appointment getAvailableTimeSlots error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách bác sĩ theo chuyên khoa
     */
    public function getDoctorsBySpecialty($specialty = null)
    {
        try {
            $sql = "SELECT bs.id, bs.ten, ck.ten AS chuyen_khoa, bs.so_nam_kinh_nghiem, bs.hinh_anh
                    FROM bac_si bs
                    LEFT JOIN chuyen_khoa ck ON ck.id = bs.chuyen_khoa_id
                    WHERE 1=1";

            $params = [];

            if ($specialty) {
                $sql .= " AND ck.ten = :specialty";
                $params[':specialty'] = $specialty;
            }

            $sql .= " ORDER BY chuyen_khoa, ten";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getDoctorsBySpecialty error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách chuyên khoa
     */
    public function getSpecialties()
    {
        try {
            $sql = "SELECT DISTINCT ten 
                    FROM chuyen_khoa 
                    WHERE trang_thai = 'active' 
                    ORDER BY ten";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ten');
        } catch (PDOException $e) {
            error_log("Appointment getSpecialties error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Chuyển đổi ngày thành thứ trong tuần
     */
    private function getDayOfWeek($date)
    {
        $days = [
            'Monday' => 'Thứ 2',
            'Tuesday' => 'Thứ 3',
            'Wednesday' => 'Thứ 4',
            'Thursday' => 'Thứ 5',
            'Friday' => 'Thứ 6',
            'Saturday' => 'Thứ 7',
            'Sunday' => 'Chủ nhật'
        ];

        $dayName = date('l', strtotime($date));
        return $days[$dayName] ?? 'Thứ 2';
    }

    /**
     * Lấy thống kê lịch hẹn
     */
    public function getStats($doctorId = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN trang_thai = 'Chờ xác nhận' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN trang_thai = 'Đã xác nhận' THEN 1 ELSE 0 END) as confirmed,
                        SUM(CASE WHEN trang_thai = 'Hoàn thành' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN trang_thai = 'hủy' THEN 1 ELSE 0 END) as cancelled
                    FROM {$this->table}";

            $params = [];
            if ($doctorId) {
                $sql .= " WHERE bac_si_id = :doctor_id";
                $params[':doctor_id'] = $doctorId;
            }

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getStats error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }
    }

    /**
     * Cập nhật Google Meet link cho lịch hẹn
     */
    public function updateMeetLink($appointmentId, $meetLink)
    {
        try {
            $sql = "UPDATE {$this->table} SET link_tu_van = :link_tu_van WHERE id = :id";
            $stmt = $this->getConnection()->prepare($sql);
            return $stmt->execute([
                ':link_tu_van' => $meetLink,
                ':id' => $appointmentId
            ]);
        } catch (PDOException $e) {
            error_log("Appointment updateMeetLink error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy lịch hẹn hôm nay của bác sĩ
     */
    public function getTodayAppointmentsByDoctor($doctorId)
    {
        try {
            $today = date('Y-m-d');
            $sql = "SELECT lh.*, bn.ten as ten_benh_nhan, bn.so_dien_thoai, bn.gioi_tinh, bn.ngay_sinh, bn.dia_chi, bn.nhom_mau
                    FROM {$this->table} lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    WHERE lh.bac_si_id = :doctor_id 
                    AND lh.ngay_hen = :today
                    AND lh.trang_thai IN ('Đã xác nhận', 'Chờ xác nhận')
                    AND lh.loai_lich != 'Tư vấn'
                    ORDER BY lh.gio_hen ASC";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':today' => $today
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getTodayAppointmentsByDoctor error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy lịch hẹn theo ngày của bác sĩ (đã xác nhận và đang khám)
     */
    public function getAppointmentsByDoctorAndDate($doctorId, $date)
    {
        try {
            $sql = "SELECT lh.*, bn.ten as ten_benh_nhan, bn.so_dien_thoai, bn.gioi_tinh, bn.ngay_sinh, bn.dia_chi, bn.nhom_mau, bn.bao_hiem_y_te, bhy.ngay_het_han
                    FROM {$this->table} lh
                    JOIN benh_nhan bn ON lh.benh_nhan_id = bn.id
                    LEFT JOIN bao_hiem_y_te bhy ON bn.bao_hiem_y_te_id = bhy.id
                    WHERE lh.bac_si_id = :doctor_id 
                    AND lh.ngay_hen = :date
                    AND lh.trang_thai IN ('Đã xác nhận', 'Đang khám')
                    AND lh.loai_lich != 'Tư vấn'
                    ORDER BY lh.gio_hen ASC";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':date' => $date
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getAppointmentsByDoctorAndDate error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thống kê lịch hẹn hôm nay của bác sĩ
     */
    public function getTodayStatsByDoctor($doctorId)
    {
        try {
            $today = date('Y-m-d');
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN trang_thai = 'Chờ xác nhận' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN trang_thai = 'Đã xác nhận' THEN 1 ELSE 0 END) as confirmed,
                        SUM(CASE WHEN trang_thai = 'Hoàn thành' THEN 1 ELSE 0 END) as completed
                    FROM {$this->table}
                    WHERE bac_si_id = :doctor_id AND ngay_hen = :today AND loai_lich != 'Tư vấn'";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':today' => $today
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getTodayStatsByDoctor error: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'completed' => 0
            ];
        }
    }

    /**
     * Lấy thống kê lịch hẹn theo ngày của bác sĩ (Tổng số bệnh nhân, Đang khám, Đã khám xong)
     */
    public function getStatsByDoctorAndDate($doctorId, $date)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_patients,
                        SUM(CASE WHEN trang_thai = 'Đang khám' THEN 1 ELSE 0 END) as examining,
                        SUM(CASE WHEN trang_thai = 'Hoàn thành' THEN 1 ELSE 0 END) as completed
                    FROM {$this->table}
                    WHERE bac_si_id = :doctor_id AND ngay_hen = :date AND loai_lich != 'Tư vấn'";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':doctor_id' => $doctorId,
                ':date' => $date
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Appointment getStatsByDoctorAndDate error: " . $e->getMessage());
            return [
                'total_patients' => 0,
                'examining' => 0,
                'completed' => 0
            ];
        }
    }

    /**
     * Bắt đầu khám bệnh - đổi trạng thái thành "Đang khám"
     */
    public function startExamination($appointmentId, $doctorId)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET trang_thai = 'Đang khám', 
                        ngay_cap_nhat = NOW()
                    WHERE id = :appointment_id 
                    AND bac_si_id = :doctor_id 
                    AND trang_thai = 'Đã xác nhận'";

            $stmt = $this->getConnection()->prepare($sql);
            $result = $stmt->execute([
                ':appointment_id' => $appointmentId,
                ':doctor_id' => $doctorId
            ]);

            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Appointment startExamination error: " . $e->getMessage());
            return false;
        }
    }
    // ===== Allergy history =====

    private function allergySchemaVariant(): string
    {
        try {
            $stmt = $this->getConnection()->prepare("SHOW COLUMNS FROM phieu_tien_su_di_ung LIKE 'con_trung'");
            $stmt->execute();
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            return $exists ? 'standard' : 'prefixed';
        } catch (PDOException $e) {
            return 'standard';
        }
    }

    public function upsertAllergyHistory($patientId, $data)
    {
        try {
            // Check exists
            $stmt = $this->getConnection()->prepare("SELECT id FROM phieu_tien_su_di_ung WHERE benh_nhan_id = :pid LIMIT 1");
            $stmt->execute([':pid' => $patientId]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            $variant = $this->allergySchemaVariant();

            $payload = [
                ':pid' => $patientId,
                ':thuoc' => $data['allergy_drug'] ?? null,
                ':sl_thuoc' => $data['allergy_drug_times'] ?? null,
                ':khong_thuoc' => isset($data['allergy_drug_no']) ? (int)$data['allergy_drug_no'] : 0,
                ':gc_thuoc' => $data['allergy_drug_note'] ?? null,
                ':ctr' => $data['allergy_insect'] ?? null,
                ':sl_ctr' => $data['allergy_insect_times'] ?? null,
                ':khong_ctr' => isset($data['allergy_insect_no']) ? (int)$data['allergy_insect_no'] : 0,
                ':gc_ctr' => $data['allergy_insect_note'] ?? null,
                ':tp' => $data['allergy_food'] ?? null,
                ':sl_tp' => $data['allergy_food_times'] ?? null,
                ':khong_tp' => isset($data['allergy_food_no']) ? (int)$data['allergy_food_no'] : 0,
                ':gc_tp' => $data['allergy_food_note'] ?? null,
                ':tnk' => $data['allergy_other'] ?? null,
                ':sl_tnk' => $data['allergy_other_times'] ?? null,
                ':khong_tnk' => isset($data['allergy_other_no']) ? (int)$data['allergy_other_no'] : 0,
                ':gc_tnk' => $data['allergy_other_note'] ?? null,
                ':tscn' => $data['personal_history'] ?? null,
                ':sl_tscn' => $data['personal_history_times'] ?? null,
                ':khong_tscn' => isset($data['personal_history_no']) ? (int)$data['personal_history_no'] : 0,
                ':gc_tscn' => $data['personal_history_note'] ?? null,
                ':tsgd' => $data['family_history'] ?? null,
                ':sl_tsgd' => $data['family_history_times'] ?? null,
                ':khong_tsgd' => isset($data['family_history_no']) ? (int)$data['family_history_no'] : 0,
                ':gc_tsgd' => $data['family_history_note'] ?? null,
            ];

            if ($variant === 'standard') {
                if ($exists) {
                    $sql = "UPDATE phieu_tien_su_di_ung SET
                            thuoc_hoac_di_nguyen=:thuoc, so_lan_thuoc=:sl_thuoc, khong_thuoc=:khong_thuoc, ghi_chu_thuoc=:gc_thuoc,
                            con_trung=:ctr, so_lan_con_trung=:sl_ctr, khong_con_trung=:khong_ctr, ghi_chu_con_trung=:gc_ctr,
                            thuc_pham=:tp, so_lan_thuc_pham=:sl_tp, khong_thuc_pham=:khong_tp, ghi_chu_thuc_pham=:gc_tp,
                            tac_nhan_khac=:tnk, so_lan_tac_nhan_khac=:sl_tnk, khong_tac_nhan_khac=:khong_tnk, ghi_chu_tac_nhan_khac=:gc_tnk,
                            tien_su_ca_nhan=:tscn, so_lan_tien_su_ca_nhan=:sl_tscn, khong_tien_su_ca_nhan=:khong_tscn, ghi_chu_tien_su_ca_nhan=:gc_tscn,
                            tien_su_gia_dinh=:tsgd, so_lan_tien_su_gia_dinh=:sl_tsgd, khong_tien_su_gia_dinh=:khong_tsgd, ghi_chu_tien_su_gia_dinh=:gc_tsgd
                            WHERE benh_nhan_id=:pid";
                } else {
                    $sql = "INSERT INTO phieu_tien_su_di_ung (
                            benh_nhan_id, thuoc_hoac_di_nguyen, so_lan_thuoc, khong_thuoc, ghi_chu_thuoc,
                            con_trung, so_lan_con_trung, khong_con_trung, ghi_chu_con_trung,
                            thuc_pham, so_lan_thuc_pham, khong_thuc_pham, ghi_chu_thuc_pham,
                            tac_nhan_khac, so_lan_tac_nhan_khac, khong_tac_nhan_khac, ghi_chu_tac_nhan_khac,
                            tien_su_ca_nhan, so_lan_tien_su_ca_nhan, khong_tien_su_ca_nhan, ghi_chu_tien_su_ca_nhan,
                            tien_su_gia_dinh, so_lan_tien_su_gia_dinh, khong_tien_su_gia_dinh, ghi_chu_tien_su_gia_dinh
                        ) VALUES (
                            :pid, :thuoc, :sl_thuoc, :khong_thuoc, :gc_thuoc,
                            :ctr, :sl_ctr, :khong_ctr, :gc_ctr,
                            :tp, :sl_tp, :khong_tp, :gc_tp,
                            :tnk, :sl_tnk, :khong_tnk, :gc_tnk,
                            :tscn, :sl_tscn, :khong_tscn, :gc_tscn,
                            :tsgd, :sl_tsgd, :khong_tsgd, :gc_tsgd
                        )";
                }
            } else { // prefixed schema with di_ung_*
                if ($exists) {
                    $sql = "UPDATE phieu_tien_su_di_ung SET
                            thuoc_hoac_di_nguyen=:thuoc, so_lan_thuoc=:sl_thuoc, khong_thuoc=:khong_thuoc, ghi_chu_thuoc=:gc_thuoc,
                            di_ung_con_trung=:ctr, so_lan_con_trung=:sl_ctr, khong_con_trung=:khong_ctr, ghi_chu_con_trung=:gc_ctr,
                            di_ung_thuc_pham=:tp, so_lan_thuc_pham=:sl_tp, khong_thuc_pham=:khong_tp, ghi_chu_thuc_pham=:gc_tp,
                            di_ung_tac_nhan_khac=:tnk, so_lan_tac_nhan_khac=:sl_tnk, khong_tac_nhan_khac=:khong_tnk, ghi_chu_tac_nhan_khac=:gc_tnk,
                            tien_su_ca_nhan=:tscn, so_lan_tien_su_ca_nhan=:sl_tscn, khong_tien_su_ca_nhan=:khong_tscn, ghi_chu_tien_su_ca_nhan=:gc_tscn,
                            tien_su_gia_dinh=:tsgd, so_lan_tien_su_gia_dinh=:sl_tsgd, khong_tien_su_gia_dinh=:khong_tsgd, ghi_chu_tien_su_gia_dinh=:gc_tsgd
                            WHERE benh_nhan_id=:pid";
                } else {
                    $sql = "INSERT INTO phieu_tien_su_di_ung (
                            benh_nhan_id, thuoc_hoac_di_nguyen, so_lan_thuoc, khong_thuoc, ghi_chu_thuoc,
                            di_ung_con_trung, so_lan_con_trung, khong_con_trung, ghi_chu_con_trung,
                            di_ung_thuc_pham, so_lan_thuc_pham, khong_thuc_pham, ghi_chu_thuc_pham,
                            di_ung_tac_nhan_khac, so_lan_tac_nhan_khac, khong_tac_nhan_khac, ghi_chu_tac_nhan_khac,
                            tien_su_ca_nhan, so_lan_tien_su_ca_nhan, khong_tien_su_ca_nhan, ghi_chu_tien_su_ca_nhan,
                            tien_su_gia_dinh, so_lan_tien_su_gia_dinh, khong_tien_su_gia_dinh, ghi_chu_tien_su_gia_dinh
                        ) VALUES (
                            :pid, :thuoc, :sl_thuoc, :khong_thuoc, :gc_thuoc,
                            :ctr, :sl_ctr, :khong_ctr, :gc_ctr,
                            :tp, :sl_tp, :khong_tp, :gc_tp,
                            :tnk, :sl_tnk, :khong_tnk, :gc_tnk,
                            :tscn, :sl_tscn, :khong_tscn, :gc_tscn,
                            :tsgd, :sl_tsgd, :khong_tsgd, :gc_tsgd
                        )";
                }
            }

            $stmt2 = $this->getConnection()->prepare($sql);
            return $stmt2->execute($payload);
        } catch (PDOException $e) {
            error_log('upsertAllergyHistory error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllergyHistoryByPatient($patientId)
    {
        try {
            $stmt = $this->getConnection()->prepare("SELECT * FROM phieu_tien_su_di_ung WHERE benh_nhan_id = :pid LIMIT 1");
            $stmt->execute([':pid' => $patientId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            if (!$row) return null;
            // Normalize keys for frontend (no di_ung_ prefix)
            if (array_key_exists('di_ung_con_trung', $row)) {
                $row['con_trung'] = $row['di_ung_con_trung'];
            }
            if (array_key_exists('di_ung_thuc_pham', $row)) {
                $row['thuc_pham'] = $row['di_ung_thuc_pham'];
            }
            if (array_key_exists('di_ung_tac_nhan_khac', $row)) {
                $row['tac_nhan_khac'] = $row['di_ung_tac_nhan_khac'];
            }
            return $row;
        } catch (PDOException $e) {
            error_log('getAllergyHistoryByPatient error: ' . $e->getMessage());
            return null;
        }
    }
}