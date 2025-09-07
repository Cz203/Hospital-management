-- Cập nhật database cho chức năng quản lý lịch làm việc bác sĩ
-- Chạy file này trong phpMyAdmin hoặc MySQL client

-- Tạo bảng lịch làm việc cho bác sĩ
CREATE TABLE `lich_lam_viec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bac_si_id` int(11) NOT NULL,
  `thu_trong_tuan` enum('Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','Chủ nhật') NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `loai_ca` enum('Ca sáng','Ca chiều','Ca tối') NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` enum('active','inactive') DEFAULT 'active',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bac_si_id` (`bac_si_id`),
  KEY `idx_thu_trong_tuan` (`thu_trong_tuan`),
  KEY `idx_trang_thai` (`trang_thai`),
  CONSTRAINT `lich_lam_viec_ibfk_1` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm dữ liệu mẫu cho bác sĩ đầu tiên (GSTS. Cao Việt)
INSERT INTO `lich_lam_viec` (`bac_si_id`, `thu_trong_tuan`, `gio_bat_dau`, `gio_ket_thuc`, `loai_ca`, `ghi_chu`, `trang_thai`) VALUES
(1, 'Thứ 2', '08:00:00', '12:00:00', 'Ca sáng', 'Khám tim mạch', 'active'),
(1, 'Thứ 2', '14:00:00', '18:00:00', 'Ca chiều', 'Khám tim mạch', 'active'),
(1, 'Thứ 3', '08:00:00', '12:00:00', 'Ca sáng', 'Khám tim mạch', 'active'),
(1, 'Thứ 3', '14:00:00', '18:00:00', 'Ca chiều', 'Khám tim mạch', 'active'),
(1, 'Thứ 4', '08:00:00', '12:00:00', 'Ca sáng', 'Khám tim mạch', 'active'),
(1, 'Thứ 5', '08:00:00', '12:00:00', 'Ca sáng', 'Khám tim mạch', 'active'),
(1, 'Thứ 5', '14:00:00', '18:00:00', 'Ca chiều', 'Khám tim mạch', 'active'),
(1, 'Thứ 6', '08:00:00', '12:00:00', 'Ca sáng', 'Khám tim mạch', 'active');

-- =============================================
-- Thêm bảng lưu thông báo (chuông) theo người dùng
-- =============================================

-- Bảng thông báo
-- Lưu ý: hệ thống đang dùng các bảng người dùng riêng: `bac_si`, `benh_nhan`, `quan_tri_vien`
-- Nên bảng thông báo sẽ lưu theo đối tượng và id tương ứng để đúng cấu trúc CSDL hiện tại
CREATE TABLE IF NOT EXISTS `thong_bao` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `doi_tuong` ENUM('bac_si','benh_nhan','quan_tri_vien') NOT NULL,
  `bac_si_id` INT(11) DEFAULT NULL,
  `benh_nhan_id` INT(11) DEFAULT NULL,
  `quan_tri_vien_id` INT(11) DEFAULT NULL,
  `loai` VARCHAR(50) NOT NULL DEFAULT 'info', -- info|success|warning|danger
  `noi_dung` TEXT NOT NULL,
  `du_lieu_kem_theo` JSON DEFAULT NULL,
  `da_doc` TINYINT(1) NOT NULL DEFAULT 0,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doi_tuong` (`doi_tuong`),
  KEY `idx_bac_si_id` (`bac_si_id`),
  KEY `idx_benh_nhan_id` (`benh_nhan_id`),
  KEY `idx_quan_tri_vien_id` (`quan_tri_vien_id`),
  KEY `idx_da_doc` (`da_doc`),
  CONSTRAINT `fk_tb_bac_si` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_benh_nhan` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_quan_tri_vien` FOREIGN KEY (`quan_tri_vien_id`) REFERENCES `quan_tri_vien`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thủ tục thêm nhanh thông báo
DROP PROCEDURE IF EXISTS `sp_tao_thong_bao`;
DELIMITER $$
CREATE PROCEDURE `sp_tao_thong_bao`(
    IN p_doi_tuong VARCHAR(30),      -- 'bac_si' | 'benh_nhan' | 'quan_tri_vien'
    IN p_doi_tuong_id INT,           -- id tương ứng theo bảng ở trên
    IN p_loai VARCHAR(50),
    IN p_noi_dung TEXT,
    IN p_du_lieu_kem_theo JSON
)
BEGIN
    IF p_doi_tuong = 'bac_si' THEN
        INSERT INTO `thong_bao`(`doi_tuong`,`bac_si_id`,`loai`,`noi_dung`,`du_lieu_kem_theo`)
        VALUES('bac_si', p_doi_tuong_id, COALESCE(p_loai,'info'), p_noi_dung, p_du_lieu_kem_theo);
    ELSEIF p_doi_tuong = 'benh_nhan' THEN
        INSERT INTO `thong_bao`(`doi_tuong`,`benh_nhan_id`,`loai`,`noi_dung`,`du_lieu_kem_theo`)
        VALUES('benh_nhan', p_doi_tuong_id, COALESCE(p_loai,'info'), p_noi_dung, p_du_lieu_kem_theo);
    ELSEIF p_doi_tuong = 'quan_tri_vien' THEN
        INSERT INTO `thong_bao`(`doi_tuong`,`quan_tri_vien_id`,`loai`,`noi_dung`,`du_lieu_kem_theo`)
        VALUES('quan_tri_vien', p_doi_tuong_id, COALESCE(p_loai,'info'), p_noi_dung, p_du_lieu_kem_theo);
    END IF;
END$$
DELIMITER ;

-- API gợi ý (PHP):
-- GET ./notifications -> SELECT * FROM thong_bao WHERE nguoi_dung_id = :id ORDER BY id DESC LIMIT 20
-- POST ./notifications/read -> UPDATE thong_bao SET da_doc = 1 WHERE id = :id AND nguoi_dung_id = :id