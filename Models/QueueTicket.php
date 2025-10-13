<?php
require_once 'config/database.php';

class QueueTicket
{
    private $conn;
    private $table = 'phieu_boc_so';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function issueTicket(int $patientId, int $doctorId, ?int $appointmentId = null, int $priority = 0, ?string $counter = null): array
    {
        $today = date('Y-m-d');
        $next = $this->nextSerialForDoctor($doctorId, $today);
        $sql = "INSERT INTO {$this->table} (ngay, so_thu_tu, benh_nhan_id, bac_si_id, lich_hen_id, trang_thai, uu_tien, quay)
				VALUES (:ngay, :so, :bn, :bs, :lh, 'cho', :ut, :quay)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':ngay' => $today,
            ':so' => $next,
            ':bn' => $patientId,
            ':bs' => $doctorId,
            ':lh' => $appointmentId,
            ':ut' => $priority,
            ':quay' => $counter,
        ]);
        $id = (int)$this->conn->lastInsertId();
        return [
            'id' => $id,
            'ngay' => $today,
            'so_thu_tu' => $next,
            'benh_nhan_id' => $patientId,
            'bac_si_id' => $doctorId,
            'lich_hen_id' => $appointmentId,
            'trang_thai' => 'cho',
            'uu_tien' => $priority,
            'quay' => $counter,
        ];
    }

    public function nextSerialForDoctor(int $doctorId, string $dateYmd): int
    {
        // Global per-day numbering: ignore doctor, take next across all tickets of the day
        $stmt = $this->conn->prepare("SELECT COALESCE(MAX(so_thu_tu), 0) FROM {$this->table} WHERE ngay = :d");
        $stmt->execute([':d' => $dateYmd]);
        return (int)$stmt->fetchColumn() + 1;
    }

    public function listQueue(int $doctorId, string $dateYmd, ?string $status = null): array
    {
        $q = "SELECT * FROM {$this->table} WHERE bac_si_id = :bs AND ngay = :d";
        $params = [':bs' => $doctorId, ':d' => $dateYmd];
        if ($status) {
            $q .= " AND trang_thai = :st";
            $params[':st'] = $status;
        }
        $q .= " ORDER BY uu_tien DESC, so_thu_tu ASC";
        $stmt = $this->conn->prepare($q);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $ticketId, string $status): bool
    {
        $timeCols = [
            'dang_goi' => 'thoi_gian_goi',
            'dang_kham' => 'thoi_gian_bat_dau',
            'xong' => 'thoi_gian_ket_thuc',
        ];
        $setTime = isset($timeCols[$status]) ? ", {$timeCols[$status]} = NOW()" : '';
        $sql = "UPDATE {$this->table} SET trang_thai = :st{$setTime}, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':st' => $status, ':id' => $ticketId]);
    }

    public function updateStatusByAppointmentId(int $appointmentId, string $status): bool
    {
        $today = date('Y-m-d');
        $timeCols = [
            'dang_goi' => 'thoi_gian_goi',
            'dang_kham' => 'thoi_gian_bat_dau',
            'xong' => 'thoi_gian_ket_thuc',
        ];
        $setTime = isset($timeCols[$status]) ? ", {$timeCols[$status]} = NOW()" : '';
        $sql = "UPDATE {$this->table} SET trang_thai = :st{$setTime}, updated_at = NOW() 
				WHERE lich_hen_id = :aid AND ngay = :d AND trang_thai IN ('cho','dang_goi')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':st' => $status, ':aid' => $appointmentId, ':d' => $today]);
    }

    public function getById(int $ticketId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function reassignDoctor(int $ticketId, int $newDoctorId, string $dateYmd): ?array
    {
        $next = $this->nextSerialForDoctor($newDoctorId, $dateYmd);
        $sql = "UPDATE {$this->table} SET bac_si_id = :bs, so_thu_tu = :so, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt->execute([':bs' => $newDoctorId, ':so' => $next, ':id' => $ticketId])) {
            return null;
        }
        return $this->getById($ticketId);
    }
}