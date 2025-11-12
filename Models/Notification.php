<?php
require_once 'config/database.php';

class Notification
{
    private $conn;
    private $table = 'thong_bao';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createForDoctor(int $doctorId, string $message, string $type = 'info', ?array $extra = null): bool
    {
        return $this->create('bac_si', $doctorId, $message, $type, $extra);
    }

    public function createForPatient(int $patientId, string $message, string $type = 'info', ?array $extra = null): bool
    {
        return $this->create('benh_nhan', $patientId, $message, $type, $extra);
    }

    public function createForAdmin(int $adminId, string $message, string $type = 'info', ?array $extra = null): bool
    {
        return $this->create('quan_tri_vien', $adminId, $message, $type, $extra);
    }

    private function create(string $target, int $targetId, string $message, string $type = 'info', ?array $extra = null): bool
    {
        if ($targetId <= 0 || $message === '') {
            return false;
        }
        $loai = in_array($type, ['info', 'success', 'warning', 'danger', 'error']) ? $type : 'info';
        $json = $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null;

        $cols = "doi_tuong, loai, noi_dung, du_lieu_kem_theo, da_doc, ngay_tao";
        $vals = ":doi_tuong, :loai, :noi_dung, :du_lieu_kem_theo, 0, NOW()";
        if ($target === 'bac_si') {
            $cols .= ", bac_si_id";
            $vals .= ", :target_id";
        } elseif ($target === 'benh_nhan') {
            $cols .= ", benh_nhan_id";
            $vals .= ", :target_id";
        } elseif ($target === 'quan_tri_vien') {
            $cols .= ", quan_tri_vien_id";
            $vals .= ", :target_id";
        } else {
            return false;
        }

        $sql = "INSERT INTO {$this->table} ({$cols}) VALUES ({$vals})";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':doi_tuong', $target);
        $stmt->bindValue(':loai', $loai);
        $stmt->bindValue(':noi_dung', $message);
        $stmt->bindValue(':du_lieu_kem_theo', $json);
        $stmt->bindValue(':target_id', $targetId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function listForUser(string $role, int $userId, int $limit = 20): array
    {
        if ($userId <= 0) return [];

        $where = '';
        if (in_array($role, ['doctor', 'xray_doctor', 'sieuam_doctor', 'xetnghiem_doctor'], true)) {
            $where = "doi_tuong = 'bac_si' AND bac_si_id = :uid";
        } elseif ($role === 'patient') {
            $where = "doi_tuong = 'benh_nhan' AND benh_nhan_id = :uid";
        } elseif ($role === 'admin') {
            $where = "doi_tuong = 'quan_tri_vien' AND quan_tri_vien_id = :uid";
        } else {
            // Chưa hỗ trợ đối tượng khác (ví dụ: lễ tân)
            return [];
        }

        $sql = "SELECT id, doi_tuong, bac_si_id, benh_nhan_id, quan_tri_vien_id, loai, noi_dung, du_lieu_kem_theo, da_doc, ngay_tao
                FROM {$this->table}
                WHERE {$where}
                ORDER BY ngay_tao DESC
                LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return $rows;
    }

    public function markAllRead(string $role, int $userId): bool
    {
        if ($userId <= 0) return false;
        if (in_array($role, ['doctor', 'xray_doctor', 'sieuam_doctor', 'xetnghiem_doctor'], true)) {
            $sql = "UPDATE {$this->table} SET da_doc = 1 WHERE doi_tuong = 'bac_si' AND bac_si_id = :uid AND da_doc = 0";
        } elseif ($role === 'patient') {
            $sql = "UPDATE {$this->table} SET da_doc = 1 WHERE doi_tuong = 'benh_nhan' AND benh_nhan_id = :uid AND da_doc = 0";
        } elseif ($role === 'admin') {
            $sql = "UPDATE {$this->table} SET da_doc = 1 WHERE doi_tuong = 'quan_tri_vien' AND quan_tri_vien_id = :uid AND da_doc = 0";
        } else {
            return false;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
