<?php
require_once 'Models/User.php';

class Admin extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = "quan_tri_vien";
    }

    public function login($email, $password)
    {
        // Đổi logic: tham số đầu dùng như số điện thoại để đăng nhập
        $raw = trim($email);
        $p1 = $raw;
        $p2 = $raw;
        if (str_starts_with($raw, '84')) {
            $p2 = '0' . substr($raw, 2);
        } elseif (str_starts_with($raw, '0')) {
            $p2 = '84' . substr($raw, 1);
        }
        $query = "SELECT * FROM " . $this->table_name . " WHERE so_dien_thoai = :p1 OR so_dien_thoai = :p2 LIMIT 1";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":p1", $p1);
        $stmt->bindParam(":p2", $p2);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($this->verifyPassword($password, $row['mat_khau'])) {
                return $row;
            }
        }
        return false;
    }

    public function emailExists($email)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function phoneExists($phone)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE so_dien_thoai = :phone";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":phone", $phone);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create($data)
    {
        // Kiểm tra email đã tồn tại chưa
        if ($this->emailExists($data['email'])) {
            throw new Exception("Email đã được sử dụng. Vui lòng chọn email khác.");
        }

        // Kiểm tra số điện thoại đã tồn tại chưa
        if ($this->phoneExists($data['so_dien_thoai'])) {
            throw new Exception("Số điện thoại đã được sử dụng. Vui lòng chọn số khác.");
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, phone_verified, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :phone_verified, NOW())";

        $stmt = $this->getConnection()->prepare($query);

        $hashedPassword = $this->hashPassword($data['mat_khau']);
        $phone_verified = $data['phone_verified'] ?? 0;

        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindParam(":phone_verified", $phone_verified);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT id, ten, email, so_dien_thoai, ngay_tao FROM " . $this->table_name;
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT id, ten, email, so_dien_thoai, mat_khau, ngay_tao FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE " . $this->table_name . " SET mat_khau = :mat_khau WHERE id = :id";
        $stmt = $this->getConnection()->prepare($query);
        $hashedPassword = $this->hashPassword($newPassword);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Lấy tổng số bác sĩ
     */
    public function getTotalDoctors()
    {
        $stmt = $this->getConnection()->query("SELECT COUNT(*) as total FROM bac_si");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy tổng số bệnh nhân
     */
    public function getTotalPatients()
    {
        $stmt = $this->getConnection()->query("SELECT COUNT(*) as total FROM benh_nhan");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy tổng số lễ tân
     */
    public function getTotalReceptions()
    {
        $stmt = $this->getConnection()->query("SELECT COUNT(*) as total FROM le_tan");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số bệnh nhân mới hôm nay
     */
    public function getNewPatientsToday($today)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as total FROM benh_nhan WHERE DATE(ngay_tao) = :today");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy tổng số lịch hẹn (tất cả thời gian)
     */
    public function getTotalAppointments()
    {
        $stmt = $this->getConnection()->query("SELECT COUNT(*) as total FROM lich_hen");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số lịch hẹn hôm nay
     */
    public function getTotalAppointmentsToday($today)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as total FROM lich_hen WHERE ngay_hen = :today");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy lịch hẹn theo trạng thái hôm nay
     */
    public function getAppointmentsStatusToday($today)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                SUM(CASE WHEN trang_thai = 'Chờ xác nhận' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN trang_thai = 'Đã xác nhận' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN trang_thai = 'Đang khám' THEN 1 ELSE 0 END) as examining,
                SUM(CASE WHEN trang_thai = 'Hoàn thành' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN trang_thai = 'hủy' THEN 1 ELSE 0 END) as cancelled
            FROM lich_hen 
            WHERE ngay_hen = :today
        ");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'pending' => (int)($result['pending'] ?? 0),
            'confirmed' => (int)($result['confirmed'] ?? 0),
            'examining' => (int)($result['examining'] ?? 0),
            'completed' => (int)($result['completed'] ?? 0),
            'cancelled' => (int)($result['cancelled'] ?? 0)
        ];
    }

    /**
     * Lấy doanh thu hôm nay
     */
    public function getRevenueToday($today)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COALESCE(SUM(tong_nguoi_benh), 0) as total 
            FROM bien_lai_vien_phi 
            WHERE DATE(ngay_lap) = :today 
            AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
        ");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Lấy doanh thu tuần này
     */
    public function getRevenueWeek($weekStart, $weekEnd)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COALESCE(SUM(tong_nguoi_benh), 0) as total 
            FROM bien_lai_vien_phi 
            WHERE DATE(ngay_lap) BETWEEN :week_start AND :week_end
            AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
        ");
        $stmt->execute([':week_start' => $weekStart, ':week_end' => $weekEnd]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Lấy doanh thu tháng này
     */
    public function getRevenueMonth($currentMonth)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COALESCE(SUM(tong_nguoi_benh), 0) as total 
            FROM bien_lai_vien_phi 
            WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month 
            AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
        ");
        $stmt->execute([':month' => $currentMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Lấy tổng doanh thu (tất cả thời gian)
     */
    public function getTotalRevenueAll()
    {
        $stmt = $this->getConnection()->query("
            SELECT COALESCE(SUM(tong_nguoi_benh), 0) AS total
            FROM bien_lai_vien_phi
            WHERE trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Lấy số biên lai chưa thanh toán
     */
    public function getUnpaidReceipts()
    {
        $stmt = $this->getConnection()->query("SELECT COUNT(*) as total FROM bien_lai_vien_phi WHERE trang_thai = 'Chưa thanh toán'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy tỷ lệ BHYT tháng này
     */
    public function getBhytRatio($currentMonth)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                COALESCE(SUM(tong_quy_bhyt), 0) as bhyt_total,
                COALESCE(SUM(tong_tien_co_ban), 0) as total_base
            FROM bien_lai_vien_phi 
            WHERE DATE_FORMAT(ngay_lap, '%Y-%m') = :month 
            AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
        ");
        $stmt->execute([':month' => $currentMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $bhytTotal = (float)($result['bhyt_total'] ?? 0);
        $totalBase = (float)($result['total_base'] ?? 0);
        return $totalBase > 0 ? round(($bhytTotal / $totalBase) * 100, 1) : 0;
    }

    /**
     * Lấy số X-Quang hôm nay
     */
    public function getXrayToday($today)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as total FROM phieu_chup_xquang WHERE DATE(ngay_tao) = :today");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số siêu âm hôm nay
     */
    public function getUltrasoundToday($today)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as total FROM phieu_yeu_cau_sieu_am WHERE DATE(ngay_tao) = :today");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số xét nghiệm hôm nay
     */
    public function getLabToday($today)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as total FROM phieu_yeu_cau_xet_nghiem WHERE DATE(ngay_tao) = :today");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số đơn thuốc hôm nay
     */
    public function getPrescriptionsToday($today)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as total FROM don_thuoc WHERE DATE(NgayKe) = :today");
        $stmt->execute([':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số bác sĩ đang trực hôm nay
     */
    public function getOnDutyDoctors($dayName)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(DISTINCT bs.id) as total
            FROM bac_si bs
            INNER JOIN lich_lam_viec llv ON bs.id = llv.bac_si_id
            WHERE llv.trang_thai = 'active'
            AND llv.thu_trong_tuan = :day_name
        ");
        $stmt->execute([':day_name' => $dayName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy số lễ tân đang trực hôm nay
     */
    public function getOnDutyReceptions($dayName)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(DISTINCT lt.id) as total
            FROM le_tan lt
            INNER JOIN lich_lam_viec_le_tan llt ON lt.id = llt.letan_id
            WHERE llt.trang_thai = 'active'
            AND llt.thu_trong_tuan = :day_name
        ");
        $stmt->execute([':day_name' => $dayName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy lịch hẹn theo tháng (12 tháng gần nhất)
     */
    public function getAppointmentsByMonth()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT DATE_FORMAT(ngay_hen, '%Y-%m') as ym, COUNT(*) as c
            FROM lich_hen
            WHERE ngay_hen >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(ngay_hen, '%Y-%m')
            ORDER BY ym ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy doanh thu theo tháng (12 tháng gần nhất)
     */
    public function getRevenueByMonth()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT DATE_FORMAT(ngay_lap, '%Y-%m') as ym, COALESCE(SUM(tong_nguoi_benh), 0) as s
            FROM bien_lai_vien_phi
            WHERE ngay_lap >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND trang_thai IN ('Đã thanh toán tiền mặt', 'Đã thanh toán chuyển khoản')
            GROUP BY DATE_FORMAT(ngay_lap, '%Y-%m')
            ORDER BY ym ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
