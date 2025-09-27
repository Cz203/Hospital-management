-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 23, 2025 lúc 01:44 PM
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
-- Cơ sở dữ liệu: `hospital_management`
--

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

-- --------------------------------------------------------
INSERT INTO `le_tan` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `ngay_tao`, `ngay_cap_nhat`) VALUES (NULL, 'Le tan A', 'letan@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', NULL, '2025-09-27 21:27:34', '2025-09-27 21:29:23')
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
(1, 'GSTS. Cao Việt', 'caoviet5.work@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0901000001', 1, 'Tim mạch', 'TM001', 15, '2025-08-12 09:06:03', '2025-09-22 06:53:16', 'uploads/bacsiviet.png');

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
  `trang_thai` enum('Hieu luc','Het han','Tam dung') DEFAULT 'Hieu luc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bao_hiem_y_te`
--

INSERT INTO `bao_hiem_y_te` (`id`, `ma_bao_hiem`, `loai_the`, `ten_chu_the`, `ngay_sinh`, `gioi_tinh`, `ngay_bat_dau`, `ngay_het_han`, `noi_cap`, `trang_thai`) VALUES
(16, '0791034567', 'BHYT', 'Hoàng Nguyễn Phương Trang', '2003-01-10', 'Nu', '2023-01-01', '2024-12-31', 'Bảo hiểm xã hội TP.HCM', 'Hieu luc'),
(17, '0791034568', 'BHYT', 'Việt', '2003-03-22', 'Nam', '2023-01-01', '2024-12-31', 'Bảo hiểm xã hội TP.HCM', 'Hieu luc'),
(18, '0791034569', 'BHYT', 'Cao Viet', '2003-03-20', 'Nam', '2023-01-01', '2024-12-31', 'Bảo hiểm xã hội TP.HCM', 'Hieu luc');

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
  `gioi_tinh` enum('Nam','Nu','Khac') DEFAULT NULL,
  `dia_chi` text DEFAULT NULL,
  `nhom_mau` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `benh_nhan`
--

