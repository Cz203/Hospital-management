-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 28, 2025 lúc 02:08 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `clinic-management`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bac_si`
--

CREATE TABLE `bac_si` (
  `id` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `chuyen_khoa_id` int(11) DEFAULT NULL,
  `chuyen_khoa` varchar(100) DEFAULT NULL,
  `so_giay_phep` varchar(50) DEFAULT NULL,
  `so_nam_kinh_nghiem` int(11) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hinh_anh` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bac_si`
--

INSERT INTO `bac_si` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `chuyen_khoa_id`, `chuyen_khoa`, `so_giay_phep`, `so_nam_kinh_nghiem`, `ngay_tao`, `ngay_cap_nhat`, `hinh_anh`) VALUES
(1, 'GSTS. Cao Việt', 'caoviet5.work@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0901000001', 1, 'Tim mạch', 'TM001', 15, '2025-08-12 09:06:03', '2025-11-25 09:21:19', 'uploads/bacsiviet.png'),
(48, 'Bác Sĩ X-Quang', 'xquang@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456789', 16, 'Chẩn đoán hình ảnh', 'XQ001', 10, '2025-09-26 07:16:15', '2025-11-27 08:38:02', NULL),
(49, 'Xét nghiệm', 'untt1608@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456789', 17, 'Xét nghiệm', NULL, 12, '2025-09-27 08:25:38', '2025-11-28 09:22:38', NULL),
(50, 'BS Siêu Âm', 'sieuam@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456789', 18, 'Siêu âm', '111', 20, '2025-10-10 19:52:54', '2025-11-27 08:38:47', NULL),
(51, 'GSTS Cường', 'Cuong@gmail.com', '$2y$10$KNTQBO3N/YQnSdptvUSwXOJaPXc5UKlkyPN//9Ci17ahxdnAsDQrS', '0913996110', 1, 'Tim mạch', '32424', 15, '2025-11-25 08:17:28', '2025-11-25 09:21:36', 'uploads/doctor_1764058648_8ec3caa3.png'),
(52, 'GSTS. bxcbxcvbcxvb', '', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456780', 6, 'Mắt', 'TM001', 15, '2025-08-12 09:06:03', '2025-11-25 09:21:47', 'uploads/bacsiviet.png'),
(53, 'Bác Sĩ X', 'xquang123123@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456780', 16, 'Chẩn đoán hình ảnh', 'XQ001', 10, '2025-09-26 07:16:15', '2025-11-27 08:38:02', NULL),
(54, 'BS Siêu', 'sbxcvbxcb@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456788', 18, 'Siêu âm', '111', 20, '2025-10-10 19:52:54', '2025-11-27 08:38:47', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bao_hiem_y_te`
--

