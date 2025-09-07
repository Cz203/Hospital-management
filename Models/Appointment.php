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
            $sql = "SELECT id, ten, chuyen_khoa, so_nam_kinh_nghiem, hinh_anh
                    FROM bac_si 
                    WHERE 1=1";

            $params = [];

            if ($specialty) {
                $sql .= " AND chuyen_khoa = :specialty";
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
            $sql = "SELECT DISTINCT chuyen_khoa 
                    FROM bac_si 
                    WHERE chuyen_khoa IS NOT NULL 
                    ORDER BY chuyen_khoa";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chuyen_khoa');
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
}