INSERT INTO `benh_nhan` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `phone_verified`, `bao_hiem_y_te`, `bao_hiem_y_te_id`, `ngay_sinh`, `gioi_tinh`, `dia_chi`, `nhom_mau`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(13, 'Hoàng Nguyễn Phương Trang', '2001trangmoon@gmail.com', '$2y$10$jCb.1eysx1RkUxaY2.cEgud7457aH5aZSqJqsRbg2GKBUttVWuk0a', '84918672152', 1, NULL, NULL, '2003-01-10', 'Nu', 'Trần Bá GIao', 'AB+', '2025-08-22 15:17:21', '2025-09-07 21:16:13'),
(21, 'Việt', 'nasumi121@gmail.com', '$2y$10$E5dJk94KPjGRb.FYwIJ86uiga63FukshJZaR.Qph8CRLkGjNIY2xm', '84385485869', 1, NULL, NULL, '2003-03-22', 'Nam', '51/16A Phạm Văn Chiêu', 'A+', '2025-08-23 21:09:27', '2025-09-10 18:12:18'),
(23, 'cvb', 'tranthi22b@example.com', '$2y$10$WWKqyUOGawB9jPJKvzdXi.ixgAx/DIYPgp1bS/OL9QxHgfnblHYSq', '8413251345134', 1, NULL, NULL, '0000-00-00', 'Nam', 'vczbvcb', '', '2025-08-23 21:19:49', '2025-08-23 21:19:49'),
(24, 'Việt', '2001tra2ngmoon@gmail.com', '$2y$10$7uqm.qatZX26SQefunbrFu/r.hAqc3Fjsox3lS4A/d4AftmwEUwGi', '8412312312312', 1, NULL, NULL, '2003-03-22', 'Nam', '51/16A Phạm Văn Chiêu', '', '2025-08-23 21:56:05', '2025-08-23 21:56:05'),
(27, 'Việt', 'vczxv@gmail.com', '$2y$10$uXQU6yqxAIeBUuNn/oWQR.NnwczHfqb5sNP9P2aVGLxqzethN89fO', '84354143619', 1, NULL, NULL, '2003-01-10', 'Nu', 'vbxcvb', 'A+', '2025-09-15 11:15:08', '2025-09-15 11:15:08'),
(28, 'Việt', 'caoduongvietquoc@gmail.com', '$2y$10$ujZnPMbxVgX1tgQaPWMnr.yU07aISutD3ehgWy5OiMhaIQWh5imWe', '84913998110', 1, NULL, NULL, '2003-03-22', 'Nam', '51/16A Phạm Văn Chiêu', 'A+', '2025-09-15 11:19:11', '2025-09-19 14:31:21');

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
(1, 'Tim mạch', 'tim-mach', 'Khám và điều trị bệnh lý tim mạch', 'fas fa-heartbeat', 60, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(2, 'Thần kinh', 'than-kinh', 'Bệnh lý hệ thần kinh trung ương và ngoại biên', 'fas fa-brain', 70, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(3, 'Nhi khoa', 'nhi-khoa', 'Chăm sóc sức khỏe trẻ em', 'fas fa-child', 80, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(4, 'Sản phụ khoa', 'san-phu-khoa', 'Chăm sóc sức khỏe phụ nữ và thai sản', 'fas fa-baby', 30, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(5, 'Da liễu', 'da-lieu', 'Khám và điều trị các bệnh lý da', 'fas fa-allergies', 110, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(6, 'Mắt', 'mat', 'Khám và điều trị các bệnh lý mắt', 'fas fa-eye', 90, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(7, 'Tai mũi họng', 'tai-mui-hong', 'Bệnh lý tai, mũi, họng', 'fas fa-head-side-cough', 100, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(9, 'Chấn thương chỉnh hình', 'chan-thuong-chinh-hinh', 'v', 'fas fa-bone', 170, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(10, 'Ung bướu', 'ung-buou', 'Chẩn đoán và điều trị các bệnh lý ung thư', 'fas fa-microscope', 20, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(11, 'Nội tiết', 'noi-tiet', 'Bệnh lý nội tiết và chuyển hóa', 'fas fa-pills', 150, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(12, 'Tiêu hóa', 'tieu-hoa', 'Bệnh lý đường tiêu hóa', 'fas fa-notes-medical', 160, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(13, 'Nội tổng quát', 'noi-tong-quat', 'Khám và điều trị các bệnh lý nội khoa tổng quát', 'fas fa-stethoscope', 10, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(14, 'Nam khoa', 'nam-khoa', 'Sức khỏe nam giới', 'fas fa-mars', 130, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(15, 'Truyền nhiễm', 'truyen-nhiem', 'Bệnh truyền nhiễm và kiểm soát nhiễm khuẩn', 'fas fa-virus', 140, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(16, 'Chẩn đoán hình ảnh', 'chuan-doan-hinh-anh', 'X-quang, CT, MRI, siêu âm', 'fas fa-x-ray', 40, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22'),
(17, 'Xét nghiệm', 'xet-nghiem', 'Xét nghiệm huyết học, sinh hóa, vi sinh', 'fas fa-flask', 50, 'active', '2025-09-22 06:53:16', '2025-09-22 08:38:22');

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
-- Cấu trúc bảng cho bảng `lich_hen`
--

CREATE TABLE `lich_hen` (
  `id` int(11) NOT NULL,
  `benh_nhan_id` int(11) NOT NULL,
  `bac_si_id` int(11) NOT NULL,
  `ngay_hen` date NOT NULL,
  `gio_hen` time NOT NULL,
  `ly_do` text DEFAULT NULL,
  `loai_lich` enum('Tư vấn','Trực tiếp','Tại nhà') NOT NULL DEFAULT 'Trực tiếp',
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
(89, 28, 1, '2025-09-24', '12:00:00', '', 'Trực tiếp', NULL, '', 'Đã xác nhận', '', '2025-09-23 10:53:12', '2025-09-23 10:53:27'),
(90, 28, 1, '2025-09-24', '12:20:00', '', 'Tư vấn', NULL, 'https://us05web.zoom.us/j/88038802020?pwd=TOQy4lo2jZzOt6fCdiFsCgFcpvckPj.1', 'Đã xác nhận', '', '2025-09-23 10:59:37', '2025-09-23 10:59:50');

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
(11, 1, 'Thứ 2', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-08 08:24:08', '2025-09-08 08:24:08'),
(13, 1, 'Thứ 2', '18:00:00', '23:59:00', 'Ca tối', '', 'active', '2025-09-23 08:57:14', '2025-09-23 08:57:14'),
(14, 1, 'Thứ 3', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-23 08:58:00', '2025-09-23 08:58:00'),
(16, 1, 'Thứ 3', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-23 09:21:11', '2025-09-23 09:21:11'),
(17, 1, 'Thứ 4', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-23 09:43:24', '2025-09-23 09:43:24'),
(18, 1, 'Thứ 5', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-23 09:46:17', '2025-09-23 09:46:17'),
(19, 1, 'Thứ 6', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-23 09:46:25', '2025-09-23 09:46:25'),
(20, 1, 'Thứ 6', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-23 09:59:01', '2025-09-23 09:59:01'),
(21, 1, 'Chủ nhật', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-23 10:41:08', '2025-09-23 10:41:08'),
(22, 1, 'Thứ 4', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-23 10:42:15', '2025-09-23 10:42:15');

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
(5, 1, 21, '2025-10-05', 'modify', '06:00:00', '12:00:00', 'Ca sáng', 'cv', '2025-09-23 11:38:05');

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

--
-- Đang đổ dữ liệu cho bảng `phieu_tien_su_di_ung`
--

INSERT INTO `phieu_tien_su_di_ung` (`id`, `benh_nhan_id`, `thuoc_hoac_di_nguyen`, `so_lan_thuoc`, `khong_thuoc`, `ghi_chu_thuoc`, `con_trung`, `so_lan_con_trung`, `khong_con_trung`, `ghi_chu_con_trung`, `thuc_pham`, `so_lan_thuc_pham`, `khong_thuc_pham`, `ghi_chu_thuc_pham`, `tac_nhan_khac`, `so_lan_tac_nhan_khac`, `khong_tac_nhan_khac`, `ghi_chu_tac_nhan_khac`, `tien_su_ca_nhan`, `so_lan_tien_su_ca_nhan`, `khong_tien_su_ca_nhan`, `ghi_chu_tien_su_ca_nhan`, `tien_su_gia_dinh`, `so_lan_tien_su_gia_dinh`, `khong_tien_su_gia_dinh`, `ghi_chu_tien_su_gia_dinh`, `ngay_tao`) VALUES
(1, 28, '', '123', 1, '', 'cvxv', '', 0, '', '', '', 0, '', '', '', 0, '', '', '', 0, '', '', '', 0, '', '2025-09-19 14:24:15');

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
(1, 'benh_nhan', NULL, 21, NULL, 'success', 'Bạn đã đặt lịch hẹn thành công vào 2025-09-07 lúc 06:40', '{\"bac_si_id\":\"1\",\"ngay\":\"2025-09-07\",\"gio\":\"06:40\"}', 0, '2025-09-07 11:59:47'),
(2, 'benh_nhan', NULL, 21, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn: Đã xác nhận', '{\"appointment_id\":\"46\",\"trang_thai\":\"\\u0110\\u00e3 x\\u00e1c nh\\u1eadn\"}', 0, '2025-09-07 12:00:14'),
(3, 'benh_nhan', NULL, 21, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn: Đã xác nhận', '{\"appointment_id\":\"46\",\"trang_thai\":\"\\u0110\\u00e3 x\\u00e1c nh\\u1eadn\"}', 0, '2025-09-07 12:00:14'),
(4, 'benh_nhan', NULL, 21, NULL, 'success', 'Bạn đã đặt lịch hẹn thành công vào 2025-09-14 lúc 10:40', '{\"bac_si_id\":\"1\",\"ngay\":\"2025-09-14\",\"gio\":\"10:40\"}', 0, '2025-09-07 12:02:59'),
(5, 'benh_nhan', NULL, 21, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn: Đã xác nhận', '{\"appointment_id\":\"47\",\"trang_thai\":\"\\u0110\\u00e3 x\\u00e1c nh\\u1eadn\"}', 0, '2025-09-07 12:03:16'),
(6, 'benh_nhan', NULL, 21, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn: Hoàn thành', '{\"appointment_id\":\"47\",\"trang_thai\":\"Ho\\u00e0n th\\u00e0nh\"}', 0, '2025-09-07 12:03:43'),
(7, 'benh_nhan', NULL, 21, NULL, 'success', 'Bạn đã đặt lịch hẹn thành công vào 2025-09-07 lúc 06:20', '{\"bac_si_id\":\"1\",\"ngay\":\"2025-09-07\",\"gio\":\"06:20\"}', 0, '2025-09-07 12:06:55'),
(8, 'benh_nhan', NULL, 21, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn: Đã xác nhận', '{\"appointment_id\":\"48\",\"trang_thai\":\"\\u0110\\u00e3 x\\u00e1c nh\\u1eadn\"}', 0, '2025-09-07 12:07:26'),
(9, 'benh_nhan', NULL, 21, NULL, 'info', 'Bác sĩ GSTS. Cao Việt đã cập nhật trạng thái lịch hẹn: Đã xác nhận', '{\"appointment_id\":\"48\",\"trang_thai\":\"\\u0110\\u00e3 x\\u00e1c nh\\u1eadn\"}', 0, '2025-09-07 12:07:26');

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
  ADD KEY `idx_bn_email` (`email`),
  ADD KEY `idx_bao_hiem_y_te_id` (`bao_hiem_y_te_id`);

--
-- Chỉ mục cho bảng `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_chuyen_khoa_ten` (`ten`),
  ADD UNIQUE KEY `uniq_chuyen_khoa_slug` (`slug`);

--
-- Chỉ mục cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lich_hen_id` (`lich_hen_id`),
  ADD KEY `idx_hs_benh_nhan` (`benh_nhan_id`),
  ADD KEY `idx_hs_bac_si` (`bac_si_id`);

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
-- Chỉ mục cho bảng `lich_lam_viec_ngoai_le`
--
ALTER TABLE `lich_lam_viec_ngoai_le`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_exc` (`bac_si_id`,`schedule_id`,`ngay`),
  ADD KEY `idx_bacsi_ngay` (`bac_si_id`,`ngay`);

--
-- Chỉ mục cho bảng `phieu_tien_su_di_ung`
--
ALTER TABLE `phieu_tien_su_di_ung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptsd_benh_nhan` (`benh_nhan_id`);

--
-- Chỉ mục cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_qtv_email` (`email`);

--
-- Chỉ mục cho bảng `le_tan`
--
ALTER TABLE `le_tan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bac_si`
--
ALTER TABLE `bac_si`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT cho bảng `bao_hiem_y_te`
--
ALTER TABLE `bao_hiem_y_te`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `benh_nhan`
--
ALTER TABLE `benh_nhan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_hen`
--
ALTER TABLE `lich_hen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec_ngoai_le`
--
ALTER TABLE `lich_lam_viec_ngoai_le`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `phieu_tien_su_di_ung`
--
ALTER TABLE `phieu_tien_su_di_ung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `le_tan`
--
ALTER TABLE `le_tan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Các ràng buộc cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  ADD CONSTRAINT `ho_so_benh_an_ibfk_1` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ho_so_benh_an_ibfk_2` FOREIGN KEY (`bac_si_id`) REFERENCES `bac_si` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ho_so_benh_an_ibfk_3` FOREIGN KEY (`lich_hen_id`) REFERENCES `lich_hen` (`id`) ON DELETE SET NULL;

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
-- Các ràng buộc cho bảng `phieu_tien_su_di_ung`
--
ALTER TABLE `phieu_tien_su_di_ung`
  ADD CONSTRAINT `fk_ptsd_benh_nhan` FOREIGN KEY (`benh_nhan_id`) REFERENCES `benh_nhan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
