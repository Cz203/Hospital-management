-- Tạo bảng lưu trữ dữ liệu CCCD giả lập
-- Bảng này giả lập cơ sở dữ liệu CCCD từ Bộ Công an

CREATE TABLE IF NOT EXISTS `cccd_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cccd` varchar(12) NOT NULL COMMENT 'Số CCCD (12 số)',
  `ten` varchar(255) NOT NULL COMMENT 'Họ và tên',
  `ngay_sinh` date NOT NULL COMMENT 'Ngày sinh',
  `gioi_tinh` enum('Nam','Nữ') NOT NULL COMMENT 'Giới tính',
  `dia_chi` varchar(255) DEFAULT NULL COMMENT 'Địa chỉ thường trú',
  `ngay_cap` date DEFAULT NULL COMMENT 'Ngày cấp CCCD',
  `noi_cap` varchar(255) DEFAULT NULL COMMENT 'Nơi cấp CCCD',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái (1: Active, 0: Inactive)',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cccd` (`cccd`),
  KEY `idx_cccd` (`cccd`),
  KEY `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng dữ liệu CCCD giả lập';

-- Insert dữ liệu mẫu
INSERT INTO `cccd_data` (`cccd`, `ten`, `ngay_sinh`, `gioi_tinh`, `dia_chi`, `ngay_cap`, `noi_cap`, `trang_thai`) VALUES
('001234567890', 'Cao Dương Quốc Việt', '2003-03-22', 'Nam', 'An Giang', '2020-01-01', 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư', 1),
('001234567891', 'Ung Nguyễn Trường Thịnh', '2003-03-22', 'Nam', 'Hồ Chí Minh', '2020-06-15', 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư', 1),
('003456789012', 'Lê Văn C', '1988-12-10', 'Nữ', 'Đà Nẵng', '2019-03-20', 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư', 1);

