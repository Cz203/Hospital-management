<?php
require_once 'Models/User.php';

class Doctor extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = "bac_si";
    }

    public function login($email, $password)
    {
        // Đổi logic: tham số đầu dùng như số điện thoại để đăng nhập, chấp nhận 84/0
        $raw = trim($email);
        $p1 = $raw;
        $p2 = $raw;
        if (str_starts_with($raw, '84')) {
            $p2 = '0' . substr($raw, 2);
        } elseif (str_starts_with($raw, '0')) {
            $p2 = '84' . substr($raw, 1);
        }
        $query = "SELECT * FROM " . $this->table_name . " WHERE so_dien_thoai = :p1 OR so_dien_thoai = :p2 LIMIT 1";
        $stmt = $this->conn->prepare($query);
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
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function phoneExists($phone)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE so_dien_thoai = :phone";
        $stmt = $this->conn->prepare($query);
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

        // Resolve chuyen_khoa_id from provided data (accept both id or name)
        $specialtyId = null;
        if (isset($data['chuyen_khoa_id']) && $data['chuyen_khoa_id']) {
            $specialtyId = (int)$data['chuyen_khoa_id'];
        } elseif (!empty($data['chuyen_khoa'])) {
            $specName = $data['chuyen_khoa'];
            $stmtSpec = $this->conn->prepare("SELECT id FROM chuyen_khoa WHERE ten = :ten LIMIT 1");
            $stmtSpec->bindParam(":ten", $specName);
            $stmtSpec->execute();
            $row = $stmtSpec->fetch(PDO::FETCH_ASSOC);
            $specialtyId = $row ? (int)$row['id'] : null;
            if (!$specialtyId) {
                // Auto create specialty if not exists
                $stmtIns = $this->conn->prepare("INSERT INTO chuyen_khoa(ten) VALUES(:ten)");
                $stmtIns->bindParam(":ten", $specName);
                try {
                    if ($stmtIns->execute()) {
                        $specialtyId = (int)$this->conn->lastInsertId();
                    }
                } catch (PDOException $e) {
                    // ignore if unique constraint violated due to race, try reselect
                    try {
                        $stmtSpec2 = $this->conn->prepare("SELECT id FROM chuyen_khoa WHERE ten = :ten LIMIT 1");
                        $stmtSpec2->bindParam(":ten", $specName);
                        $stmtSpec2->execute();
                        $row2 = $stmtSpec2->fetch(PDO::FETCH_ASSOC);
                        $specialtyId = $row2 ? (int)$row2['id'] : null;
                    } catch (PDOException $e2) {
                    }
                }
            }
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (ten, email, mat_khau, so_dien_thoai, chuyen_khoa_id, so_giay_phep, so_nam_kinh_nghiem, hinh_anh, ngay_tao) 
                  VALUES (:ten, :email, :mat_khau, :so_dien_thoai, :chuyen_khoa_id, :so_giay_phep, :so_nam_kinh_nghiem, :hinh_anh, NOW())";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = $this->hashPassword($data['mat_khau']);

        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindValue(":chuyen_khoa_id", $specialtyId, PDO::PARAM_INT);
        $stmt->bindParam(":so_giay_phep", $data['so_giay_phep']);
        $stmt->bindParam(":so_nam_kinh_nghiem", $data['so_nam_kinh_nghiem']);
        $hinhAnh = $data['hinh_anh'] ?? null;
        $stmt->bindParam(":hinh_anh", $hinhAnh);

        return $stmt->execute();
    }

    public function getAll(): array
    {
        $query = "SELECT bs.id, bs.ten, bs.email, bs.so_dien_thoai, 
                         ck.ten AS chuyen_khoa, bs.chuyen_khoa_id, bs.so_giay_phep, bs.so_nam_kinh_nghiem, bs.ngay_tao, bs.hinh_anh
                  FROM " . $this->table_name . " bs
                  LEFT JOIN chuyen_khoa ck ON ck.id = bs.chuyen_khoa_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table_name);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getPaginated(int $offset, int $limit): array
    {
        $query = "SELECT bs.id, bs.ten, bs.email, bs.so_dien_thoai,
                         ck.ten AS chuyen_khoa, bs.chuyen_khoa_id, bs.so_giay_phep, bs.so_nam_kinh_nghiem, bs.ngay_tao, bs.hinh_anh
                  FROM " . $this->table_name . " bs
                  LEFT JOIN chuyen_khoa ck ON ck.id = bs.chuyen_khoa_id
                  ORDER BY bs.ngay_tao DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $query = "SELECT bs.id, bs.ten, bs.email, bs.so_dien_thoai, 
                         ck.ten AS chuyen_khoa, bs.so_giay_phep, bs.so_nam_kinh_nghiem, bs.mat_khau, bs.ngay_tao, bs.ngay_cap_nhat, bs.hinh_anh
                  FROM " . $this->table_name . " bs
                  LEFT JOIN chuyen_khoa ck ON ck.id = bs.chuyen_khoa_id
                  WHERE bs.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE " . $this->table_name . " SET mat_khau = :mat_khau WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = $this->hashPassword($newPassword);
        $stmt->bindParam(":mat_khau", $hashedPassword);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getBySpecialization($specialization)
    {
        $query = "SELECT bs.id, bs.ten, bs.email, bs.so_dien_thoai, ck.ten AS chuyen_khoa, bs.so_giay_phep, bs.so_nam_kinh_nghiem, bs.hinh_anh
                  FROM " . $this->table_name . " bs
                  LEFT JOIN chuyen_khoa ck ON ck.id = bs.chuyen_khoa_id
                  WHERE ck.ten = :chuyen_khoa";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chuyen_khoa", $specialization);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateImage($id, $imageName)
    {
        $query = "UPDATE " . $this->table_name . " SET hinh_anh = :hinh_anh WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hinh_anh", $imageName);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateProfile(int $id, array $data): bool
    {
        // Allow either chuyen_khoa_id directly or resolve from name
        $specialtyId = $data['chuyen_khoa_id'] ?? null;
        if (!$specialtyId && isset($data['chuyen_khoa'])) {
            $specName = $data['chuyen_khoa'];
            $stmtSpec = $this->conn->prepare("SELECT id FROM chuyen_khoa WHERE ten = :ten LIMIT 1");
            $stmtSpec->bindParam(":ten", $specName);
            $stmtSpec->execute();
            $row = $stmtSpec->fetch(PDO::FETCH_ASSOC);
            $specialtyId = $row ? (int)$row['id'] : null;
        }

        $query = "UPDATE " . $this->table_name . " SET 
                  ten = :ten, 
                  email = :email, 
                  so_dien_thoai = :so_dien_thoai, 
                  chuyen_khoa_id = :chuyen_khoa_id, 
                  so_giay_phep = :so_giay_phep, 
                  so_nam_kinh_nghiem = :so_nam_kinh_nghiem,
                  ngay_cap_nhat = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ten", $data['ten']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":so_dien_thoai", $data['so_dien_thoai']);
        $stmt->bindValue(":chuyen_khoa_id", $specialtyId, PDO::PARAM_INT);
        $stmt->bindParam(":so_giay_phep", $data['so_giay_phep']);
        $stmt->bindParam(":so_nam_kinh_nghiem", $data['so_nam_kinh_nghiem']);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ========== QUẢN LÝ LỊCH LÀM VIỆC ==========

    /**
     * Thêm lịch làm việc mới
     */
    public function addSchedule($doctorId, $data)
    {
        $query = "INSERT INTO lich_lam_viec 
                  (bac_si_id, thu_trong_tuan, gio_bat_dau, gio_ket_thuc, loai_ca, ghi_chu, trang_thai) 
                  VALUES (:bac_si_id, :thu_trong_tuan, :gio_bat_dau, :gio_ket_thuc, :loai_ca, :ghi_chu, :trang_thai)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":bac_si_id", $doctorId);
        $stmt->bindParam(":thu_trong_tuan", $data['thu_trong_tuan']);
        $stmt->bindParam(":gio_bat_dau", $data['gio_bat_dau']);
        $stmt->bindParam(":gio_ket_thuc", $data['gio_ket_thuc']);
        $stmt->bindParam(":loai_ca", $data['loai_ca']);
        $stmt->bindParam(":ghi_chu", $data['ghi_chu']);
        $trangThai = $data['trang_thai'] ?? 'active';
        $stmt->bindParam(":trang_thai", $trangThai);

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }
        return 0;
    }

    /**
     * Lấy tất cả lịch làm việc của bác sĩ
     */
    public function getSchedules($doctorId)
    {
        $query = "SELECT * FROM lich_lam_viec 
                  WHERE bac_si_id = :bac_si_id 
                  ORDER BY 
                    CASE thu_trong_tuan 
                        WHEN 'Thứ 2' THEN 1
                        WHEN 'Thứ 3' THEN 2
                        WHEN 'Thứ 4' THEN 3
                        WHEN 'Thứ 5' THEN 4
                        WHEN 'Thứ 6' THEN 5
                        WHEN 'Thứ 7' THEN 6
                        WHEN 'Chủ nhật' THEN 7
                    END, gio_bat_dau";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":bac_si_id", $doctorId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy lịch làm việc theo ID
     */
    public function getScheduleById($scheduleId, $doctorId)
    {
        $query = "SELECT * FROM lich_lam_viec 
                  WHERE id = :id AND bac_si_id = :bac_si_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $scheduleId);
        $stmt->bindParam(":bac_si_id", $doctorId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật lịch làm việc
     */
    public function updateSchedule($scheduleId, $doctorId, $data)
    {
        $query = "UPDATE lich_lam_viec SET 
                  thu_trong_tuan = :thu_trong_tuan,
                  gio_bat_dau = :gio_bat_dau,
                  gio_ket_thuc = :gio_ket_thuc,
                  loai_ca = :loai_ca,
                  ghi_chu = :ghi_chu,
                  trang_thai = :trang_thai,
                  ngay_cap_nhat = NOW()
                  WHERE id = :id AND bac_si_id = :bac_si_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":thu_trong_tuan", $data['thu_trong_tuan']);
        $stmt->bindParam(":gio_bat_dau", $data['gio_bat_dau']);
        $stmt->bindParam(":gio_ket_thuc", $data['gio_ket_thuc']);
        $stmt->bindParam(":loai_ca", $data['loai_ca']);
        $stmt->bindParam(":ghi_chu", $data['ghi_chu']);
        $trangThai = $data['trang_thai'] ?? 'active';
        $stmt->bindParam(":trang_thai", $trangThai);
        $stmt->bindParam(":id", $scheduleId);
        $stmt->bindParam(":bac_si_id", $doctorId);

        return $stmt->execute();
    }

    /**
     * Xóa lịch làm việc
     */
    public function deleteSchedule($scheduleId, $doctorId)
    {
        $query = "DELETE FROM lich_lam_viec 
                  WHERE id = :id AND bac_si_id = :bac_si_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $scheduleId);
        $stmt->bindParam(":bac_si_id", $doctorId);

        return $stmt->execute();
    }

    /**
     * Kiểm tra xung đột lịch làm việc
     */
    public function checkScheduleConflict($doctorId, $thuTrongTuan, $gioBatDau, $gioKetThuc, $excludeId = null)
    {
        $query = "SELECT COUNT(*) FROM lich_lam_viec 
                  WHERE bac_si_id = :bac_si_id 
                  AND thu_trong_tuan = :thu_trong_tuan 
                  AND trang_thai = 'active'
                  AND (
                      (gio_bat_dau < :gio_ket_thuc AND gio_ket_thuc > :gio_bat_dau)
                  )";

        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":bac_si_id", $doctorId);
        $stmt->bindParam(":thu_trong_tuan", $thuTrongTuan);
        $stmt->bindParam(":gio_bat_dau", $gioBatDau);
        $stmt->bindParam(":gio_ket_thuc", $gioKetThuc);

        if ($excludeId) {
            $stmt->bindParam(":exclude_id", $excludeId);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Lấy lịch làm việc theo thứ trong tuần
     */
    public function getSchedulesByDay($doctorId, $thuTrongTuan)
    {
        $query = "SELECT * FROM lich_lam_viec 
                  WHERE bac_si_id = :bac_si_id 
                  AND thu_trong_tuan = :thu_trong_tuan 
                  AND trang_thai = 'active'
                  ORDER BY gio_bat_dau";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":bac_si_id", $doctorId);
        $stmt->bindParam(":thu_trong_tuan", $thuTrongTuan);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy lịch làm việc theo NGÀY (YYYY-MM-DD), áp dụng ngoại lệ trong ngày nếu có
     */
    public function getSchedulesByDate(int $doctorId, string $dateYmd): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateYmd)) {
            return [];
        }
        $ts = strtotime($dateYmd);
        if ($ts === false) {
            return [];
        }
        $englishDay = date('l', $ts);
        $map = [
            'Monday' => 'Thứ 2',
            'Tuesday' => 'Thứ 3',
            'Wednesday' => 'Thứ 4',
            'Thursday' => 'Thứ 5',
            'Friday' => 'Thứ 6',
            'Saturday' => 'Thứ 7',
            'Sunday' => 'Chủ nhật',
        ];
        $thuVn = isset($map[$englishDay]) ? $map[$englishDay] : '';
        if ($thuVn === '') {
            return [];
        }

        $base = $this->getSchedulesByDay($doctorId, $thuVn);
        // Đọc ngoại lệ đúng ngày
        $exList = $this->getScheduleExceptionsByDateRange($doctorId, $dateYmd, $dateYmd);
        $exByScheduleId = [];
        foreach ($exList as $ex) {
            $exByScheduleId[(int)$ex['schedule_id']] = $ex;
        }

        $result = [];
        foreach ($base as $row) {
            $sid = (int)$row['id'];
            if (isset($exByScheduleId[$sid])) {
                $ex = $exByScheduleId[$sid];
                if ($ex['action'] === 'cancel') {
                    continue; // bỏ lịch này trong ngày
                }
                if ($ex['action'] === 'modify') {
                    if (!empty($ex['loai_ca'])) $row['loai_ca'] = $ex['loai_ca'];
                    if (!empty($ex['gio_bat_dau'])) $row['gio_bat_dau'] = $ex['gio_bat_dau'];
                    if (!empty($ex['gio_ket_thuc'])) $row['gio_ket_thuc'] = $ex['gio_ket_thuc'];
                    $row['ghi_chu'] = $row['ghi_chu'] ?: $ex['ghi_chu'];
                    $row['exception_action'] = 'modify';
                }
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Kiểm tra xung đột ca làm việc theo NGÀY (đã áp dụng ngoại lệ hiện có),
     * loại trừ một scheduleId nếu cung cấp
     */
    public function checkDateScheduleConflict(int $doctorId, string $dateYmd, string $gioBatDau, string $gioKetThuc, ?int $excludeScheduleId = null): bool
    {
        $schedules = $this->getSchedulesByDate($doctorId, $dateYmd);
        $newStart = strtotime($gioBatDau);
        $newEnd = strtotime($gioKetThuc);
        if ($newStart === false || $newEnd === false) {
            return true; // coi là xung đột nếu thời gian không hợp lệ
        }
        foreach ($schedules as $sc) {
            $sid = (int)$sc['id'];
            if ($excludeScheduleId !== null && $sid === $excludeScheduleId) {
                continue;
            }
            $existStart = strtotime($sc['gio_bat_dau']);
            $existEnd = strtotime($sc['gio_ket_thuc']);
            if ($existStart === false || $existEnd === false) {
                continue;
            }
            if ($newStart < $existEnd && $newEnd > $existStart) {
                return true;
            }
        }
        return false;
    }

    // ====== Ngoại lệ theo ngày (chỉ áp dụng cho 1 ngày cụ thể) ======

    public function getScheduleExceptionsByDateRange($doctorId, $startDate, $endDate)
    {

        $query = "SELECT * FROM lich_lam_viec_ngoai_le WHERE bac_si_id = :bac_si_id AND ngay BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':bac_si_id', $doctorId, PDO::PARAM_INT);
        $stmt->bindParam(':start', $startDate);
        $stmt->bindParam(':end', $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertModifyException($doctorId, $scheduleId, $dateYmd, $data)
    {

        // Upsert style: try update then insert if not exists
        $queryUpdate = "UPDATE lich_lam_viec_ngoai_le SET action='modify', gio_bat_dau=:gio_bat_dau, gio_ket_thuc=:gio_ket_thuc, loai_ca=:loai_ca, ghi_chu=:ghi_chu WHERE bac_si_id=:bac_si_id AND schedule_id=:schedule_id AND ngay=:ngay";
        $stmt = $this->conn->prepare($queryUpdate);
        $stmt->bindParam(':gio_bat_dau', $data['gio_bat_dau']);
        $stmt->bindParam(':gio_ket_thuc', $data['gio_ket_thuc']);
        $stmt->bindParam(':loai_ca', $data['loai_ca']);
        $stmt->bindParam(':ghi_chu', $data['ghi_chu']);
        $stmt->bindParam(':bac_si_id', $doctorId, PDO::PARAM_INT);
        $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->bindParam(':ngay', $dateYmd);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return true;

        $queryInsert = "INSERT INTO lich_lam_viec_ngoai_le (bac_si_id, schedule_id, ngay, action, gio_bat_dau, gio_ket_thuc, loai_ca, ghi_chu) VALUES (:bac_si_id, :schedule_id, :ngay, 'modify', :gio_bat_dau, :gio_ket_thuc, :loai_ca, :ghi_chu)";
        $stmt2 = $this->conn->prepare($queryInsert);
        $stmt2->bindParam(':bac_si_id', $doctorId, PDO::PARAM_INT);
        $stmt2->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt2->bindParam(':ngay', $dateYmd);
        $stmt2->bindParam(':gio_bat_dau', $data['gio_bat_dau']);
        $stmt2->bindParam(':gio_ket_thuc', $data['gio_ket_thuc']);
        $stmt2->bindParam(':loai_ca', $data['loai_ca']);
        $stmt2->bindParam(':ghi_chu', $data['ghi_chu']);
        return $stmt2->execute();
    }

    public function addCancelException($doctorId, $scheduleId, $dateYmd, $reason)
    {

        // If a modify exists, convert to cancel
        $queryUp = "UPDATE lich_lam_viec_ngoai_le SET action='cancel', gio_bat_dau=NULL, gio_ket_thuc=NULL, loai_ca=NULL, ghi_chu=:ghi_chu WHERE bac_si_id=:bac_si_id AND schedule_id=:schedule_id AND ngay=:ngay";
        $stmt = $this->conn->prepare($queryUp);
        $stmt->bindParam(':ghi_chu', $reason);
        $stmt->bindParam(':bac_si_id', $doctorId, PDO::PARAM_INT);
        $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->bindParam(':ngay', $dateYmd);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return true;

        $queryInsert = "INSERT INTO lich_lam_viec_ngoai_le (bac_si_id, schedule_id, ngay, action, ghi_chu) VALUES (:bac_si_id, :schedule_id, :ngay, 'cancel', :ghi_chu)";
        $stmt2 = $this->conn->prepare($queryInsert);
        $stmt2->bindParam(':bac_si_id', $doctorId, PDO::PARAM_INT);
        $stmt2->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt2->bindParam(':ngay', $dateYmd);
        $stmt2->bindParam(':ghi_chu', $reason);
        return $stmt2->execute();
    }

    /**
     * Lấy thống kê lịch làm việc
     */
    public function getScheduleStats($doctorId)
    {
        $query = "SELECT 
                    COUNT(*) as total_schedules,
                    COUNT(CASE WHEN trang_thai = 'active' THEN 1 END) as active_schedules,
                    COUNT(CASE WHEN loai_ca = 'Ca sáng' THEN 1 END) as morning_shifts,
                    COUNT(CASE WHEN loai_ca = 'Ca chiều' THEN 1 END) as afternoon_shifts,
                    COUNT(CASE WHEN loai_ca = 'Ca tối' THEN 1 END) as evening_shifts,
                    COUNT(CASE WHEN loai_ca = 'Ca đêm' THEN 1 END) as night_shifts
                  FROM lich_lam_viec 
                  WHERE bac_si_id = :bac_si_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":bac_si_id", $doctorId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}