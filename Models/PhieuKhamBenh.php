<?php
require_once 'config/database.php';

class PhieuKhamBenh
{
    private $conn;
    private $table = 'phieu_kham_benh';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    private function v($arr, $key)
    {
        return array_key_exists($key, $arr) ? $arr[$key] : null;
    }

    public function create(array $data)
    {
        try {
            // Debug: Log ngay_sinh value before binding
            error_log('PhieuKhamBenh::create - ngay_sinh value: ' . var_export($this->v($data, 'ngay_sinh'), true));

            $sql = "INSERT INTO {$this->table} (
                benh_nhan_id, bac_si_id, so_y_te, benh_vien, buong_kham,
                ho_ten, ngay_sinh, thang_sinh, nam_sinh, tuoi, gioi_tinh, nghe_nghiep, dan_toc, ngoai_kieu, noi_lam_viec, dia_chi,
                doi_tuong_bhyt, doi_tuong_thu_phi, doi_tuong_mien, doi_tuong_khac,
                bhyt_ngay, bhyt_thang, bhyt_nam, so_the_bhyt,
                dien_thoai_bao_tin,
                gio_kham, phut_kham, ngay_kham, thang_kham, nam_kham, chan_doan_gioi_thieu,
                qua_trinh_benh_li, tien_su_ban_than, tien_su_gia_dinh,
                kham_toan_than, mach, nhiet_do, huyet_ap_tam_thu, huyet_ap_tam_truong, nhip_tho, kham_cac_bo_phan, tom_tat_lam_sang, chan_doan_vao_vien, da_xu_li, khoa_dieu_tri, chu_y,
                ngay_ky, thang_ky, nam_ky, ten_bac_si, id_lich_hen
            ) VALUES (
                :benh_nhan_id, :bac_si_id, :so_y_te, :benh_vien, :buong_kham,
                :ho_ten, :ngay_sinh, :thang_sinh, :nam_sinh, :tuoi, :gioi_tinh, :nghe_nghiep, :dan_toc, :ngoai_kieu, :noi_lam_viec, :dia_chi,
                :doi_tuong_bhyt, :doi_tuong_thu_phi, :doi_tuong_mien, :doi_tuong_khac,
                :bhyt_ngay, :bhyt_thang, :bhyt_nam, :so_the_bhyt,
                :dien_thoai_bao_tin,
                :gio_kham, :phut_kham, :ngay_kham, :thang_kham, :nam_kham, :chan_doan_gioi_thieu,
                :qua_trinh_benh_li, :tien_su_ban_than, :tien_su_gia_dinh,
                :kham_toan_than, :mach, :nhiet_do, :huyet_ap_tam_thu, :huyet_ap_tam_truong, :nhip_tho, :kham_cac_bo_phan, :tom_tat_lam_sang, :chan_doan_vao_vien, :da_xu_li, :khoa_dieu_tri, :chu_y,
                :ngay_ky, :thang_ky, :nam_ky, :ten_bac_si, :id_lich_hen
            )";

            $stmt = $this->conn->prepare($sql);
            $ok = $stmt->execute([
                ':benh_nhan_id' => $this->v($data, 'benh_nhan_id'),
                ':bac_si_id' => $this->v($data, 'bac_si_id'),
                ':so_y_te' => $this->v($data, 'so_y_te'),
                ':benh_vien' => $this->v($data, 'benh_vien'),
                ':buong_kham' => $this->v($data, 'buong_kham'),
                ':ho_ten' => $this->v($data, 'ho_ten'),
                ':ngay_sinh' => $this->v($data, 'ngay_sinh'),
                ':thang_sinh' => $this->v($data, 'thang_sinh'),
                ':nam_sinh' => $this->v($data, 'nam_sinh'),
                ':tuoi' => $this->v($data, 'tuoi'),
                ':gioi_tinh' => $this->v($data, 'gioi_tinh'),
                ':nghe_nghiep' => $this->v($data, 'nghe_nghiep'),
                ':dan_toc' => $this->v($data, 'dan_toc'),
                ':ngoai_kieu' => $this->v($data, 'ngoai_kieu'),
                ':noi_lam_viec' => $this->v($data, 'noi_lam_viec'),
                ':dia_chi' => $this->v($data, 'dia_chi'),
                ':doi_tuong_bhyt' => (int)$this->v($data, 'doi_tuong_bhyt'),
                ':doi_tuong_thu_phi' => (int)$this->v($data, 'doi_tuong_thu_phi'),
                ':doi_tuong_mien' => (int)$this->v($data, 'doi_tuong_mien'),
                ':doi_tuong_khac' => (int)$this->v($data, 'doi_tuong_khac'),
                ':bhyt_ngay' => $this->v($data, 'bhyt_ngay'),
                ':bhyt_thang' => $this->v($data, 'bhyt_thang'),
                ':bhyt_nam' => $this->v($data, 'bhyt_nam'),
                ':so_the_bhyt' => $this->v($data, 'so_the_bhyt'),
                ':dien_thoai_bao_tin' => $this->v($data, 'dien_thoai_bao_tin'),
                ':gio_kham' => $this->v($data, 'gio_kham'),
                ':phut_kham' => $this->v($data, 'phut_kham'),
                ':ngay_kham' => $this->v($data, 'ngay_kham'),
                ':thang_kham' => $this->v($data, 'thang_kham'),
                ':nam_kham' => $this->v($data, 'nam_kham'),
                ':chan_doan_gioi_thieu' => $this->v($data, 'chan_doan_gioi_thieu'),
                ':qua_trinh_benh_li' => $this->v($data, 'qua_trinh_benh_li'),
                ':tien_su_ban_than' => $this->v($data, 'tien_su_ban_than'),
                ':tien_su_gia_dinh' => $this->v($data, 'tien_su_gia_dinh'),
                ':kham_toan_than' => $this->v($data, 'kham_toan_than'),
                ':mach' => $this->v($data, 'mach'),
                ':nhiet_do' => $this->v($data, 'nhiet_do'),
                ':huyet_ap_tam_thu' => $this->v($data, 'huyet_ap_tam_thu'),
                ':huyet_ap_tam_truong' => $this->v($data, 'huyet_ap_tam_truong'),
                ':nhip_tho' => $this->v($data, 'nhip_tho'),
                ':kham_cac_bo_phan' => $this->v($data, 'kham_cac_bo_phan'),
                ':tom_tat_lam_sang' => $this->v($data, 'tom_tat_lam_sang'),
                ':chan_doan_vao_vien' => $this->v($data, 'chan_doan_vao_vien'),
                ':da_xu_li' => $this->v($data, 'da_xu_li'),
                ':khoa_dieu_tri' => $this->v($data, 'khoa_dieu_tri'),
                ':chu_y' => $this->v($data, 'chu_y'),
                ':ngay_ky' => $this->v($data, 'ngay_ky'),
                ':thang_ky' => $this->v($data, 'thang_ky'),
                ':nam_ky' => $this->v($data, 'nam_ky'),
                ':ten_bac_si' => $this->v($data, 'ten_bac_si'),
                ':id_lich_hen' => $this->v($data, 'id_lich_hen'),
            ]);

            if (!$ok) {
                $errorInfo = $stmt->errorInfo();
                error_log("PhieuKhamBenh::create failed - SQLSTATE: {$errorInfo[0]}, Error: {$errorInfo[2]}");
                return false;
            }

            $lastId = $this->conn->lastInsertId();
            if (!$lastId || $lastId == 0) {
                error_log("PhieuKhamBenh::create - lastInsertId returned: " . var_export($lastId, true));
                return false;
            }

            return $lastId;
        } catch (PDOException $e) {
            error_log("PhieuKhamBenh::create PDO Exception: " . $e->getMessage());
            throw $e;
        }
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByAppointmentId($appointmentId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id_lich_hen = :aid ORDER BY id DESC LIMIT 1");
        $stmt->execute([':aid' => $appointmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function upsertByAppointmentId(int $appointmentId, array $data)
    {
        try {
            // Debug: Log ngay_sinh value before upsert
            error_log('PhieuKhamBenh::upsertByAppointmentId - ngay_sinh value: ' . var_export($this->v($data, 'ngay_sinh'), true));

            $existing = $this->getByAppointmentId($appointmentId);
            if ($existing) {
                // build update
                $sql = "UPDATE {$this->table} SET
                    id_lich_hen=:id_lich_hen, benh_nhan_id=:benh_nhan_id, bac_si_id=:bac_si_id, so_y_te=:so_y_te, benh_vien=:benh_vien, buong_kham=:buong_kham,
                    ho_ten=:ho_ten, ngay_sinh=:ngay_sinh, thang_sinh=:thang_sinh, nam_sinh=:nam_sinh, tuoi=:tuoi, gioi_tinh=:gioi_tinh, nghe_nghiep=:nghe_nghiep, dan_toc=:dan_toc, ngoai_kieu=:ngoai_kieu, noi_lam_viec=:noi_lam_viec, dia_chi=:dia_chi,
                    doi_tuong_bhyt=:doi_tuong_bhyt, doi_tuong_thu_phi=:doi_tuong_thu_phi, doi_tuong_mien=:doi_tuong_mien, doi_tuong_khac=:doi_tuong_khac,
                    bhyt_ngay=:bhyt_ngay, bhyt_thang=:bhyt_thang, bhyt_nam=:bhyt_nam, so_the_bhyt=:so_the_bhyt,
                    dien_thoai_bao_tin=:dien_thoai_bao_tin,
                    gio_kham=:gio_kham, phut_kham=:phut_kham, ngay_kham=:ngay_kham, thang_kham=:thang_kham, nam_kham=:nam_kham, chan_doan_gioi_thieu=:chan_doan_gioi_thieu,
                    qua_trinh_benh_li=:qua_trinh_benh_li, tien_su_ban_than=:tien_su_ban_than, tien_su_gia_dinh=:tien_su_gia_dinh,
                    kham_toan_than=:kham_toan_than, mach=:mach, nhiet_do=:nhiet_do, huyet_ap_tam_thu=:huyet_ap_tam_thu, huyet_ap_tam_truong=:huyet_ap_tam_truong, nhip_tho=:nhip_tho, kham_cac_bo_phan=:kham_cac_bo_phan, tom_tat_lam_sang=:tom_tat_lam_sang, chan_doan_vao_vien=:chan_doan_vao_vien, da_xu_li=:da_xu_li, khoa_dieu_tri=:khoa_dieu_tri, chu_y=:chu_y,
                    ngay_ky=:ngay_ky, thang_ky=:thang_ky, nam_ky=:nam_ky, ten_bac_si=:ten_bac_si
                    WHERE id=:id";
                $stmt = $this->conn->prepare($sql);
                $ok = $stmt->execute(array_merge([
                    ':id' => $existing['id'],
                ], [
                    ':id_lich_hen' => $appointmentId,
                    ':benh_nhan_id' => $this->v($data, 'benh_nhan_id'),
                    ':bac_si_id' => $this->v($data, 'bac_si_id'),
                    ':so_y_te' => $this->v($data, 'so_y_te'),
                    ':benh_vien' => $this->v($data, 'benh_vien'),
                    ':buong_kham' => $this->v($data, 'buong_kham'),
                    ':ho_ten' => $this->v($data, 'ho_ten'),
                    ':ngay_sinh' => $this->v($data, 'ngay_sinh'),
                    ':thang_sinh' => $this->v($data, 'thang_sinh'),
                    ':nam_sinh' => $this->v($data, 'nam_sinh'),
                    ':tuoi' => $this->v($data, 'tuoi'),
                    ':gioi_tinh' => $this->v($data, 'gioi_tinh'),
                    ':nghe_nghiep' => $this->v($data, 'nghe_nghiep'),
                    ':dan_toc' => $this->v($data, 'dan_toc'),
                    ':ngoai_kieu' => $this->v($data, 'ngoai_kieu'),
                    ':noi_lam_viec' => $this->v($data, 'noi_lam_viec'),
                    ':dia_chi' => $this->v($data, 'dia_chi'),
                    ':doi_tuong_bhyt' => (int)$this->v($data, 'doi_tuong_bhyt'),
                    ':doi_tuong_thu_phi' => (int)$this->v($data, 'doi_tuong_thu_phi'),
                    ':doi_tuong_mien' => (int)$this->v($data, 'doi_tuong_mien'),
                    ':doi_tuong_khac' => (int)$this->v($data, 'doi_tuong_khac'),
                    ':bhyt_ngay' => $this->v($data, 'bhyt_ngay'),
                    ':bhyt_thang' => $this->v($data, 'bhyt_thang'),
                    ':bhyt_nam' => $this->v($data, 'bhyt_nam'),
                    ':so_the_bhyt' => $this->v($data, 'so_the_bhyt'),
                    ':dien_thoai_bao_tin' => $this->v($data, 'dien_thoai_bao_tin'),
                    ':gio_kham' => $this->v($data, 'gio_kham'),
                    ':phut_kham' => $this->v($data, 'phut_kham'),
                    ':ngay_kham' => $this->v($data, 'ngay_kham'),
                    ':thang_kham' => $this->v($data, 'thang_kham'),
                    ':nam_kham' => $this->v($data, 'nam_kham'),
                    ':chan_doan_gioi_thieu' => $this->v($data, 'chan_doan_gioi_thieu'),
                    ':qua_trinh_benh_li' => $this->v($data, 'qua_trinh_benh_li'),
                    ':tien_su_ban_than' => $this->v($data, 'tien_su_ban_than'),
                    ':tien_su_gia_dinh' => $this->v($data, 'tien_su_gia_dinh'),
                    ':kham_toan_than' => $this->v($data, 'kham_toan_than'),
                    ':mach' => $this->v($data, 'mach'),
                    ':nhiet_do' => $this->v($data, 'nhiet_do'),
                    ':huyet_ap_tam_thu' => $this->v($data, 'huyet_ap_tam_thu'),
                    ':huyet_ap_tam_truong' => $this->v($data, 'huyet_ap_tam_truong'),
                    ':nhip_tho' => $this->v($data, 'nhip_tho'),
                    ':kham_cac_bo_phan' => $this->v($data, 'kham_cac_bo_phan'),
                    ':tom_tat_lam_sang' => $this->v($data, 'tom_tat_lam_sang'),
                    ':chan_doan_vao_vien' => $this->v($data, 'chan_doan_vao_vien'),
                    ':da_xu_li' => $this->v($data, 'da_xu_li'),
                    ':khoa_dieu_tri' => $this->v($data, 'khoa_dieu_tri'),
                    ':chu_y' => $this->v($data, 'chu_y'),
                    ':ngay_ky' => $this->v($data, 'ngay_ky'),
                    ':thang_ky' => $this->v($data, 'thang_ky'),
                    ':nam_ky' => $this->v($data, 'nam_ky'),
                    ':ten_bac_si' => $this->v($data, 'ten_bac_si'),
                ]));

                if (!$ok) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("PhieuKhamBenh::upsertByAppointmentId UPDATE failed - SQLSTATE: {$errorInfo[0]}, Error: {$errorInfo[2]}");
                    return false;
                }

                return $existing['id'];
            }
            // else insert new
            return $this->create($data);
        } catch (PDOException $e) {
            error_log("PhieuKhamBenh::upsertByAppointmentId PDO Exception: " . $e->getMessage());
            throw $e;
        }
    }
}