CREATE TABLE `bao_hiem_y_te` (
  `id` int(11) NOT NULL,
  `ma_bao_hiem` varchar(20) DEFAULT NULL,
  `loai_the` varchar(100) DEFAULT NULL,
  `ten_chu_the` varchar(255) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nu','Khac') DEFAULT NULL,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_het_han` date DEFAULT NULL,
  `noi_cap` varchar(255) DEFAULT NULL,
  `trang_thai` enum('Hieu luc','Het han','Tam dung') DEFAULT 'Hieu luc',
  `huong_muc` decimal(5,2) DEFAULT 0.80 COMMENT 'Hướng mức giảm giá BHYT (ví dụ: 0.80 = 80%)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bao_hiem_y_te`
--

INSERT INTO `bao_hiem_y_te` (`id`, `ma_bao_hiem`, `loai_the`, `ten_chu_the`, `ngay_sinh`, `gioi_tinh`, `ngay_bat_dau`, `ngay_het_han`, `noi_cap`, `trang_thai`, `huong_muc`) VALUES
(16, '0791034567', 'BHYT', 'Hoàng Nguyễn Phương Trang', '2003-01-10', 'Nu', '2023-01-01', '2024-12-31', 'Bảo hiểm xã hội TP.HCM', 'Hieu luc', 0.50),
(17, '0791034568', 'BHYT', 'Việt', '2003-03-22', 'Nam', '2023-01-01', '2024-12-31', 'Bảo hiểm xã hội TP.HCM', 'Hieu luc', 0.70),
(18, '0791034569', 'BHYT', 'Cao Viet', '2003-03-20', 'Nam', '2023-01-01', '2024-12-31', 'Bảo hiểm xã hội TP.HCM', 'Hieu luc', 0.50);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `benh_nhan`
--

CREATE TABLE `benh_nhan` (
  `id` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `bao_hiem_y_te` varchar(50) DEFAULT NULL,
  `bao_hiem_y_te_id` int(11) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') DEFAULT NULL,
  `dia_chi` text DEFAULT NULL,
  `cccd` varchar(12) DEFAULT NULL COMMENT 'Căn cước công dân (12 số)',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ma_benh_nhan` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `benh_nhan`
--

INSERT INTO `benh_nhan` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `phone_verified`, `bao_hiem_y_te`, `bao_hiem_y_te_id`, `ngay_sinh`, `gioi_tinh`, `dia_chi`, `cccd`, `ngay_tao`, `ngay_cap_nhat`, `ma_benh_nhan`) VALUES
(44, 'Lê Văn C', 'viet@gmail.com', '$2y$10$AgUQWduL/nIsAiFPVlbejOTtrmCdSwyN967rVW/JLJ/9UtZHSmo9.', '84913992110', 1, NULL, NULL, '1988-12-10', '', 'Đà Nẵng', '003456789012', '2025-11-25 08:06:45', '2025-11-25 08:06:45', 'BN25112548'),
(45, 'Cao Dương Quốc Việt', 'caoduongvietquoc1@gmail.com', '$2y$10$r9U812xerLsSHNA0vvG20.f6soRoBJXB0eZrn6DCM7gczQNlu81F6', '84913998110', 1, NULL, NULL, '2003-03-22', 'Nam', 'An Giang', '001234567890', '2025-11-27 14:36:33', '2025-11-27 14:36:33', 'BN25112712');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bien_lai_vien_phi`
--

CREATE TABLE `bien_lai_vien_phi` (
  `id` int(11) NOT NULL,
  `ma_bien_lai` varchar(50) NOT NULL,
  `id_phieu_kham_benh` int(11) NOT NULL,
  `id_le_tan` int(11) DEFAULT NULL COMMENT 'ID lễ tân lập biên lai',
  `id_bac_si` int(11) DEFAULT NULL COMMENT 'ID bác sĩ khám bệnh',
  `tong_tien_co_ban` decimal(15,2) DEFAULT 0.00,
  `tong_quy_bhyt` decimal(15,2) DEFAULT 0.00,
  `tong_nguoi_benh` decimal(15,2) DEFAULT 0.00,
  `ngay_lap` datetime NOT NULL,
  `nguoi_lap` varchar(100) DEFAULT NULL,
  `trang_thai` enum('Chưa thanh toán','Đã thanh toán tiền mặt','Đã thanh toán chuyển khoản','Hủy') DEFAULT 'Chưa thanh toán',
  `ghi_chu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bien_lai_vien_phi`
--

INSERT INTO `bien_lai_vien_phi` (`id`, `ma_bien_lai`, `id_phieu_kham_benh`, `tong_tien_co_ban`, `tong_quy_bhyt`, `tong_nguoi_benh`, `ngay_lap`, `nguoi_lap`, `trang_thai`, `ghi_chu`, `created_at`, `updated_at`) VALUES
(16, 'BL202511271358', 40, 993000.00, 0.00, 993000.00, '2025-11-27 15:43:28', 'GSTS. Cao Việt', 'Đã thanh toán chuyển khoản', 'Biên lai tự động tạo từ hệ thống', '2025-11-27 08:43:28', '2025-11-27 08:45:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cccd_data`
--

CREATE TABLE `cccd_data` (
  `id` int(11) NOT NULL,
  `cccd` varchar(12) NOT NULL COMMENT 'Số CCCD (12 số)',
  `ten` varchar(255) NOT NULL COMMENT 'Họ và tên',
  `ngay_sinh` date NOT NULL COMMENT 'Ngày sinh',
  `gioi_tinh` enum('Nam','Nữ') NOT NULL COMMENT 'Giới tính',
  `dia_chi` varchar(255) DEFAULT NULL COMMENT 'Địa chỉ thường trú',
  `ngay_cap` date DEFAULT NULL COMMENT 'Ngày cấp CCCD',
  `noi_cap` varchar(255) DEFAULT NULL COMMENT 'Nơi cấp CCCD',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái (1: Active, 0: Inactive)',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng dữ liệu CCCD giả lập';

--
-- Đang đổ dữ liệu cho bảng `cccd_data`
--

INSERT INTO `cccd_data` (`id`, `cccd`, `ten`, `ngay_sinh`, `gioi_tinh`, `dia_chi`, `ngay_cap`, `noi_cap`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, '001234567890', 'Cao Dương Quốc Việt', '2003-03-22', 'Nam', 'An Giang', '2020-01-01', 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư', 1, '2025-11-09 06:13:23', '2025-11-09 06:13:23'),
(2, '001234567891', 'Ung Nguyễn Trường Thịnh', '2003-03-22', 'Nam', 'Hồ Chí Minh', '2020-06-15', 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư', 1, '2025-11-09 06:13:23', '2025-11-09 06:13:23'),
(3, '003456789012', 'Lê Văn C', '1988-12-10', 'Nữ', 'Đà Nẵng', '2019-03-20', 'Cục cảnh sát ĐKQL cư trú và DLQG về dân cư', 1, '2025-11-09 06:13:23', '2025-11-09 06:13:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_so_xet_nghiem`
--

CREATE TABLE `chi_so_xet_nghiem` (
  `id` int(11) NOT NULL,
  `xet_nghiem` varchar(255) NOT NULL COMMENT 'Tên xét nghiệm',
  `gia_tri_tham_chieu` text DEFAULT NULL COMMENT 'Giá trị tham chiếu',
  `don_vi` varchar(50) DEFAULT NULL COMMENT 'Đơn vị đo',
  `chi_so_tu` float DEFAULT NULL COMMENT 'Chỉ số từ (ngưỡng dưới)',
  `chi_so_den` float DEFAULT NULL COMMENT 'Chỉ số đến (ngưỡng trên)',
  `may_qtkt` varchar(100) DEFAULT NULL COMMENT 'Máy/Quy trình kỹ thuật',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `loai_form` enum('mau_toan_phan','mau_nuoc_tieu','other') DEFAULT 'other' COMMENT 'Loại form xét nghiệm',
  `thu_tu` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị trong form'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng chỉ số xét nghiệm';

--
-- Đang đổ dữ liệu cho bảng `chi_so_xet_nghiem`
--

INSERT INTO `chi_so_xet_nghiem` (`id`, `xet_nghiem`, `gia_tri_tham_chieu`, `don_vi`, `chi_so_tu`, `chi_so_den`, `may_qtkt`, `ngay_tao`, `ngay_cap_nhat`, `loai_form`, `thu_tu`) VALUES
(1, 'WBC *', '( 4.0 - 10.5 )', 'G/L', 4, 10.5, 'AU5800/QTKT.25', '2025-10-18 21:03:48', '2025-11-01 12:19:02', 'mau_toan_phan', 1),
(2, 'NEU', '( 1.8 - 7.0 )', 'G/L', 1.8, 7, 'AU5800/QTKT.25', '2025-10-18 21:03:48', '2025-11-01 09:33:36', 'mau_toan_phan', 2),
(3, 'NEU%', '( 45 - 70 )', '%', 45, 70, 'AU5800/QTKT.25', '2025-10-18 21:05:36', '2025-11-01 10:05:15', 'mau_toan_phan', 3),
(4, 'MONO%', '( 4.0 - 10 )', '%', 4, 10, 'AU5800/QTKT.25', '2025-10-18 21:05:36', '2025-11-01 10:05:52', 'mau_toan_phan', 7),
(5, 'RBC *', '( 4.2 - 5.4 )', 'L', 4.2, 5.4, '01/XN-QTXN.HS.01', '2025-10-18 21:07:19', '2025-11-01 10:17:56', 'mau_toan_phan', 14),
(8, 'MCV', '( 80.0 - 100.0 )', 'fL', 80, 100, '01/XN-QTXN.HS.01', '2025-10-18 21:08:17', '2025-11-01 11:28:03', 'mau_toan_phan', 17),
(9, 'MCH', '( 28.0 - 32.0 )', 'Pg', 28, 32, 'AU5800/QTKT.25', '2025-10-20 16:54:06', '2025-11-01 11:28:55', 'mau_toan_phan', 18),
(10, 'MCHC', '(320 - 360)', 'g/L', 320, 360, 'DXH 600', '2025-10-20 16:54:06', '2025-11-01 11:29:55', 'mau_toan_phan', 19),
(11, 'RDW', '( 11.0 - 14.0 )', '%', 11, 14, 'AU5800/QTKT.25', '2025-10-20 16:54:44', '2025-11-01 11:30:47', 'mau_toan_phan', 20),
(12, 'PLT *', '( 150 - 450 )', 'L', 150, 450, 'DXH 600', '2025-10-20 16:54:44', '2025-11-01 11:31:41', 'mau_toan_phan', 21),
(37, 'LYM', '( 0.8 - 4.0 )', 'G/L', 0.8, 4, 'DXH 600', '2025-11-01 09:36:00', '2025-11-01 09:36:00', 'mau_toan_phan', 4),
(38, 'LYM%', '( 20.0 - 40.0 )', '%', 20, 40, 'DXH 600', '2025-11-01 09:37:54', '2025-11-01 09:37:54', 'mau_toan_phan', 5),
(39, 'MONO', '( 0.2 - 1 )', 'G/L', 0.2, 1, 'DXH 600', '2025-11-01 10:04:16', '2025-11-01 10:04:30', 'mau_toan_phan', 6),
(40, 'EOS', '( 0.1 - 0.8 )', 'G/L', 0.1, 0.8, 'DXH 600', '2025-11-01 10:06:58', '2025-11-01 10:06:58', 'mau_toan_phan', 8),
(41, 'EOS%', '( 2.0 - 8.0 )', '%', 2, 8, 'DXH 600', '2025-11-01 10:08:05', '2025-11-01 10:08:05', 'mau_toan_phan', 9),
(42, 'BASO', '( 0.0 - 0.2 )', 'G/L', 0, 0.2, 'DXH 600', '2025-11-01 10:10:40', '2025-11-01 10:10:40', 'mau_toan_phan', 10),
(43, 'BASO%', '( 0.0 - 0.2 )', '%', 0, 0.2, 'DXH 600', '2025-11-01 10:10:40', '2025-11-01 10:10:40', 'mau_toan_phan', 11),
(44, 'NRBC', '( 0.0 - 0.02 )', 'G/L', 0, 0.02, 'DXH 600', '2025-11-01 10:16:21', '2025-11-01 10:16:21', 'mau_toan_phan', 12),
(45, 'NRBC%', '( 0.0 - 0.4 )', '%', 0, 0.4, 'DXH 600', '2025-11-01 10:16:21', '2025-11-01 10:16:21', 'mau_toan_phan', 13),
(46, 'HGB *', '( 130 - 160 )', 'g/L', 130, 160, 'DXH 600', '2025-11-01 10:20:28', '2025-11-01 10:20:28', 'mau_toan_phan', 15),
(47, 'HCT *', '( 40.0 - 47.0 )', '%', 40, 47, 'DXH 600', '2025-11-01 10:20:28', '2025-11-01 10:20:28', 'mau_toan_phan', 16),
(48, 'MPV', '( 5.0 - 8.0 )', 'fL', 5, 8, 'DXH 600', '2025-11-01 11:32:47', '2025-11-01 11:32:58', 'mau_toan_phan', 22),
(49, 'Ure / BUN *', '( 2.8 - 7.2 )', 'mmonL/L', 2.8, 7.2, 'AC680_3', '2025-11-01 11:39:34', '2025-11-01 11:39:34', 'mau_nuoc_tieu', 1),
(50, 'Glucose', '( 4.1 - 5.6 )', 'mmoL/L', 4.1, 5.6, 'AC680_3', '2025-11-01 11:39:34', '2025-11-01 11:39:34', 'mau_nuoc_tieu', 2),
(51, 'Creatimin *', '( 74 - 110 )', 'umol/L', 74, 110, 'AC680_3', '2025-11-01 11:43:13', '2025-11-01 11:43:13', 'mau_nuoc_tieu', 3),
(52, 'eGFR (Theo công thức MDRD)', '( >= 60 )', 'nL/phút/1.73m', 60, 10000, 'AC680_3', '2025-11-01 11:43:13', '2025-11-01 11:43:13', 'mau_nuoc_tieu', 4),
(53, 'AST(SGOT) *', '<50', 'U/L', 0, 49, 'AC680_3', '2025-11-01 11:45:27', '2025-11-01 11:45:27', 'mau_nuoc_tieu', 5),
(54, 'ALT(SGPT)*', '<50', 'U/L', 0, 49, 'AC680_3', '2025-11-01 11:45:27', '2025-11-01 11:45:27', 'mau_nuoc_tieu', 6),
(55, 'Cholesterol', '( <5.2 )', 'mmol/L', 0, 5.1, 'AC680_3', '2025-11-01 11:48:09', '2025-11-01 11:48:09', 'mau_nuoc_tieu', 7),
(56, 'Triglyxerid', '( < 1.7 )', 'mmol/L', 0, 1.6, 'mmol/L', '2025-11-01 11:48:09', '2025-11-01 11:48:09', 'mau_nuoc_tieu', 8),
(57, 'HDL-C', '( > 0.90 )', 'mmol/L', 0.91, 99999, 'mmol/L', '2025-11-01 11:51:39', '2025-11-01 11:51:39', 'mau_nuoc_tieu', 9),
(58, 'LDL-C', '( < 3.3 )', 'mmol/L', 0, 3.2, 'mmol/L', '2025-11-01 11:51:39', '2025-11-01 11:51:39', 'mau_nuoc_tieu', 10),
(59, 'Uric Acid', '( 208.0 - 428.0 )', 'umol/L', 208, 428, 'mmol/L', '2025-11-01 11:53:32', '2025-11-01 11:53:32', 'mau_nuoc_tieu', 11),
(60, 'GGT', '( < 55 )', 'U/L', 0, 54, 'mmol/L', '2025-11-01 11:53:32', '2025-11-01 11:53:32', 'mau_nuoc_tieu', 12),
(61, 'CYFRA 21-1', '( 0.100 - 3.30 )', 'ng/mL', 0.1, 3.3, 'AXH 600', '2025-11-01 11:56:30', '2025-11-01 11:56:30', 'mau_nuoc_tieu', 13),
(62, 'AFP', '( 0 - 10.0 )', 'ng/mL', 0, 10, 'AXH 600', '2025-11-01 11:56:30', '2025-11-01 11:56:30', 'mau_nuoc_tieu', 14),
(63, 'Ns-Glucose', 'Âm tính', NULL, NULL, NULL, 'UC3500', '2025-11-01 11:59:48', '2025-11-01 11:59:48', 'mau_nuoc_tieu', 15),
(64, 'Ns-Bilirubin', 'Âm tính', NULL, NULL, NULL, 'UC3500', '2025-11-01 11:59:48', '2025-11-01 11:59:48', 'mau_nuoc_tieu', 16),
(65, 'Nt-Xetonic', 'Âm tính', NULL, NULL, NULL, 'UC3500', '2025-11-01 12:02:00', '2025-11-01 12:02:00', 'mau_nuoc_tieu', 17),
(66, 'Nt-Tỷ trọng', '( 1.010 - 1.030 )', NULL, 1.01, 1.03, 'UC3500', '2025-11-01 12:02:00', '2025-11-01 12:02:00', 'mau_nuoc_tieu', 18),
(67, 'Nt-Độ PH', '( 4.5 - 7.5 )', NULL, 4.5, 7.5, 'UC3500', '2025-11-01 12:03:52', '2025-11-01 12:03:52', 'mau_nuoc_tieu', 19),
(68, 'Nt-Protein', '( 0.0 - 0.02 )', 'g/L', 0, 0.02, 'UC3500', '2025-11-01 12:03:52', '2025-11-01 12:03:52', 'mau_nuoc_tieu', 20),
(69, 'Nt-Urobilimogen', '( 0.1 - 1 )', 'mg/dL', 0.1, 1, 'UC3500', '2025-11-01 12:06:52', '2025-11-01 12:06:52', 'mau_nuoc_tieu', 21),
(70, 'NT-Nitrit', 'Âm tính', NULL, NULL, NULL, 'UC3500', '2025-11-01 12:06:52', '2025-11-01 12:06:52', 'mau_nuoc_tieu', 22),
(71, 'Nt-Hồng cầu', 'Âm tính', NULL, NULL, NULL, NULL, '2025-11-01 12:07:46', '2025-11-01 12:07:46', 'mau_nuoc_tieu', 23),
(72, 'Nt-Bạch cầu', 'Âm tính', NULL, NULL, NULL, NULL, '2025-11-01 12:07:46', '2025-11-01 12:07:46', 'mau_nuoc_tieu', 24);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_bien_lai`
--

CREATE TABLE `chi_tiet_bien_lai` (
  `id` int(11) NOT NULL,
  `id_bien_lai` int(11) NOT NULL,
  `loai_dich_vu` enum('Kham benh','Xet nghiem','Sieu am','X-Quang','Thuoc') NOT NULL,
  `ten_dich_vu` varchar(200) NOT NULL,
  `so_luong` int(11) DEFAULT 1,
  `don_gia` decimal(15,2) NOT NULL,
  `thanh_tien` decimal(15,2) NOT NULL,
  `quy_bhyt` decimal(15,2) DEFAULT 0.00,
  `nguoi_benh` decimal(15,2) DEFAULT 0.00,
  `bao_hiem` tinyint(1) DEFAULT 0,
  `ghi_chu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_bien_lai`
--

INSERT INTO `chi_tiet_bien_lai` (`id`, `id_bien_lai`, `loai_dich_vu`, `ten_dich_vu`, `so_luong`, `don_gia`, `thanh_tien`, `quy_bhyt`, `nguoi_benh`, `bao_hiem`, `ghi_chu`, `created_at`) VALUES
(143, 16, 'Kham benh', 'Khám bệnh', 1, 200000.00, 200000.00, 0.00, 200000.00, 0, '', '2025-11-27 08:43:28'),
(144, 16, 'Xet nghiem', 'Xét nghiệm máu toàn phần', 1, 50000.00, 50000.00, 0.00, 50000.00, 0, '', '2025-11-27 08:43:28'),
(145, 16, 'Sieu am', 'Siêu âm ổ bụng tổng quát', 1, 250000.00, 250000.00, 0.00, 250000.00, 0, '', '2025-11-27 08:43:28'),
(146, 16, 'X-Quang', 'Xương cánh tay phải', 1, 120000.00, 120000.00, 0.00, 120000.00, 0, '', '2025-11-27 08:43:28'),
(147, 16, 'Thuoc', 'Amoxicillin 500mg', 10, 15000.00, 150000.00, 0.00, 150000.00, 0, '', '2025-11-27 08:43:28'),
(148, 16, 'Thuoc', 'Aspirin 100mg', 11, 8000.00, 88000.00, 0.00, 88000.00, 0, '', '2025-11-27 08:43:28'),
(149, 16, 'Thuoc', 'Salbutamol 100mcg', 3, 45000.00, 135000.00, 0.00, 135000.00, 0, '', '2025-11-27 08:43:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_don_thuoc`
--

CREATE TABLE `chi_tiet_don_thuoc` (
  `MaChiTiet` int(11) NOT NULL,
  `MaDonThuoc` varchar(20) NOT NULL COMMENT 'FK -> don_thuoc.MaDonThuoc',
  `MaThuoc` varchar(10) NOT NULL COMMENT 'FK -> thuoc.MaThuoc',
  `TenThuoc` varchar(100) DEFAULT NULL,
  `HoatChat` varchar(100) DEFAULT NULL,
  `DonViTinh` varchar(20) DEFAULT NULL,
  `SoLuong` int(11) NOT NULL DEFAULT 1,
  `LieuDung` varchar(255) DEFAULT NULL,
  `GhiChu` varchar(255) DEFAULT NULL,
  `NgayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `NgayCapNhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `so_ngay` int(11) NOT NULL DEFAULT 1 COMMENT 'Số ngày uống',
  `ghi_chu_cach_dung` varchar(255) DEFAULT NULL COMMENT 'Ghi chú cách dùng chi tiết',
  `vien_sang` decimal(6,2) NOT NULL DEFAULT 0.00 COMMENT 'Số viên buổi sáng',
  `vien_trua` decimal(6,2) NOT NULL DEFAULT 0.00 COMMENT 'Số viên buổi trưa',
  `vien_chieu` decimal(6,2) NOT NULL DEFAULT 0.00 COMMENT 'Số viên buổi chiều',
  `vien_toi` decimal(6,2) NOT NULL DEFAULT 0.00 COMMENT 'Số viên buổi tối',
  `sang_bua` enum('none','before','after') NOT NULL DEFAULT 'none' COMMENT 'Bữa ăn buổi sáng',
  `trua_bua` enum('none','before','after') NOT NULL DEFAULT 'none' COMMENT 'Bữa ăn buổi trưa',
  `chieu_bua` enum('none','before','after') NOT NULL DEFAULT 'none' COMMENT 'Bữa ăn buổi chiều',
  `toi_bua` enum('none','before','after') NOT NULL DEFAULT 'none' COMMENT 'Bữa ăn buổi tối',
  `so_luong_tinh` int(11) GENERATED ALWAYS AS (greatest(1,round(`so_ngay` * (`vien_sang` + `vien_trua` + `vien_chieu` + `vien_toi`),0))) STORED COMMENT 'SL tự tính = so_ngay * (tổng viên/ngày), tối thiểu 1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_don_thuoc`
--

INSERT INTO `chi_tiet_don_thuoc` (`MaChiTiet`, `MaDonThuoc`, `MaThuoc`, `TenThuoc`, `HoatChat`, `DonViTinh`, `SoLuong`, `LieuDung`, `GhiChu`, `NgayTao`, `NgayCapNhat`, `so_ngay`, `ghi_chu_cach_dung`, `vien_sang`, `vien_trua`, `vien_chieu`, `vien_toi`, `sang_bua`, `trua_bua`, `chieu_bua`, `toi_bua`) VALUES
(102, 'P864208-660', 'T001', 'Amoxicillin 500mg', 'Amoxicillin', 'Viên', 10, '', '', '2025-11-27 15:42:49', '2025-11-27 15:42:49', 1, '', 5.00, 5.00, 0.00, 0.00, 'before', 'after', 'none', 'none'),
(103, 'P864208-660', 'T003', 'Aspirin 100mg', 'Acetylsalicylic acid', 'Viên', 11, '', '', '2025-11-27 15:42:49', '2025-11-27 15:42:49', 1, '', 4.00, 5.00, 2.00, 0.00, 'before', 'after', 'before', 'none'),
(104, 'P864208-660', 'T005', 'Salbutamol 100mcg', 'Salbutamol', 'Bình', 3, '1-2 nhát x 3-4 lần/ngày', '', '2025-11-27 15:42:49', '2025-11-27 15:42:49', 1, '1-2 nhát x 3-4 lần/ngày', 0.00, 0.00, 0.00, 0.00, 'none', 'none', 'none', 'none');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_ket_qua_xet_nghiem`
--

CREATE TABLE `chi_tiet_ket_qua_xet_nghiem` (
  `id` int(11) NOT NULL,
  `id_phieu_tra_ket_qua` int(11) NOT NULL COMMENT 'ID phiếu trả kết quả',
  `stt` int(11) NOT NULL COMMENT 'Số thứ tự',
  `ten_xet_nghiem` varchar(255) NOT NULL COMMENT 'Tên xét nghiệm',
  `gia_tri_tham_chieu` text DEFAULT NULL COMMENT 'Giá trị tham chiếu',
  `ket_qua` text NOT NULL COMMENT 'Kết quả xét nghiệm',
  `don_vi` varchar(50) DEFAULT NULL COMMENT 'Đơn vị đo',
  `may_qtkt` varchar(100) DEFAULT NULL COMMENT 'Máy/Quy trình kỹ thuật',
  `ghi_chu` text DEFAULT NULL COMMENT 'Ghi chú riêng cho từng xét nghiệm',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng chi tiết kết quả xét nghiệm';

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_ket_qua_xet_nghiem`
--

INSERT INTO `chi_tiet_ket_qua_xet_nghiem` (`id`, `id_phieu_tra_ket_qua`, `stt`, `ten_xet_nghiem`, `gia_tri_tham_chieu`, `ket_qua`, `don_vi`, `may_qtkt`, `ghi_chu`, `ngay_tao`) VALUES
(193, 31, 1, 'WBC *', '( 4.0 - 10.5 )', '5', 'G/L', 'AU5800/QTKT.25', NULL, '2025-11-27 08:36:54'),
(194, 31, 2, 'NEU', '( 1.8 - 7.0 )', '4', 'G/L', 'AU5800/QTKT.25', NULL, '2025-11-27 08:36:54'),
(195, 31, 3, 'NEU%', '( 45 - 70 )', '5', '%', 'AU5800/QTKT.25', NULL, '2025-11-27 08:36:54'),
(196, 31, 4, 'LYM', '( 0.8 - 4.0 )', '5', 'G/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(197, 31, 5, 'LYM%', '( 20.0 - 40.0 )', '5', '%', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(198, 31, 6, 'MONO', '( 0.2 - 1 )', '5', 'G/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(199, 31, 7, 'MONO%', '( 4.0 - 10 )', '5', '%', 'AU5800/QTKT.25', NULL, '2025-11-27 08:36:54'),
(200, 31, 8, 'EOS', '( 0.1 - 0.8 )', '5', 'G/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(201, 31, 9, 'EOS%', '( 2.0 - 8.0 )', '5', '%', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(202, 31, 10, 'BASO', '( 0.0 - 0.2 )', '5', 'G/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(203, 31, 11, 'BASO%', '( 0.0 - 0.2 )', '5', '%', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(204, 31, 12, 'NRBC', '( 0.0 - 0.02 )', '5', 'G/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(205, 31, 13, 'NRBC%', '( 0.0 - 0.4 )', '5', '%', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(206, 31, 14, 'RBC *', '( 4.2 - 5.4 )', '5', 'L', '01/XN-QTXN.HS.01', NULL, '2025-11-27 08:36:54'),
(207, 31, 15, 'HGB *', '( 130 - 160 )', '5', 'g/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(208, 31, 16, 'HCT *', '( 40.0 - 47.0 )', '5', '%', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(209, 31, 17, 'MCV', '( 80.0 - 100.0 )', '5', 'fL', '01/XN-QTXN.HS.01', NULL, '2025-11-27 08:36:54'),
(210, 31, 18, 'MCH', '( 28.0 - 32.0 )', '4', 'Pg', 'AU5800/QTKT.25', NULL, '2025-11-27 08:36:54'),
(211, 31, 19, 'MCHC', '(320 - 360)', '2', 'g/L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(212, 31, 20, 'RDW', '( 11.0 - 14.0 )', '5', '%', 'AU5800/QTKT.25', NULL, '2025-11-27 08:36:54'),
(213, 31, 21, 'PLT *', '( 150 - 450 )', '2', 'L', 'DXH 600', NULL, '2025-11-27 08:36:54'),
(214, 31, 22, 'MPV', '( 5.0 - 8.0 )', '5', 'fL', 'DXH 600', NULL, '2025-11-27 08:36:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chuyen_khoa`
--

CREATE TABLE `chuyen_khoa` (
  `id` int(11) NOT NULL,
  `ten` varchar(100) NOT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('active','inactive') DEFAULT 'active',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chuyen_khoa`
--

INSERT INTO `chuyen_khoa` (`id`, `ten`, `slug`, `mo_ta`, `icon`, `thu_tu`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Tim mạch', 'tim-mach', 'Khám và điều trị bệnh lý tim mạch', 'icofont-heart-beat-alt', 60, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(2, 'Thần kinh', 'than-kinh', 'Bệnh lý hệ thần kinh trung ương và ngoại biên', 'icofont-brain-alt', 70, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(3, 'Nhi khoa', 'nhi-khoa', 'Chăm sóc sức khỏe trẻ em', 'icofont-baby', 80, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(4, 'Sản phụ khoa', 'san-phu-khoa', 'Chăm sóc sức khỏe phụ nữ và thai sản', 'icofont-dna-alt-1', 30, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(5, 'Da liễu', 'da-lieu', 'Khám và điều trị các bệnh lý da', 'icofont-medical-sign-alt', 110, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(6, 'Mắt', 'mat', 'Khám và điều trị các bệnh lý mắt', 'icofont-eye', 90, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(7, 'Tai mũi họng', 'tai-mui-hong', 'Bệnh lý tai, mũi, họng', 'icofont-earphone', 100, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(9, 'Chấn thương chỉnh hình', 'chan-thuong-chinh-hinh', 'v', 'icofont-bone', 170, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(10, 'Ung bướu', 'ung-buou', 'Chẩn đoán và điều trị các bệnh lý ung thư', 'icofont-hospital', 20, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(11, 'Nội tiết', 'noi-tiet', 'Bệnh lý nội tiết và chuyển hóa', 'icofont-pills', 150, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(12, 'Tiêu hóa', 'tieu-hoa', 'Bệnh lý đường tiêu hóa', 'icofont-medicine', 160, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(13, 'Nội tổng quát', 'noi-tong-quat', 'Khám và điều trị các bệnh lý nội khoa tổng quát', 'icofont-stethoscope', 10, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(14, 'Nam khoa', 'nam-khoa', 'Sức khỏe nam giới', 'icofont-doctor-alt', 130, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(15, 'Truyền nhiễm', 'truyen-nhiem', 'Bệnh truyền nhiễm và kiểm soát nhiễm khuẩn', 'icofont-injection-syringe', 140, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(16, 'Chẩn đoán hình ảnh', 'chuan-doan-hinh-anh', 'X-quang, CT, MRI, siêu âm', 'icofont-medical-sign-alt', 40, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(17, 'Xét nghiệm', 'xet-nghiem', 'Xét nghiệm huyết học, sinh hóa, vi sinh', 'icofont-laboratory', 50, 'active', '2025-09-21 16:53:16', '2025-09-21 18:38:22'),
(18, 'Khoa sản', 'sieu-am', 'siêu âm thai', 'icofont-medical-sign-alt', 0, 'active', '2025-10-09 07:16:16', '2025-10-11 03:45:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dich_vu_kham`
--

CREATE TABLE `dich_vu_kham` (
  `id` int(11) NOT NULL,
  `ten_dich_vu` varchar(255) NOT NULL COMMENT 'Tên dịch vụ khám bệnh',
  `don_gia` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Đơn giá dịch vụ (VND)',
  `mo_ta` text DEFAULT NULL COMMENT 'Mô tả chi tiết dịch vụ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `giam_phan_tram_bhyt` decimal(3,2) DEFAULT 0.80 COMMENT 'Phần trăm BHYT chi trả (ví dụ: 0.8 = 80%, 0.7 = 70%)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý dịch vụ khám bệnh và đơn giá';

--
-- Đang đổ dữ liệu cho bảng `dich_vu_kham`
--

INSERT INTO `dich_vu_kham` (`id`, `ten_dich_vu`, `don_gia`, `mo_ta`, `created_at`, `updated_at`, `giam_phan_tram_bhyt`) VALUES
(1, 'Khám bệnh', 200000.00, 'Tiền khám bệnh', '2025-10-23 08:02:42', '2025-10-23 08:10:50', 0.80);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_thuoc`
--

CREATE TABLE `don_thuoc` (
  `MaDonThuoc` varchar(20) NOT NULL COMMENT 'Prescription code',
  `MaBenhNhan` int(11) NOT NULL COMMENT 'FK -> benh_nhan.id',
  `MaBacSi` int(11) NOT NULL COMMENT 'FK -> bac_si.id',
  `id_phieu_kham_benh` int(11) NOT NULL COMMENT 'FK -> phieu_kham_benh.id',
  `NgayKe` date NOT NULL,
  `ChanDoan` varchar(255) DEFAULT NULL,
  `GhiChu` varchar(255) DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT 'nhap' COMMENT 'nhap/hoan_tat/huy',
  `NgayTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `don_thuoc`
--

INSERT INTO `don_thuoc` (`MaDonThuoc`, `MaBenhNhan`, `MaBacSi`, `id_phieu_kham_benh`, `NgayKe`, `ChanDoan`, `GhiChu`, `TrangThai`, `NgayTao`) VALUES
('P864208-660', 44, 1, 40, '2025-11-27', 'Đau bụng ', 'Nhớ uống ', 'Chưa lấy thuốc', '2025-11-27 15:41:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ho_so_benh_an`
--

CREATE TABLE `ho_so_benh_an` (
  `id` int(11) NOT NULL,
  `benh_nhan_id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `lich_hen_id` int(11) DEFAULT NULL,
  `chan_doan` text DEFAULT NULL,
  `phac_do_dieu_tri` text DEFAULT NULL,
  `don_thuoc` text DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_sieu_am`
--

CREATE TABLE `ket_qua_sieu_am` (
  `id` int(11) NOT NULL,
  `id_phieu_yeu_cau_sieu_am` int(11) NOT NULL,
  `ket_qua_khao_sat` text DEFAULT NULL,
  `ket_luan` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bac_si_sieu_am` varchar(255) DEFAULT NULL COMMENT 'Tên bác sĩ siêu âm thực hiện và trả kết quả'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_xquang`
--

CREATE TABLE `ket_qua_xquang` (
  `id` int(11) NOT NULL,
  `id_phieu_chup_xquang` int(11) NOT NULL,
  `chuan_doan` text DEFAULT NULL,
  `noi_dung` text DEFAULT NULL,
  `ket_luan` text DEFAULT NULL,
  `bac_si_xquang` varchar(255) DEFAULT NULL,
  `ngay_doc` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_xquang_hinh_anh`
--

CREATE TABLE `ket_qua_xquang_hinh_anh` (
  `id` int(11) NOT NULL,
  `ket_qua_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `ngay_tai` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `le_tan`
--

CREATE TABLE `le_tan` (
  `id` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `le_tan`
--

INSERT INTO `le_tan` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Lễ tân A\r\n', '', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456789', '2025-10-20 21:03:32', '2025-11-28 10:37:45'),
(2, 'Lễ tân B', '', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0123456788', '2025-10-20 21:03:32', '2025-11-28 10:38:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_hen`
--

CREATE TABLE `lich_hen` (
  `id` int(11) NOT NULL,
  `benh_nhan_id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `ngay_hen` date NOT NULL,
  `gio_hen` time NOT NULL,
  `ly_do` text DEFAULT NULL,
  `loai_lich` enum('Tư vấn','Trực tiếp','Tại nhà','Tại viện') NOT NULL DEFAULT 'Trực tiếp',
  `dia_chi_kham` varchar(255) DEFAULT NULL,
  `link_tu_van` varchar(255) DEFAULT NULL,
  `trang_thai` enum('Chờ xác nhận','Đã xác nhận','Đang khám','Hoàn thành','Đã khám xong','hủy') DEFAULT 'Chờ xác nhận',
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_hen`
--

INSERT INTO `lich_hen` (`id`, `benh_nhan_id`, `bac_si_id`, `ngay_hen`, `gio_hen`, `ly_do`, `loai_lich`, `dia_chi_kham`, `link_tu_van`, `trang_thai`, `ghi_chu`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(209, 45, 1, '2025-11-28', '16:40:00', NULL, 'Tại viện', NULL, NULL, 'Đang khám', NULL, '2025-11-28 09:31:15', '2025-11-28 09:34:04'),
(210, 45, 51, '2025-11-28', '16:40:00', NULL, 'Tại viện', NULL, NULL, 'Đã xác nhận', 'Lễ tân phát số walk-in', '2025-11-28 09:32:15', '2025-11-28 09:32:15'),
(211, 45, 1, '2025-11-28', '16:50:00', NULL, 'Tại viện', NULL, NULL, 'Đang khám', NULL, '2025-11-28 09:32:21', '2025-11-28 09:51:54'),
(212, 45, 51, '2025-11-28', '16:50:00', NULL, 'Tại viện', NULL, NULL, 'Đã xác nhận', 'Lễ tân phát số walk-in', '2025-11-28 09:32:26', '2025-11-28 09:32:26'),
(213, 45, 1, '2025-11-28', '17:00:00', NULL, 'Tại viện', NULL, NULL, 'Đã xác nhận', 'Lễ tân phát số walk-in', '2025-11-28 09:32:30', '2025-11-28 09:32:30'),
(214, 45, 51, '2025-11-28', '17:00:00', NULL, 'Tại viện', NULL, NULL, 'Đã xác nhận', 'Lễ tân phát số walk-in', '2025-11-28 09:32:35', '2025-11-28 09:32:35'),
(215, 45, 48, '2025-11-28', '16:40:00', 'Chụp X-Quang', 'Tại viện', NULL, NULL, 'Đã xác nhận', 'Tự động tạo từ Chụp X-Quang cho phiếu khám #41', '2025-11-28 09:34:33', '2025-11-28 09:34:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_lam_viec`
--

CREATE TABLE `lich_lam_viec` (
  `id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `thu_trong_tuan` enum('Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','Chủ nhật') NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `loai_ca` enum('Ca sáng','Ca chiều','Ca tối') NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` enum('active','inactive') DEFAULT 'active',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_lam_viec`
--

INSERT INTO `lich_lam_viec` (`id`, `bac_si_id`, `thu_trong_tuan`, `gio_bat_dau`, `gio_ket_thuc`, `loai_ca`, `ghi_chu`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(31, 1, 'Thứ 6', '13:00:00', '21:00:00', 'Ca chiều', '', 'active', '2025-11-28 08:37:58', '2025-11-28 08:37:58'),
(32, 1, 'Thứ 6', '07:00:00', '11:30:00', 'Ca sáng', '', 'active', '2025-11-28 08:39:05', '2025-11-28 08:39:05'),
(35, 51, 'Thứ 6', '13:00:00', '21:00:00', 'Ca chiều', '', 'active', '2025-11-28 09:27:08', '2025-11-28 09:32:07'),
(37, 48, 'Thứ 6', '07:00:00', '11:30:00', 'Ca sáng', '', 'active', '2025-11-28 10:09:00', '2025-11-28 10:09:00'),
(38, 53, 'Thứ 6', '07:00:00', '11:30:00', 'Ca sáng', '', 'active', '2025-11-28 10:10:31', '2025-11-28 10:10:31'),
(39, 53, 'Thứ 6', '13:00:00', '21:00:00', 'Ca chiều', '', 'active', '2025-11-28 10:17:03', '2025-11-28 10:17:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_lam_viec_le_tan`
--

CREATE TABLE `lich_lam_viec_le_tan` (
  `id` int(11) NOT NULL,
  `letan_id` int(11) NOT NULL,
  `thu_trong_tuan` enum('Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','Chủ nhật') NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `loai_ca` enum('Ca sáng','Ca chiều') NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` enum('active','inactive') DEFAULT 'active',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_lam_viec_le_tan`
--

INSERT INTO `lich_lam_viec_le_tan` (`id`, `letan_id`, `thu_trong_tuan`, `gio_bat_dau`, `gio_ket_thuc`, `loai_ca`, `ghi_chu`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(3, 1, 'Thứ 3', '07:00:00', '11:30:00', 'Ca sáng', '', 'active', '2025-11-28 13:03:44', '2025-11-28 13:03:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_lam_viec_le_tan_ngoai_le`
--

CREATE TABLE `lich_lam_viec_le_tan_ngoai_le` (
  `id` int(11) NOT NULL,
  `letan_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `ngay` date NOT NULL,
  `action` enum('cancel','modify') NOT NULL,
  `gio_bat_dau` time DEFAULT NULL,
  `gio_ket_thuc` time DEFAULT NULL,
  `loai_ca` varchar(50) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_lam_viec_ngoai_le`
--

CREATE TABLE `lich_lam_viec_ngoai_le` (
  `id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `ngay` date NOT NULL,
  `action` enum('cancel','modify') NOT NULL,
  `gio_bat_dau` time DEFAULT NULL,
  `gio_ket_thuc` time DEFAULT NULL,
  `loai_ca` varchar(50) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_lam_viec_ngoai_le`
--

INSERT INTO `lich_lam_viec_ngoai_le` (`id`, `bac_si_id`, `schedule_id`, `ngay`, `action`, `gio_bat_dau`, `gio_ket_thuc`, `loai_ca`, `ghi_chu`, `created_at`) VALUES
(1, 1, 17, '2025-09-24', 'cancel', NULL, NULL, NULL, 'c c', '2025-09-23 09:52:44'),
(2, 1, 19, '2025-10-03', 'modify', '18:00:00', '23:59:00', 'Ca tối', 'xc', '2025-09-23 09:57:19'),
(3, 1, 19, '2025-09-26', 'cancel', NULL, NULL, NULL, 'thích thì nghỉ', '2025-09-23 09:58:22'),
(4, 1, 16, '2025-09-30', 'cancel', NULL, NULL, NULL, 'c', '2025-09-23 10:13:41'),
(5, 1, 21, '2025-10-05', 'modify', '06:00:00', '12:00:00', 'Ca sáng', 'cv', '2025-09-23 11:38:05'),
(6, 1, 23, '2025-09-23', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(7, 1, 23, '2025-09-24', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(8, 1, 23, '2025-09-25', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(9, 1, 23, '2025-09-26', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(10, 1, 23, '2025-09-27', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(11, 1, 23, '2025-09-28', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(12, 1, 23, '2025-09-29', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(13, 1, 23, '2025-09-30', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 19:58:25'),
(14, 1, 24, '2025-09-23', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(15, 1, 24, '2025-09-24', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(16, 1, 24, '2025-09-25', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(17, 1, 24, '2025-09-26', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(18, 1, 24, '2025-09-27', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(19, 1, 24, '2025-09-28', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(20, 1, 24, '2025-09-29', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(21, 1, 24, '2025-09-30', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:03'),
(22, 1, 25, '2025-09-23', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(23, 1, 25, '2025-09-24', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(24, 1, 25, '2025-09-25', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(25, 1, 25, '2025-09-26', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(26, 1, 25, '2025-09-27', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(27, 1, 25, '2025-09-28', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(28, 1, 25, '2025-09-29', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(29, 1, 25, '2025-09-30', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-09-24 20:00:18'),
(30, 1, 28, '2025-11-23', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(31, 1, 28, '2025-11-24', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(32, 1, 28, '2025-11-25', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(33, 1, 28, '2025-11-26', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(34, 1, 28, '2025-11-27', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(35, 1, 28, '2025-11-28', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(36, 1, 28, '2025-11-29', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(37, 1, 28, '2025-11-30', 'cancel', NULL, NULL, NULL, 'Khóa cuối tháng (23→hết tháng) theo chính sách 23-25', '2025-11-25 08:40:49'),
(38, 1, 16, '2025-12-02', 'cancel', NULL, NULL, NULL, 'xcvbxc', '2025-11-25 08:44:26'),
(39, 1, 28, '2025-12-02', 'cancel', NULL, NULL, NULL, 'cvb', '2025-11-25 08:46:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_boc_so`
--

CREATE TABLE `phieu_boc_so` (
  `id` int(11) NOT NULL,
  `ngay` date NOT NULL,
  `so_thu_tu` int(11) NOT NULL,
  `benh_nhan_id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `lich_hen_id` int(11) DEFAULT NULL,
  `trang_thai` enum('cho','dang_goi','dang_kham','bo_lo','xong','huy') DEFAULT 'cho',
  `uu_tien` tinyint(4) DEFAULT 0,
  `quay` varchar(50) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `thoi_gian_goi` datetime DEFAULT NULL,
  `thoi_gian_bat_dau` datetime DEFAULT NULL,
  `thoi_gian_ket_thuc` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_boc_so`
--

INSERT INTO `phieu_boc_so` (`id`, `ngay`, `so_thu_tu`, `benh_nhan_id`, `bac_si_id`, `lich_hen_id`, `trang_thai`, `uu_tien`, `quay`, `ghi_chu`, `thoi_gian_goi`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `created_at`, `updated_at`) VALUES
(83, '2025-11-28', 1, 45, 1, 209, 'dang_kham', 0, NULL, NULL, NULL, '2025-11-28 16:34:04', NULL, '2025-11-28 09:31:15', '2025-11-28 09:34:04'),
(84, '2025-11-28', 2, 45, 51, 210, 'cho', 0, NULL, NULL, NULL, NULL, NULL, '2025-11-28 09:32:15', '2025-11-28 09:32:15'),
(85, '2025-11-28', 3, 45, 1, 211, 'dang_kham', 0, NULL, NULL, NULL, '2025-11-28 16:51:09', NULL, '2025-11-28 09:32:21', '2025-11-28 09:51:09'),
(86, '2025-11-28', 4, 45, 51, 212, 'cho', 0, NULL, NULL, NULL, NULL, NULL, '2025-11-28 09:32:26', '2025-11-28 09:32:26'),
(87, '2025-11-28', 5, 45, 1, 213, 'cho', 0, NULL, NULL, NULL, NULL, NULL, '2025-11-28 09:32:30', '2025-11-28 09:32:30'),
(88, '2025-11-28', 6, 45, 51, 214, 'cho', 0, NULL, NULL, NULL, NULL, NULL, '2025-11-28 09:32:35', '2025-11-28 09:32:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_chup_xquang`
--

CREATE TABLE `phieu_chup_xquang` (
  `id` int(11) NOT NULL,
  `id_phieu_kham_benh` int(11) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT '0777871608',
  `quan` varchar(100) DEFAULT 'Gò Vấp',
  `yeu_cau_chup` text NOT NULL,
  `bac_si_kham` varchar(255) NOT NULL,
  `trang_thai` enum('Đã yêu cầu','Hoàn thành') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đã yêu cầu',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `chan_doan_vao_vien` text DEFAULT NULL COMMENT 'Chuẩn đoán nhập vào',
  `bac_si_xquang_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_chup_xquang`
--

INSERT INTO `phieu_chup_xquang` (`id`, `id_phieu_kham_benh`, `so_dien_thoai`, `quan`, `yeu_cau_chup`, `bac_si_kham`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`, `chan_doan_vao_vien`, `bac_si_xquang_id`) VALUES
(45, 42, '0777871608', 'Gò Vấp', 'Xương cánh tay trái', 'GSTS. Cao Việt', 'Đã yêu cầu', '2025-11-28 10:23:18', '2025-11-28 10:23:18', 'zxcvzxcv', 53);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_kham_benh`
--

CREATE TABLE `phieu_kham_benh` (
  `id` int(11) NOT NULL,
  `id_lich_hen` int(11) DEFAULT NULL COMMENT 'ID lịch hẹn khám bệnh',
  `benh_nhan_id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `so_y_te` varchar(255) DEFAULT NULL,
  `benh_vien` varchar(255) DEFAULT NULL,
  `buong_kham` varchar(100) DEFAULT NULL,
  `ho_ten` varchar(255) DEFAULT NULL,
  `ngay_sinh` tinyint(3) UNSIGNED DEFAULT NULL,
  `thang_sinh` tinyint(3) UNSIGNED DEFAULT NULL,
  `nam_sinh` smallint(5) UNSIGNED DEFAULT NULL,
  `tuoi` tinyint(3) UNSIGNED DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nu','Khac') DEFAULT NULL,
  `nghe_nghiep` varchar(255) DEFAULT NULL,
  `dan_toc` varchar(100) DEFAULT NULL,
  `ngoai_kieu` varchar(100) DEFAULT NULL,
  `noi_lam_viec` varchar(255) DEFAULT NULL,
  `dia_chi` varchar(500) DEFAULT NULL,
  `doi_tuong_bhyt` tinyint(1) NOT NULL DEFAULT 0,
  `doi_tuong_thu_phi` tinyint(1) NOT NULL DEFAULT 0,
  `doi_tuong_mien` tinyint(1) NOT NULL DEFAULT 0,
  `doi_tuong_khac` tinyint(1) NOT NULL DEFAULT 0,
  `bhyt_ngay` tinyint(3) UNSIGNED DEFAULT NULL,
  `bhyt_thang` tinyint(3) UNSIGNED DEFAULT NULL,
  `bhyt_nam` smallint(5) UNSIGNED DEFAULT NULL,
  `so_the_bhyt` varchar(50) DEFAULT NULL,
  `dien_thoai_bao_tin` varchar(50) DEFAULT NULL,
  `gio_kham` tinyint(3) UNSIGNED DEFAULT NULL,
  `phut_kham` tinyint(3) UNSIGNED DEFAULT NULL,
  `ngay_kham` tinyint(3) UNSIGNED DEFAULT NULL,
  `thang_kham` tinyint(3) UNSIGNED DEFAULT NULL,
  `nam_kham` smallint(5) UNSIGNED DEFAULT NULL,
  `chan_doan_gioi_thieu` varchar(500) DEFAULT NULL,
  `qua_trinh_benh_li` text DEFAULT NULL,
  `tien_su_ban_than` text DEFAULT NULL,
  `tien_su_gia_dinh` text DEFAULT NULL,
  `kham_toan_than` text DEFAULT NULL,
  `mach` smallint(6) DEFAULT NULL,
  `nhiet_do` decimal(4,1) DEFAULT NULL,
  `huyet_ap_tam_thu` smallint(6) DEFAULT NULL,
  `huyet_ap_tam_truong` smallint(6) DEFAULT NULL,
  `nhip_tho` smallint(6) DEFAULT NULL,
  `kham_cac_bo_phan` text DEFAULT NULL,
  `tom_tat_lam_sang` text DEFAULT NULL,
  `chan_doan_vao_vien` text DEFAULT NULL,
  `da_xu_li` text DEFAULT NULL,
  `khoa_dieu_tri` varchar(255) DEFAULT NULL,
  `chu_y` varchar(255) DEFAULT NULL,
  `ngay_ky` tinyint(3) UNSIGNED DEFAULT NULL,
  `thang_ky` tinyint(3) UNSIGNED DEFAULT NULL,
  `nam_ky` smallint(5) UNSIGNED DEFAULT NULL,
  `ten_bac_si` varchar(255) DEFAULT NULL,
  `lich_hen` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_kham_benh`
--

INSERT INTO `phieu_kham_benh` (`id`, `id_lich_hen`, `benh_nhan_id`, `bac_si_id`, `so_y_te`, `benh_vien`, `buong_kham`, `ho_ten`, `ngay_sinh`, `thang_sinh`, `nam_sinh`, `tuoi`, `gioi_tinh`, `nghe_nghiep`, `dan_toc`, `ngoai_kieu`, `noi_lam_viec`, `dia_chi`, `doi_tuong_bhyt`, `doi_tuong_thu_phi`, `doi_tuong_mien`, `doi_tuong_khac`, `bhyt_ngay`, `bhyt_thang`, `bhyt_nam`, `so_the_bhyt`, `dien_thoai_bao_tin`, `gio_kham`, `phut_kham`, `ngay_kham`, `thang_kham`, `nam_kham`, `chan_doan_gioi_thieu`, `qua_trinh_benh_li`, `tien_su_ban_than`, `tien_su_gia_dinh`, `kham_toan_than`, `mach`, `nhiet_do`, `huyet_ap_tam_thu`, `huyet_ap_tam_truong`, `nhip_tho`, `kham_cac_bo_phan`, `tom_tat_lam_sang`, `chan_doan_vao_vien`, `da_xu_li`, `khoa_dieu_tri`, `chu_y`, `ngay_ky`, `thang_ky`, `nam_ky`, `ten_bac_si`, `lich_hen`, `created_at`, `updated_at`) VALUES
(40, NULL, 44, 1, 'Thành Phố Hồ Chí Minh', 'Thịnh Việt', 'Tim mạch', 'LÊ VĂN C', 10, 12, 1988, 36, 'Nam', '', '', '', '', 'Đà Nẵng', 0, 1, 0, 0, NULL, NULL, NULL, '', '84913992110', NULL, NULL, 27, 11, 2025, NULL, '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'Đau bụng ', '', '', '', 27, 11, 2025, 'GSTS. Cao Việt', NULL, '2025-11-27 08:34:39', '2025-11-27 08:34:39'),
(41, 209, 45, 1, 'Thành Phố Hồ Chí Minh', 'Thịnh Việt', 'Tim mạch', 'CAO DƯƠNG QUỐC VIỆT', 22, 3, 2003, 22, 'Nam', '', '', '', '', 'An Giang', 0, 1, 0, 0, NULL, NULL, NULL, '', '84913998110', NULL, NULL, 28, 11, 2025, NULL, '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'zxczxc', '', '', '', 28, 11, 2025, 'GSTS. Cao Việt', NULL, '2025-11-28 09:34:19', '2025-11-28 09:34:19'),
(42, 211, 45, 1, 'Thành Phố Hồ Chí Minh', 'Thịnh Việt', 'Tim mạch', 'CAO DƯƠNG QUỐC VIỆT', 22, 3, 2003, 22, 'Nam', '', '', '', '', 'An Giang', 0, 1, 0, 0, NULL, NULL, NULL, '', '84913998110', NULL, NULL, 28, 11, 2025, NULL, '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'zxcvzxcv', '', '', '', 28, 11, 2025, 'GSTS. Cao Việt', NULL, '2025-11-28 09:51:23', '2025-11-28 09:51:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_tien_su_di_ung`
--

CREATE TABLE `phieu_tien_su_di_ung` (
  `id` int(11) NOT NULL,
  `benh_nhan_id` int(11) NOT NULL,
  `thuoc_hoac_di_nguyen` text DEFAULT NULL,
  `so_lan_thuoc` varchar(50) DEFAULT NULL,
  `khong_thuoc` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu_thuoc` text DEFAULT NULL,
  `con_trung` text DEFAULT NULL,
  `so_lan_con_trung` varchar(50) DEFAULT NULL,
  `khong_con_trung` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu_con_trung` text DEFAULT NULL,
  `thuc_pham` text DEFAULT NULL,
  `so_lan_thuc_pham` varchar(50) DEFAULT NULL,
  `khong_thuc_pham` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu_thuc_pham` text DEFAULT NULL,
  `tac_nhan_khac` text DEFAULT NULL,
  `so_lan_tac_nhan_khac` varchar(50) DEFAULT NULL,
  `khong_tac_nhan_khac` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu_tac_nhan_khac` text DEFAULT NULL,
  `tien_su_ca_nhan` text DEFAULT NULL,
  `so_lan_tien_su_ca_nhan` varchar(50) DEFAULT NULL,
  `khong_tien_su_ca_nhan` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu_tien_su_ca_nhan` text DEFAULT NULL,
  `tien_su_gia_dinh` text DEFAULT NULL,
  `so_lan_tien_su_gia_dinh` varchar(50) DEFAULT NULL,
  `khong_tien_su_gia_dinh` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu_tien_su_gia_dinh` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_tra_ket_qua_xet_nghiem`
--

CREATE TABLE `phieu_tra_ket_qua_xet_nghiem` (
  `id` int(11) NOT NULL,
  `id_phieu_yeu_cau` int(11) NOT NULL COMMENT 'ID phiếu yêu cầu xét nghiệm',
  `id_benh_nhan` int(11) NOT NULL COMMENT 'ID bệnh nhân',
  `ma_benh_nhan` varchar(50) NOT NULL COMMENT 'Mã bệnh nhân',
  `ho_ten` varchar(255) NOT NULL COMMENT 'Họ tên bệnh nhân',
  `tuoi` tinyint(3) UNSIGNED NOT NULL COMMENT 'Tuổi',
  `gioi_tinh` varchar(10) NOT NULL COMMENT 'Giới tính',
  `dia_chi` text DEFAULT NULL COMMENT 'Địa chỉ',
  `chan_doan_so_bo` text DEFAULT NULL COMMENT 'Chẩn đoán sơ bộ',
  `tinh_trang_mau` varchar(100) DEFAULT NULL COMMENT 'Tình trạng mẫu',
  `vi_tri_lay_mau` varchar(255) DEFAULT NULL COMMENT 'Vị trí lấy mẫu',
  `bac_si_yeu_cau` varchar(255) DEFAULT NULL COMMENT 'Bác sĩ yêu cầu',
  `bac_si_xet_nghiem` varchar(255) NOT NULL COMMENT 'Bác sĩ xét nghiệm',
  `ngay_dang_ky` datetime NOT NULL COMMENT 'Ngày đăng ký',
  `ngay_tra_ket_qua` date NOT NULL COMMENT 'Ngày trả kết quả',
  `trang_thai` enum('Đã trả kết quả','Đã in','Đã gửi') NOT NULL DEFAULT 'Đã trả kết quả',
  `ghi_chu` text DEFAULT NULL COMMENT 'Ghi chú thêm',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu phiếu trả kết quả xét nghiệm';

--
-- Đang đổ dữ liệu cho bảng `phieu_tra_ket_qua_xet_nghiem`
--

INSERT INTO `phieu_tra_ket_qua_xet_nghiem` (`id`, `id_phieu_yeu_cau`, `id_benh_nhan`, `ma_benh_nhan`, `ho_ten`, `tuoi`, `gioi_tinh`, `dia_chi`, `chan_doan_so_bo`, `tinh_trang_mau`, `vi_tri_lay_mau`, `bac_si_yeu_cau`, `bac_si_xet_nghiem`, `ngay_dang_ky`, `ngay_tra_ket_qua`, `trang_thai`, `ghi_chu`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(31, 25, 44, 'BN25112548', 'LÊ VĂN C', 36, '', 'Đà Nẵng', 'Đau bụng ', 'zxcv', 'zxcv', 'GSTS. Cao Việt', 'Thinh', '2025-11-27 15:35:03', '2025-11-27', 'Đã trả kết quả', NULL, '2025-11-27 08:36:54', '2025-11-27 08:36:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_yeu_cau_sieu_am`
--

CREATE TABLE `phieu_yeu_cau_sieu_am` (
  `id` int(11) NOT NULL,
  `id_phieu_kham_benh` int(11) NOT NULL COMMENT 'FK -> phieu_kham_benh.id',
  `so_ho_so` varchar(100) DEFAULT NULL COMMENT 'Mã bệnh nhân (ma_benh_nhan)',
  `ho_ten` varchar(255) DEFAULT NULL,
  `gioi_tinh` varchar(10) DEFAULT NULL,
  `doi_tuong` varchar(50) DEFAULT NULL COMMENT 'BHYT / Thu phí',
  `so_the_bhyt` varchar(50) DEFAULT NULL,
  `phong_kham` varchar(255) DEFAULT NULL,
  `chan_doan` varchar(500) DEFAULT NULL,
  `yeu_cau` text DEFAULT NULL COMMENT 'Yêu cầu siêu âm (có thể nhiều mục, ngăn cách bằng dấu phẩy)',
  `bac_si_kham` varchar(255) DEFAULT NULL,
  `thoi_gian_yeu_cau` datetime DEFAULT NULL,
  `bac_si_sieu_am_id` int(11) DEFAULT NULL,
  `trang_thai` enum('Đã yêu cầu','Hoàn thành') NOT NULL DEFAULT 'Đã yêu cầu',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu yêu cầu siêu âm';

--
-- Đang đổ dữ liệu cho bảng `phieu_yeu_cau_sieu_am`
--

INSERT INTO `phieu_yeu_cau_sieu_am` (`id`, `id_phieu_kham_benh`, `so_ho_so`, `ho_ten`, `gioi_tinh`, `doi_tuong`, `so_the_bhyt`, `phong_kham`, `chan_doan`, `yeu_cau`, `bac_si_kham`, `thoi_gian_yeu_cau`, `bac_si_sieu_am_id`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(16, 42, 'BN25112712', 'Cao Dương Quốc Việt', 'Nam', 'Thu phí', '', 'Thịnh Việt', 'zxcvzxcv', 'Siêu âm ổ bụng tổng quát', 'GSTS. Cao Việt', '2025-11-28 17:27:28', 54, 'Đã yêu cầu', '2025-11-28 10:27:28', '2025-11-28 10:27:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_yeu_cau_xet_nghiem`
--

CREATE TABLE `phieu_yeu_cau_xet_nghiem` (
  `id` int(11) NOT NULL,
  `id_phieu_kham_benh` int(11) DEFAULT NULL,
  `so_ho_so` varchar(100) DEFAULT NULL,
  `ho_ten` varchar(255) DEFAULT NULL,
  `tuoi` tinyint(3) UNSIGNED DEFAULT NULL,
  `gioi_tinh` varchar(10) DEFAULT NULL,
  `doi_tuong` varchar(50) DEFAULT NULL COMMENT 'BHYT / Thu phí',
  `so_the_bhyt` varchar(50) DEFAULT NULL,
  `phong_kham` varchar(255) DEFAULT NULL,
  `chan_doan` varchar(500) DEFAULT NULL,
  `yeu_cau` text DEFAULT NULL COMMENT 'Yêu cầu xét nghiệm (có thể nhiều mục, ngăn cách bằng dấu phẩy)',
  `bac_si_kham` varchar(255) DEFAULT NULL,
  `thoi_gian_yeu_cau` datetime DEFAULT NULL,
  `bac_si_xet_nghiem_id` int(11) DEFAULT NULL,
  `trang_thai` enum('Đã yêu cầu','Hoàn thành') NOT NULL DEFAULT 'Đã yêu cầu',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_yeu_cau_xet_nghiem`
--

INSERT INTO `phieu_yeu_cau_xet_nghiem` (`id`, `id_phieu_kham_benh`, `so_ho_so`, `ho_ten`, `tuoi`, `gioi_tinh`, `doi_tuong`, `so_the_bhyt`, `phong_kham`, `chan_doan`, `yeu_cau`, `bac_si_kham`, `thoi_gian_yeu_cau`, `bac_si_xet_nghiem_id`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(25, 40, 'BN25112548', 'Lê Văn C', 37, '', 'Thu phí', '', 'Thịnh Việt', 'Đau bụng ', 'Xét nghiệm máu toàn phần', 'GSTS. Cao Việt', '2025-11-27 15:35:03', NULL, 'Hoàn thành', '2025-11-27 08:35:03', '2025-11-27 08:37:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quan_tri_vien`
--

CREATE TABLE `quan_tri_vien` (
  `id` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `quan_tri_vien`
--

INSERT INTO `quan_tri_vien` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$yhTSpU/RsVsqqst2e48peOojd/lGjmkkO30KVX3rTHXrkCcG65oR6', '0123456789', '2025-08-12 07:44:29', '2025-09-06 08:35:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sieuam_suggestions`
--

CREATE TABLE `sieuam_suggestions` (
  `id` int(11) NOT NULL,
  `ten_goi_y` varchar(255) NOT NULL COMMENT 'Tên gợi ý Siêu âm',
  `gia_tien` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá tiền (VNĐ)',
  `mo_ta` text DEFAULT NULL,
  `loai_chup` enum('Ổ bụng','Sản phụ khoa','Tim mạch','Mạch máu','Tuyến - đầu cổ','Cơ xương khớp','Khác') NOT NULL DEFAULT 'Khác',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1,
  `thu_tu` int(11) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng gợi ý Siêu âm';

--
-- Đang đổ dữ liệu cho bảng `sieuam_suggestions`
--

INSERT INTO `sieuam_suggestions` (`id`, `ten_goi_y`, `gia_tien`, `mo_ta`, `loai_chup`, `trang_thai`, `thu_tu`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Siêu âm ổ bụng tổng quát', 250000.00, NULL, 'Ổ bụng', 1, 1, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(2, 'Siêu âm gan – mật – tụy – lách', 220000.00, NULL, 'Ổ bụng', 1, 2, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(3, 'Siêu âm thận – bàng quang', 200000.00, NULL, 'Ổ bụng', 1, 3, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(4, 'Siêu âm tuyến thượng thận', 300000.00, NULL, 'Ổ bụng', 1, 4, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(5, 'Siêu âm vùng hạ vị', 200000.00, NULL, 'Ổ bụng', 1, 5, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(6, 'Siêu âm tử cung – phần phụ', 220000.00, NULL, 'Sản phụ khoa', 1, 6, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(7, 'Siêu âm thai 2D', 220000.00, NULL, 'Sản phụ khoa', 1, 7, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(8, 'Siêu âm thai 3D', 350000.00, NULL, 'Sản phụ khoa', 1, 8, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(9, 'Siêu âm thai 4D (3D động)', 450000.00, NULL, 'Sản phụ khoa', 1, 9, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(10, 'Siêu âm Doppler thai nhi (Doppler màu)', 400000.00, NULL, 'Sản phụ khoa', 1, 10, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(11, 'Siêu âm thai đo độ mờ da gáy', 350000.00, NULL, 'Sản phụ khoa', 1, 11, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(12, 'Siêu âm thai kiểm tra hình thái học (thai 12–22 tuần)', 500000.00, NULL, 'Sản phụ khoa', 1, 12, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(13, 'Siêu âm thai tim thai – Doppler tim thai', 450000.00, NULL, 'Sản phụ khoa', 1, 13, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(14, 'Siêu âm đầu dò âm đạo', 280000.00, NULL, 'Sản phụ khoa', 1, 14, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(15, 'Siêu âm theo dõi nang noãn, rụng trứng', 250000.00, NULL, 'Sản phụ khoa', 1, 15, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(16, 'Siêu âm tử cung – buồng trứng', 220000.00, NULL, 'Sản phụ khoa', 1, 16, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(17, 'Siêu âm tim 2D', 450000.00, NULL, 'Tim mạch', 1, 17, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(18, 'Siêu âm tim Doppler màu', 550000.00, NULL, 'Tim mạch', 1, 18, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(19, 'Siêu âm tim thai (Doppler tim thai)', 650000.00, NULL, 'Tim mạch', 1, 19, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(20, 'Siêu âm Doppler động mạch cảnh', 500000.00, NULL, 'Mạch máu', 1, 20, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(21, 'Siêu âm Doppler tĩnh mạch chi dưới', 450000.00, NULL, 'Mạch máu', 1, 21, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(22, 'Siêu âm Doppler tĩnh mạch chi trên', 450000.00, NULL, 'Mạch máu', 1, 22, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(23, 'Siêu âm Doppler động mạch thận', 550000.00, NULL, 'Mạch máu', 1, 23, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(24, 'Siêu âm Doppler mạch chi', 500000.00, NULL, 'Mạch máu', 1, 24, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(25, 'Siêu âm tuyến giáp', 220000.00, NULL, 'Tuyến - đầu cổ', 1, 25, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(26, 'Siêu âm tuyến mang tai', 250000.00, NULL, 'Tuyến - đầu cổ', 1, 26, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(27, 'Siêu âm tuyến dưới hàm', 250000.00, NULL, 'Tuyến - đầu cổ', 1, 27, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(28, 'Siêu âm hạch cổ', 250000.00, NULL, 'Tuyến - đầu cổ', 1, 28, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(29, 'Siêu âm tuyến cận giáp', 300000.00, NULL, 'Tuyến - đầu cổ', 1, 29, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(30, 'Siêu âm khớp gối', 250000.00, NULL, 'Cơ xương khớp', 1, 30, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(31, 'Siêu âm khớp vai', 250000.00, NULL, 'Cơ xương khớp', 1, 31, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(32, 'Siêu âm khớp cổ tay – bàn tay', 250000.00, NULL, 'Cơ xương khớp', 1, 32, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(33, 'Siêu âm khớp cổ chân – bàn chân', 250000.00, NULL, 'Cơ xương khớp', 1, 33, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(34, 'Siêu âm gân cơ (đùi, bắp chân, cánh tay, vai, cổ...)', 280000.00, NULL, 'Cơ xương khớp', 1, 34, '2025-10-09 12:39:48', '2025-10-09 12:39:48'),
(35, 'Siêu âm phát hiện tràn dịch khớp', 220000.00, NULL, 'Cơ xương khớp', 1, 35, '2025-10-09 12:39:48', '2025-10-09 12:39:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sieu_am_hinh_anh`
--

CREATE TABLE `sieu_am_hinh_anh` (
  `id` int(11) NOT NULL,
  `id_ket_qua_sieu_am` int(11) NOT NULL,
  `ten_file` varchar(255) NOT NULL,
  `duong_dan` varchar(500) NOT NULL,
  `kich_thuoc` int(11) DEFAULT NULL COMMENT 'Kích thước file tính bằng bytes',
  `loai_file` varchar(50) DEFAULT NULL COMMENT 'image/jpeg, image/png, etc.',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL,
  `doi_tuong` enum('bac_si','benh_nhan','quan_tri_vien') NOT NULL,
  `bac_si_id` int(11) DEFAULT NULL,
  `benh_nhan_id` int(11) DEFAULT NULL,
  `quan_tri_vien_id` int(11) DEFAULT NULL,
  `loai` varchar(50) NOT NULL DEFAULT 'info',
  `noi_dung` text NOT NULL,
  `du_lieu_kem_theo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`du_lieu_kem_theo`)),
  `da_doc` tinyint(1) NOT NULL DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_bao`
--

INSERT INTO `thong_bao` (`id`, `doi_tuong`, `bac_si_id`, `benh_nhan_id`, `quan_tri_vien_id`, `loai`, `noi_dung`, `du_lieu_kem_theo`, `da_doc`, `ngay_tao`) VALUES
(10, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 14:40', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"14:40\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 14:40\",\"timestamp\":\"2025-11-12 14:36:26\"}', 1, '2025-11-12 07:36:26'),
(12, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:00', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:00\",\"timestamp\":\"2025-11-12 14:57:43\"}', 1, '2025-11-12 07:57:43'),
(14, 'bac_si', 1, NULL, NULL, 'warning', 'Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 15:00:00', '{\"appointmentId\":137,\"patientId\":43,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:00:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 15:00:00\",\"timestamp\":\"2025-11-12 15:02:08\"}', 1, '2025-11-12 08:02:08'),
(15, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:20', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:20\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:20\",\"timestamp\":\"2025-11-12 15:14:56\"}', 1, '2025-11-12 08:14:56'),
(18, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:20', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:20\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:20\",\"timestamp\":\"2025-11-12 15:19:37\"}', 1, '2025-11-12 08:19:37'),
(20, 'bac_si', 1, NULL, NULL, 'warning', 'Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 15:20:00', '{\"appointmentId\":139,\"patientId\":43,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:20:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 15:20:00\",\"timestamp\":\"2025-11-12 15:24:11\"}', 1, '2025-11-12 08:24:11'),
(21, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:30', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:30\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 15:30\",\"timestamp\":\"2025-11-12 15:24:42\"}', 1, '2025-11-12 08:24:42'),
(23, 'bac_si', 1, NULL, NULL, 'warning', 'Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 15:30:00', '{\"appointmentId\":140,\"patientId\":43,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"15:30:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 15:30:00\",\"timestamp\":\"2025-11-12 15:46:21\"}', 1, '2025-11-12 08:46:21'),
(24, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 16:00', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"16:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 16:00\",\"timestamp\":\"2025-11-12 15:50:17\"}', 1, '2025-11-12 08:50:17'),
(26, 'bac_si', 1, NULL, NULL, 'warning', 'Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 16:00:00', '{\"appointmentId\":141,\"patientId\":43,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"16:00:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 16:00:00\",\"timestamp\":\"2025-11-12 15:50:29\"}', 1, '2025-11-12 08:50:29'),
(27, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 16:10', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"16:10\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 16:10\",\"timestamp\":\"2025-11-12 16:02:14\"}', 1, '2025-11-12 09:02:14'),
(29, 'bac_si', 1, NULL, NULL, 'warning', 'Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 16:10:00', '{\"appointmentId\":142,\"patientId\":43,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"16:10:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 16:10:00\",\"timestamp\":\"2025-11-12 16:12:10\"}', 1, '2025-11-12 09:12:10'),
(30, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 16:30', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"16:30\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 12-11-2025 lúc 16:30\",\"timestamp\":\"2025-11-12 16:23:29\"}', 1, '2025-11-12 09:23:29'),
(32, 'bac_si', 1, NULL, NULL, 'warning', 'Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 16:30:00', '{\"appointmentId\":143,\"patientId\":43,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-12\",\"appointmentTime\":\"16:30:00\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã hủy lịch hẹn vào 2025-11-12 lúc 16:30:00\",\"timestamp\":\"2025-11-25 14:17:48\"}', 1, '2025-11-25 07:17:48'),
(33, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 25-11-2025 lúc 14:40', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-25\",\"appointmentTime\":\"14:40\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 25-11-2025 lúc 14:40\",\"timestamp\":\"2025-11-25 14:36:04\"}', 1, '2025-11-25 07:36:04'),
(36, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 25-11-2025 lúc 16:20', '{\"doctorId\":\"1\",\"patientId\":43,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-25\",\"appointmentTime\":\"16:20\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 25-11-2025 lúc 16:20\",\"timestamp\":\"2025-11-25 15:52:21\"}', 1, '2025-11-25 08:52:21'),
(38, 'bac_si', 1, NULL, NULL, 'info', 'Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 28-11-2025 lúc 16:30', '{\"doctorId\":\"1\",\"patientId\":45,\"patientName\":\"Cao Dương Quốc Việt\",\"appointmentDate\":\"2025-11-28\",\"appointmentTime\":\"16:30\",\"message\":\"Bệnh nhân Cao Dương Quốc Việt đã đặt lịch hẹn vào 28-11-2025 lúc 16:30\",\"timestamp\":\"2025-11-28 16:24:47\"}', 0, '2025-11-28 09:24:47'),
(39, 'benh_nhan', NULL, 45, NULL, 'success', 'Bạn đã đặt lịch hẹn thành công với bác sĩ GSTS. Cao Việt vào 28-11-2025 lúc 16:30', '{\"patientId\":45,\"doctorId\":\"1\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-28\",\"appointmentTime\":\"16:30\",\"message\":\"Bạn đã đặt lịch hẹn thành công với bác sĩ GSTS. Cao Việt vào 28-11-2025 lúc 16:30\",\"timestamp\":\"2025-11-28 16:24:47\"}', 0, '2025-11-28 09:24:47'),
(40, 'benh_nhan', NULL, 45, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn ngày 28-11-2025 thành: Đã xác nhận', '{\"appointmentId\":208,\"patientId\":45,\"doctorId\":1,\"patientName\":\"Cao Dương Quốc Việt\",\"doctorName\":\"GSTS. Cao Việt\",\"appointmentDate\":\"2025-11-28\",\"appointmentTime\":\"16:30:00\",\"newStatus\":\"Đã xác nhận\",\"note\":\"\",\"timestamp\":\"2025-11-28 16:25:56\",\"message\":\"Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn ngày 28-11-2025 thành: Đã xác nhận\"}', 0, '2025-11-28 09:25:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuoc`
--

CREATE TABLE `thuoc` (
  `MaThuoc` varchar(10) NOT NULL,
  `TenThuoc` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `HoatChatChinh` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `DangBaoChe` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `DonViTinh` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `HamLuong` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ChiDinh` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ChongChiDinh` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `LieuDung` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `DonGia` decimal(10,2) DEFAULT NULL CHECK (`DonGia` >= 0),
  `SoLuongTon` int(11) DEFAULT 0 CHECK (`SoLuongTon` >= 0),
  `NgaySanXuat` date DEFAULT NULL,
  `HanSuDung` date DEFAULT NULL,
  `NhaSanXuat` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `NuocSanXuat` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `BaoHiem` bit(1) DEFAULT b'0',
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `TrangThai` bit(1) DEFAULT b'1',
  `NgayTao` datetime DEFAULT current_timestamp(),
  `NgayCapNhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thuoc`
--

INSERT INTO `thuoc` (`MaThuoc`, `TenThuoc`, `HoatChatChinh`, `DangBaoChe`, `DonViTinh`, `HamLuong`, `ChiDinh`, `ChongChiDinh`, `LieuDung`, `DonGia`, `SoLuongTon`, `NgaySanXuat`, `HanSuDung`, `NhaSanXuat`, `NuocSanXuat`, `BaoHiem`, `GhiChu`, `TrangThai`, `NgayTao`, `NgayCapNhat`) VALUES
('T001', 'Amoxicillin 500mg', 'Amoxicillin', 'Viên nang', 'Viên', '500mg', 'Nhiễm khuẩn đường hô hấp, tiêu hóa', 'Dị ứng penicillin', '1-2 viên x 3 lần/ngày', 15000.00, 65, '2024-01-01', '2026-01-01', 'Công ty Dược phẩm A', 'Việt Nam', b'1', 'Thuốc kháng sinh phổ rộng', b'1', '2025-10-20 00:43:58', '2025-11-27 15:42:49'),
('T002', 'Paracetamol 500mg', 'Paracetamol', 'Viên nén', 'Viên', '500mg', 'Giảm đau, hạ sốt', 'Suy gan nặng', '1-2 viên x 3-4 lần/ngày', 5000.00, 172, '2024-01-01', '2026-01-01', 'Công ty Dược phẩm B', 'Việt Nam', b'0', 'Thuốc giảm đau, hạ sốt', b'1', '2025-10-20 00:43:58', '2025-10-31 05:02:35'),
('T003', 'Aspirin 100mg', 'Acetylsalicylic acid', 'Viên nén', 'Viên', '100mg', 'Dự phòng đột quỵ, nhồi máu cơ tim', 'Loét dạ dày, xuất huyết', '1 viên x 1 lần/ngày', 8000.00, 122, '2024-01-01', '2026-01-01', 'Công ty Dược phẩm C', 'Việt Nam', b'1', 'Thuốc chống kết tập tiểu cầu', b'1', '2025-10-20 00:43:58', '2025-11-27 15:42:49'),
('T004', 'Omeprazole 20mg', 'Omeprazole', 'Viên nang', 'Viên', '20mg', 'Điều trị loét dạ dày, trào ngược', 'Dị ứng omeprazole', '1 viên x 1 lần/ngày', 25000.00, 80, '2024-01-01', '2026-01-01', 'Công ty Dược phẩm D', 'Việt Nam', b'1', 'Thuốc ức chế bơm proton', b'1', '2025-10-20 00:43:58', '2025-10-31 04:37:16'),
('T005', 'Salbutamol 100mcg', 'Salbutamol', 'Bình xịt', 'Bình', '100mcg', 'Điều trị hen phế quản, COPD', 'Dị ứng salbutamol', '1-2 nhát x 3-4 lần/ngày', 45000.00, 44, '2024-01-01', '2026-01-01', 'Công ty Dược phẩm E', 'Việt Nam', b'0', 'Thuốc giãn phế quản', b'1', '2025-10-20 00:43:58', '2025-11-27 15:42:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `xet_nghiem_suggestions`
--

CREATE TABLE `xet_nghiem_suggestions` (
  `id` int(11) NOT NULL,
  `ten_goi_y` varchar(255) NOT NULL COMMENT 'Tên gợi ý xét nghiệm',
  `gia_tien` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá tiền (VNĐ)',
  `mo_ta` text DEFAULT NULL,
  `loai_xet_nghiem` enum('Huyết học','Sinh hóa','Vi sinh','Miễn dịch','Nội tiết','Khác') NOT NULL DEFAULT 'Khác',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1,
  `thu_tu` int(11) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng gợi ý xét nghiệm';

--
-- Đang đổ dữ liệu cho bảng `xet_nghiem_suggestions`
--

INSERT INTO `xet_nghiem_suggestions` (`id`, `ten_goi_y`, `gia_tien`, `mo_ta`, `loai_xet_nghiem`, `trang_thai`, `thu_tu`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Xét nghiệm máu toàn phần', 50000.00, 'Xét nghiệm cơ bản về huyết học', 'Huyết học', 1, 1, '2025-10-18 16:58:03', '2025-10-30 12:51:04'),
(2, 'Xét nghiệm máu / nước tiểu', 80000.00, 'Đường huyết, ure, creatinin', 'Sinh hóa', 1, 2, '2025-10-18 16:58:03', '2025-10-30 12:51:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `xray_suggestions`
--

CREATE TABLE `xray_suggestions` (
  `id` int(11) NOT NULL,
  `ten_goi_y` varchar(255) NOT NULL COMMENT 'Tên gợi ý X-Quang',
  `gia_tien` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá tiền (VNĐ)',
  `mo_ta` text DEFAULT NULL COMMENT 'Mô tả chi tiết',
  `loai_chup` enum('Xương','Ngực','Cột sống','Khớp','Sọ mặt','Khác') NOT NULL DEFAULT 'Khác' COMMENT 'Loại chụp',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Tạm dừng',
  `thu_tu` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng gợi ý X-Quang';

--
-- Đang đổ dữ liệu cho bảng `xray_suggestions`
--

INSERT INTO `xray_suggestions` (`id`, `ten_goi_y`, `gia_tien`, `mo_ta`, `loai_chup`, `trang_thai`, `thu_tu`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Vai phải', 150000.00, 'Chụp X-quang vai phải', 'Xương', 1, 1, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(2, 'Vai trái', 150000.00, 'Chụp X-quang vai trái', 'Xương', 1, 2, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(3, 'Xương cánh tay phải', 120000.00, 'Chụp X-quang xương cánh tay phải', 'Xương', 1, 3, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(4, 'Xương cánh tay trái', 120000.00, 'Chụp X-quang xương cánh tay trái', 'Xương', 1, 4, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(5, 'Khuỷu tay phải', 100000.00, 'Chụp X-quang khuỷu tay phải', 'Khớp', 1, 5, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(6, 'Khuỷu tay trái', 100000.00, 'Chụp X-quang khuỷu tay trái', 'Khớp', 1, 6, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(7, 'Cẳng tay phải', 100000.00, 'Chụp X-quang cẳng tay phải', 'Xương', 1, 7, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(8, 'Cẳng tay trái', 100000.00, 'Chụp X-quang cẳng tay trái', 'Xương', 1, 8, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(9, 'Cổ tay phải', 80000.00, 'Chụp X-quang cổ tay phải', 'Khớp', 1, 9, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(10, 'Cổ tay trái', 80000.00, 'Chụp X-quang cổ tay trái', 'Khớp', 1, 10, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(11, 'Bàn tay phải', 80000.00, 'Chụp X-quang bàn tay phải', 'Xương', 1, 11, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(12, 'Bàn tay trái', 80000.00, 'Chụp X-quang bàn tay trái', 'Xương', 1, 12, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(13, 'Cột sống cổ thẳng', 200000.00, 'Chụp X-quang cột sống cổ thẳng', 'Cột sống', 1, 13, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(14, 'Cột sống cổ nghiêng', 200000.00, 'Chụp X-quang cột sống cổ nghiêng', 'Cột sống', 1, 14, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(15, 'Cột sống cổ chếch', 200000.00, 'Chụp X-quang cột sống cổ chếch', 'Cột sống', 1, 15, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(16, 'Cột sống ngực thẳng', 250000.00, 'Chụp X-quang cột sống ngực thẳng', 'Cột sống', 1, 16, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(17, 'Cột sống ngực nghiêng', 250000.00, 'Chụp X-quang cột sống ngực nghiêng', 'Cột sống', 1, 17, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(18, 'Khớp háng phải', 180000.00, 'Chụp X-quang khớp háng phải', 'Khớp', 1, 18, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(19, 'Khớp háng trái', 180000.00, 'Chụp X-quang khớp háng trái', 'Khớp', 1, 19, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(20, 'Xương đùi phải', 150000.00, 'Chụp X-quang xương đùi phải', 'Xương', 1, 20, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(21, 'Xương đùi trái', 150000.00, 'Chụp X-quang xương đùi trái', 'Xương', 1, 21, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(22, 'Khớp gối phải', 120000.00, 'Chụp X-quang khớp gối phải', 'Khớp', 1, 22, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(23, 'Khớp gối trái', 120000.00, 'Chụp X-quang khớp gối trái', 'Khớp', 1, 23, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(24, 'Cổ chân phải', 100000.00, 'Chụp X-quang cổ chân phải', 'Khớp', 1, 24, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(25, 'Cổ chân trái', 100000.00, 'Chụp X-quang cổ chân trái', 'Khớp', 1, 25, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(26, 'Khung chậu', 200000.00, 'Chụp X-quang khung chậu', 'Xương', 1, 26, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(27, 'Hộp sọ', 180000.00, 'Chụp X-quang hộp sọ', 'Sọ mặt', 1, 27, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(28, 'Xoang', 150000.00, 'Chụp X-quang xoang', 'Sọ mặt', 1, 28, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(29, 'Hốc mũi', 120000.00, 'Chụp X-quang hốc mũi', 'Sọ mặt', 1, 29, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(30, 'Răng – hàm mặt', 100000.00, 'Chụp X-quang răng hàm mặt', 'Sọ mặt', 1, 30, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(31, 'Ngực thẳng', 120000.00, 'Chụp X-quang ngực thẳng', 'Ngực', 1, 31, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(32, 'Ngực nghiêng', 120000.00, 'Chụp X-quang ngực nghiêng', 'Ngực', 1, 32, '2025-09-27 11:24:24', '2025-09-27 11:24:24'),
(33, 'Cột sống cổ', 200000.00, 'Chụp X-quang cột sống cổ tổng quát', 'Cột sống', 1, 33, '2025-09-27 11:24:24', '2025-09-27 11:24:24');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bac_si`
--
ALTER TABLE `bac_si`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_bac_si_chuyen_khoa_id` (`chuyen_khoa_id`);

--
-- Chỉ mục cho bảng `bao_hiem_y_te`
--
ALTER TABLE `bao_hiem_y_te`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_bao_hiem` (`ma_bao_hiem`);

--
-- Chỉ mục cho bảng `benh_nhan`
--
ALTER TABLE `benh_nhan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `ma_benh_nhan` (`ma_benh_nhan`),
  ADD KEY `idx_bn_email` (`email`),
  ADD KEY `idx_bao_hiem_y_te_id` (`bao_hiem_y_te_id`),
  ADD KEY `idx_cccd` (`cccd`);

--
-- Chỉ mục cho bảng `bien_lai_vien_phi`
--
ALTER TABLE `bien_lai_vien_phi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_bien_lai` (`ma_bien_lai`),
  ADD KEY `id_phieu_kham_benh` (`id_phieu_kham_benh`),
  ADD KEY `id_le_tan` (`id_le_tan`),
  ADD KEY `id_bac_si` (`id_bac_si`),
  ADD KEY `ngay_lap` (`ngay_lap`);

--
-- Chỉ mục cho bảng `cccd_data`
--
ALTER TABLE `cccd_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cccd` (`cccd`),
  ADD KEY `idx_cccd` (`cccd`),
  ADD KEY `idx_trang_thai` (`trang_thai`);

--
-- Chỉ mục cho bảng `chi_so_xet_nghiem`
--
ALTER TABLE `chi_so_xet_nghiem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_xet_nghiem` (`xet_nghiem`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `chi_tiet_bien_lai`
--
ALTER TABLE `chi_tiet_bien_lai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bien_lai` (`id_bien_lai`),
  ADD KEY `loai_dich_vu` (`loai_dich_vu`);

--
-- Chỉ mục cho bảng `chi_tiet_don_thuoc`
--
ALTER TABLE `chi_tiet_don_thuoc`
  ADD PRIMARY KEY (`MaChiTiet`),
  ADD KEY `idx_ctdt_madon` (`MaDonThuoc`),
  ADD KEY `idx_ctdt_math` (`MaThuoc`);

--
-- Chỉ mục cho bảng `chi_tiet_ket_qua_xet_nghiem`
--
ALTER TABLE `chi_tiet_ket_qua_xet_nghiem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phieu_tra_ket_qua` (`id_phieu_tra_ket_qua`),
  ADD KEY `idx_stt` (`stt`);

--
-- Chỉ mục cho bảng `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_chuyen_khoa_ten` (`ten`),
  ADD UNIQUE KEY `uniq_chuyen_khoa_slug` (`slug`);

--
-- Chỉ mục cho bảng `dich_vu_kham`
--
ALTER TABLE `dich_vu_kham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ten_dich_vu` (`ten_dich_vu`);

--
-- Chỉ mục cho bảng `don_thuoc`
--
ALTER TABLE `don_thuoc`
  ADD PRIMARY KEY (`MaDonThuoc`),
  ADD KEY `idx_benh_nhan` (`MaBenhNhan`),
  ADD KEY `idx_bac_si` (`MaBacSi`),
  ADD KEY `idx_phieu_kham` (`id_phieu_kham_benh`);

--
-- Chỉ mục cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lich_hen_id` (`lich_hen_id`),
  ADD KEY `idx_hs_benh_nhan` (`benh_nhan_id`),
  ADD KEY `idx_hs_bac_si` (`bac_si_id`);

--
-- Chỉ mục cho bảng `ket_qua_sieu_am`
--
ALTER TABLE `ket_qua_sieu_am`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phieu_yeu_cau` (`id_phieu_yeu_cau_sieu_am`);

--
-- Chỉ mục cho bảng `ket_qua_xquang`
--
ALTER TABLE `ket_qua_xquang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_kq_by_px` (`id_phieu_chup_xquang`);

--
-- Chỉ mục cho bảng `ket_qua_xquang_hinh_anh`
--
ALTER TABLE `ket_qua_xquang_hinh_anh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ket_qua` (`ket_qua_id`);

--
-- Chỉ mục cho bảng `le_tan`
--
ALTER TABLE `le_tan`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `lich_hen`
--
ALTER TABLE `lich_hen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lich_benh_nhan` (`benh_nhan_id`),
  ADD KEY `idx_lich_bac_si` (`bac_si_id`),
  ADD KEY `idx_lich_ngay` (`ngay_hen`);

--
-- Chỉ mục cho bảng `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bac_si_id` (`bac_si_id`),
  ADD KEY `idx_thu_trong_tuan` (`thu_trong_tuan`),
  ADD KEY `idx_trang_thai` (`trang_thai`);

--
-- Chỉ mục cho bảng `lich_lam_viec_le_tan`
--
ALTER TABLE `lich_lam_viec_le_tan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_letan_id` (`letan_id`);

--
-- Chỉ mục cho bảng `lich_lam_viec_le_tan_ngoai_le`
--
ALTER TABLE `lich_lam_viec_le_tan_ngoai_le`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_letan_ngay` (`letan_id`,`ngay`);

--
-- Chỉ mục cho bảng `lich_lam_viec_ngoai_le`
--
ALTER TABLE `lich_lam_viec_ngoai_le`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_exc` (`bac_si_id`,`schedule_id`,`ngay`),
  ADD KEY `idx_bacsi_ngay` (`bac_si_id`,`ngay`);

--
-- Chỉ mục cho bảng `phieu_boc_so`
--
ALTER TABLE `phieu_boc_so`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `phieu_chup_xquang`
--
ALTER TABLE `phieu_chup_xquang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_phieu_kham_benh` (`id_phieu_kham_benh`);

--
-- Chỉ mục cho bảng `phieu_kham_benh`
--
ALTER TABLE `phieu_kham_benh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pkb_benh_nhan` (`benh_nhan_id`),
  ADD KEY `idx_pkb_bac_si` (`bac_si_id`),
  ADD KEY `idx_id_lich_hen` (`id_lich_hen`),
  ADD KEY `fk_pkb_lich_hen` (`lich_hen`);

--
-- Chỉ mục cho bảng `phieu_tien_su_di_ung`
--
ALTER TABLE `phieu_tien_su_di_ung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptsd_benh_nhan` (`benh_nhan_id`);

--
-- Chỉ mục cho bảng `phieu_tra_ket_qua_xet_nghiem`
--
ALTER TABLE `phieu_tra_ket_qua_xet_nghiem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phieu_yeu_cau` (`id_phieu_yeu_cau`),
  ADD KEY `idx_benh_nhan` (`id_benh_nhan`),
  ADD KEY `idx_ma_benh_nhan` (`ma_benh_nhan`),
  ADD KEY `idx_ngay_tra` (`ngay_tra_ket_qua`),
  ADD KEY `idx_trang_thai` (`trang_thai`);

--
-- Chỉ mục cho bảng `phieu_yeu_cau_sieu_am`
--
ALTER TABLE `phieu_yeu_cau_sieu_am`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pycsa_by_exam` (`id_phieu_kham_benh`),
  ADD KEY `idx_exam` (`id_phieu_kham_benh`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `phieu_yeu_cau_xet_nghiem`
--
ALTER TABLE `phieu_yeu_cau_xet_nghiem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_phieu_kham_benh` (`id_phieu_kham_benh`);

--
-- Chỉ mục cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_qtv_email` (`email`);

--
-- Chỉ mục cho bảng `sieuam_suggestions`
--
ALTER TABLE `sieuam_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loai_chup` (`loai_chup`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- Chỉ mục cho bảng `sieu_am_hinh_anh`
--
ALTER TABLE `sieu_am_hinh_anh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ket_qua_sieu_am` (`id_ket_qua_sieu_am`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doi_tuong` (`doi_tuong`),
  ADD KEY `idx_bac_si_id` (`bac_si_id`),
  ADD KEY `idx_benh_nhan_id` (`benh_nhan_id`),
  ADD KEY `idx_quan_tri_vien_id` (`quan_tri_vien_id`),
  ADD KEY `idx_da_doc` (`da_doc`);

--
-- Chỉ mục cho bảng `thuoc`
--
ALTER TABLE `thuoc`
  ADD PRIMARY KEY (`MaThuoc`),
  ADD KEY `idx_thuoc_ten` (`TenThuoc`),
  ADD KEY `idx_thuoc_hoat_chat` (`HoatChatChinh`),
  ADD KEY `idx_thuoc_trang_thai` (`TrangThai`);

--
-- Chỉ mục cho bảng `xet_nghiem_suggestions`
--
ALTER TABLE `xet_nghiem_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loai_xet_nghiem` (`loai_xet_nghiem`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- Chỉ mục cho bảng `xray_suggestions`
--
ALTER TABLE `xray_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loai_chup` (`loai_chup`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bac_si`
--
ALTER TABLE `bac_si`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT cho bảng `bao_hiem_y_te`
--
ALTER TABLE `bao_hiem_y_te`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `benh_nhan`
--
ALTER TABLE `benh_nhan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT cho bảng `bien_lai_vien_phi`
--
ALTER TABLE `bien_lai_vien_phi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `cccd_data`
--
ALTER TABLE `cccd_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `chi_so_xet_nghiem`
--
ALTER TABLE `chi_so_xet_nghiem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_bien_lai`
--
ALTER TABLE `chi_tiet_bien_lai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_don_thuoc`
--
ALTER TABLE `chi_tiet_don_thuoc`
  MODIFY `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_ket_qua_xet_nghiem`
--
ALTER TABLE `chi_tiet_ket_qua_xet_nghiem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT cho bảng `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT cho bảng `dich_vu_kham`
--
ALTER TABLE `dich_vu_kham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ket_qua_sieu_am`
--
ALTER TABLE `ket_qua_sieu_am`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `ket_qua_xquang`
--
ALTER TABLE `ket_qua_xquang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `ket_qua_xquang_hinh_anh`
--
ALTER TABLE `ket_qua_xquang_hinh_anh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `le_tan`
--
ALTER TABLE `le_tan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `lich_hen`
--
ALTER TABLE `lich_hen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec_le_tan`
--
ALTER TABLE `lich_lam_viec_le_tan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec_le_tan_ngoai_le`
--
ALTER TABLE `lich_lam_viec_le_tan_ngoai_le`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec_ngoai_le`
--
ALTER TABLE `lich_lam_viec_ngoai_le`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT cho bảng `phieu_boc_so`
--
ALTER TABLE `phieu_boc_so`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT cho bảng `phieu_chup_xquang`
--
ALTER TABLE `phieu_chup_xquang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT cho bảng `phieu_kham_benh`
--
ALTER TABLE `phieu_kham_benh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT cho bảng `phieu_tien_su_di_ung`
--
ALTER TABLE `phieu_tien_su_di_ung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `phieu_tra_ket_qua_xet_nghiem`
--
ALTER TABLE `phieu_tra_ket_qua_xet_nghiem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `phieu_yeu_cau_sieu_am`
--
ALTER TABLE `phieu_yeu_cau_sieu_am`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `phieu_yeu_cau_xet_nghiem`
--
ALTER TABLE `phieu_yeu_cau_xet_nghiem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `sieuam_suggestions`
--
ALTER TABLE `sieuam_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `sieu_am_hinh_anh`
--
ALTER TABLE `sieu_am_hinh_anh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `xet_nghiem_suggestions`
--
ALTER TABLE `xet_nghiem_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `xray_suggestions`
--
ALTER TABLE `xray_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bac_si`
--
ALTER TABLE `bac_si`
  ADD CONSTRAINT `fk_bac_si_chuyen_khoa` FOREIGN KEY (`chuyen_khoa_id`) REFERENCES `chuyen_khoa` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `benh_nhan`
--
ALTER TABLE `benh_nhan`
  ADD CONSTRAINT `fk_benh_nhan_bao_hiem_y_te` FOREIGN KEY (`bao_hiem_y_te_id`) REFERENCES `bao_hiem_y_te` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `bien_lai_vien_phi`
--
ALTER TABLE `bien_lai_vien_phi`
  ADD CONSTRAINT `fk_bien_lai_phieu_kham` FOREIGN KEY (`id_phieu_kham_benh`) REFERENCES `phieu_kham_benh` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_bien_lai`
--
ALTER TABLE `chi_tiet_bien_lai`
  ADD CONSTRAINT `fk_chi_tiet_bien_lai` FOREIGN KEY (`id_bien_lai`) REFERENCES `bien_lai_vien_phi` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_don_thuoc`
--
ALTER TABLE `chi_tiet_don_thuoc`
  ADD CONSTRAINT `fk_ctdt_donthuoc` FOREIGN KEY (`MaDonThuoc`) REFERENCES `don_thuoc` (`MaDonThuoc`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctdt_thuoc` FOREIGN KEY (`MaThuoc`) REFERENCES `thuoc` (`MaThuoc`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_ket_qua_xet_nghiem`
--
ALTER TABLE `chi_tiet_ket_qua_xet_nghiem`
  ADD CONSTRAINT `chi_tiet_ket_qua_xet_nghiem_ibfk_1` FOREIGN KEY (`id_phieu_tra_ket_qua`) REFERENCES `phieu_tra_ket_qua_xet_nghiem` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `don_thuoc`
--
ALTER TABLE `don_thuoc`
  ADD CONSTRAINT `fk_dt_bacsi` FOREIGN KEY (`MaBacSi`) REFERENCES `bac_si` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_benhnhan` FOREIGN KEY (`MaBenhNhan`) REFERENCES `benh_nhan` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_phieukham` FOREIGN KEY (`id_phieu_kham_benh`) REFERENCES `phieu_kham_benh` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  ADD CONSTRAINT `ho_so_benh_an_ibfk_1` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ho_so_benh_an_ibfk_2` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ho_so_benh_an_ibfk_3` FOREIGN KEY (`lich_hen_id`) REFERENCES `lich_hen` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `ket_qua_sieu_am`
--
ALTER TABLE `ket_qua_sieu_am`
  ADD CONSTRAINT `fk_ket_qua_sieu_am_phieu` FOREIGN KEY (`id_phieu_yeu_cau_sieu_am`) REFERENCES `phieu_yeu_cau_sieu_am` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `ket_qua_xquang`
--
ALTER TABLE `ket_qua_xquang`
  ADD CONSTRAINT `fk_kqxq_px` FOREIGN KEY (`id_phieu_chup_xquang`) REFERENCES `phieu_chup_xquang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `ket_qua_xquang_hinh_anh`
--
ALTER TABLE `ket_qua_xquang_hinh_anh`
  ADD CONSTRAINT `fk_kqxq_img_kq` FOREIGN KEY (`ket_qua_id`) REFERENCES `ket_qua_xquang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `lich_hen`
--
ALTER TABLE `lich_hen`
  ADD CONSTRAINT `lich_hen_ibfk_1` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_hen_ibfk_2` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  ADD CONSTRAINT `lich_lam_viec_ibfk_1` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lich_lam_viec_le_tan`
--
ALTER TABLE `lich_lam_viec_le_tan`
  ADD CONSTRAINT `fk_lich_lam_viec_le_tan_letan` FOREIGN KEY (`letan_id`) REFERENCES `le_tan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `lich_lam_viec_le_tan_ngoai_le`
--
ALTER TABLE `lich_lam_viec_le_tan_ngoai_le`
  ADD CONSTRAINT `fk_lich_lam_viec_le_tan_ngoai_le_letan` FOREIGN KEY (`letan_id`) REFERENCES `le_tan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_chup_xquang`
--
ALTER TABLE `phieu_chup_xquang`
  ADD CONSTRAINT `phieu_chup_xquang_ibfk_1` FOREIGN KEY (`id_phieu_kham_benh`) REFERENCES `phieu_kham_benh` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_kham_benh`
--
ALTER TABLE `phieu_kham_benh`
  ADD CONSTRAINT `fk_phieu_kham_benh_lich_hen` FOREIGN KEY (`id_lich_hen`) REFERENCES `lich_hen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pkb_bac_si` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pkb_benh_nhan` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pkb_lich_hen` FOREIGN KEY (`lich_hen`) REFERENCES `lich_hen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_tien_su_di_ung`
--
ALTER TABLE `phieu_tien_su_di_ung`
  ADD CONSTRAINT `fk_ptsd_benh_nhan` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_tra_ket_qua_xet_nghiem`
--
ALTER TABLE `phieu_tra_ket_qua_xet_nghiem`
  ADD CONSTRAINT `phieu_tra_ket_qua_xet_nghiem_ibfk_1` FOREIGN KEY (`id_phieu_yeu_cau`) REFERENCES `phieu_yeu_cau_xet_nghiem` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phieu_tra_ket_qua_xet_nghiem_ibfk_2` FOREIGN KEY (`id_benh_nhan`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_yeu_cau_sieu_am`
--
ALTER TABLE `phieu_yeu_cau_sieu_am`
  ADD CONSTRAINT `fk_pycsa_exam` FOREIGN KEY (`id_phieu_kham_benh`) REFERENCES `phieu_kham_benh` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_yeu_cau_xet_nghiem`
--
ALTER TABLE `phieu_yeu_cau_xet_nghiem`
  ADD CONSTRAINT `fk_xn_phieu_kham` FOREIGN KEY (`id_phieu_kham_benh`) REFERENCES `phieu_kham_benh` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sieu_am_hinh_anh`
--
ALTER TABLE `sieu_am_hinh_anh`
  ADD CONSTRAINT `fk_sieu_am_hinh_anh_ket_qua` FOREIGN KEY (`id_ket_qua_sieu_am`) REFERENCES `ket_qua_sieu_am` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD CONSTRAINT `fk_tb_bac_si` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tb_benh_nhan` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tb_quan_tri_vien` FOREIGN KEY (`quan_tri_vien_id`) REFERENCES `quan_tri_vien` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
