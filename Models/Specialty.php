<?php
require_once 'config/database.php';

class Specialty
{
    private $conn;
    private $table = 'chuyen_khoa';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function all()
    {
        $sql = "SELECT id, ten, slug, mo_ta, icon, thu_tu, trang_thai, ngay_tao, ngay_cap_nhat FROM {$this->table} ORDER BY thu_tu, ten";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySlug($slug)
    {
        $sql = "SELECT id, ten, slug, mo_ta, icon FROM {$this->table} WHERE slug = :slug";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT id, ten, slug, mo_ta, icon, thu_tu, trang_thai FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function allWithDoctorCounts()
    {
        $sql = "SELECT ck.id, ck.ten, ck.slug, ck.mo_ta, ck.icon, ck.thu_tu, ck.trang_thai,
                       COUNT(bs.id) AS doctor_count
                FROM {$this->table} ck
                LEFT JOIN bac_si bs ON bs.chuyen_khoa_id = ck.id
                GROUP BY ck.id, ck.ten, ck.slug, ck.mo_ta, ck.icon, ck.thu_tu, ck.trang_thai
                ORDER BY ck.thu_tu, ck.ten";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (ten, slug, mo_ta, icon, thu_tu, trang_thai) VALUES (:ten, :slug, :mo_ta, :icon, :thu_tu, :trang_thai)";
        $stmt = $this->conn->prepare($sql);
        $slug = $data['slug'] ?? $this->slugify($data['ten']);
        $thu_tu = isset($data['thu_tu']) ? (int)$data['thu_tu'] : 0;
        $trang_thai = $data['trang_thai'] ?? 'active';
        $stmt->bindParam(':ten', $data['ten']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':mo_ta', $data['mo_ta']);
        $stmt->bindParam(':icon', $data['icon']);
        $stmt->bindParam(':thu_tu', $thu_tu, PDO::PARAM_INT);
        $stmt->bindParam(':trang_thai', $trang_thai);
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET ten = :ten, slug = :slug, mo_ta = :mo_ta, icon = :icon, thu_tu = :thu_tu, trang_thai = :trang_thai WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $slug = $data['slug'] ?? $this->slugify($data['ten']);
        $thu_tu = isset($data['thu_tu']) ? (int)$data['thu_tu'] : 0;
        $trang_thai = $data['trang_thai'] ?? 'active';
        $stmt->bindParam(':ten', $data['ten']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':mo_ta', $data['mo_ta']);
        $stmt->bindParam(':icon', $data['icon']);
        $stmt->bindParam(':thu_tu', $thu_tu, PDO::PARAM_INT);
        $stmt->bindParam(':trang_thai', $trang_thai);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id)
    {
        // Chỉ cho phép xóa nếu không còn bác sĩ tham chiếu
        $check = $this->conn->prepare("SELECT COUNT(*) FROM bac_si WHERE chuyen_khoa_id = :id");
        $check->bindParam(':id', $id, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetchColumn() > 0) {
            return false;
        }
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function slugify($text)
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: null;
    }
}