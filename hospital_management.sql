-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 19, 2025 lúc 03:40 PM
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
-- Cấu trúc bảng cho bảng `bac_si`
--

CREATE TABLE `bac_si` (
  `id` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
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

INSERT INTO `bac_si` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `chuyen_khoa`, `so_giay_phep`, `so_nam_kinh_nghiem`, `ngay_tao`, `ngay_cap_nhat`, `hinh_anh`) VALUES
(1, 'GSTS. Cao Việt', 'caoviet5.work@gmail.com', '$2y$10$6GgJ61STU3mG2zkFGZ4Eo.XeBivScR/N9wmFNVbLuFuGYcZs7ujPa', '0901000001', 'Tim mạch', 'TM001', 15, '2025-08-12 09:06:03', '2025-09-10 18:57:33', 'uploads/bacsiviet.png'),
(2, 'BS. Ngô Thị Giang', 'tm.ngogiang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000002', 'Tim mạch', 'TM002', 12, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/ngothigiang.jpg'),
(3, 'BS. Trịnh Văn Khoa', 'tk.trinhkhoa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000003', 'Thần kinh', 'TK001', 14, '2025-08-12 09:06:03', '2025-08-23 19:21:05', 'uploads/avt-bac-si-the-truong-1.png'),
(4, 'BS. Nguyễn Văn Minh', 'tk.nguyenminh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000004', 'Thần kinh', 'TK002', 10, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyenvanminh.jpg'),
(5, 'BS. Hoàng Thị Em', 'nhi.hoangem@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000005', 'Nhi khoa', 'NK001', 11, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/hoangthiem.jpg'),
(6, 'BS. Lý Thị Hoa', 'nhi.lyhoa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000006', 'Nhi khoa', 'NK002', 9, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/lythihoa.jpg'),
(7, 'BS. Lê Thị Cẩm', 'san.lecam@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000007', 'Sản phụ khoa', 'SK001', 13, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/lethicam.jpg'),
(8, 'BS. Mai Thị Lan', 'san.mailan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000008', 'Sản phụ khoa', 'SK002', 8, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/maithilan.jpg'),
(9, 'BS. Trần Thu Trang', 'dalieu.trang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000009', 'Da liễu', 'DL001', 10, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/tranthutrang.jpg'),
(10, 'BS. Phạm Đức Long', 'dalieu.long@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000010', 'Da liễu', 'DL002', 7, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/phamduclong.jpg'),
(11, 'BS. Vũ Thị Hạnh', 'mat.hanh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000011', 'Mắt', 'MT001', 12, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/vuthihanh.jpg'),
(12, 'BS. Đỗ Minh Khôi', 'mat.khoi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000012', 'Mắt', 'MT002', 6, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/dominhkhoi.jpg'),
(13, 'BS. Lâm Quốc Bảo', 'tmh.baolam@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000013', 'Tai mũi họng', 'TMH001', 9, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/lamquocbao.jpg'),
(14, 'BS. Nguyễn Ngọc Ánh', 'tmh.anhngoc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000014', 'Tai mũi họng', 'TMH002', 7, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyenngocanh.jpg'),
(15, 'BS. Tạ Minh Tuấn', 'rhm.tuan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000015', 'Răng hàm mặt', 'RHM001', 10, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/taminhtuan.jpg'),
(16, 'BS. Bùi Thị Kim', 'rhm.kim@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000016', 'Răng hàm mặt', 'RHM002', 5, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/buithikim.jpg'),
(17, 'BS. Đặng Quang Huy', 'ctch.huy@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000017', 'Chấn thương chỉnh hình', 'CTCH001', 12, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/dangquanghuy.jpg'),
(18, 'BS. Lưu Văn Tín', 'ctch.tin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000018', 'Chấn thương chỉnh hình', 'CTCH002', 7, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/luuvantin.jpg'),
(19, 'BS. Phan Thanh Hải', 'ungbuou.hai@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000019', 'Ung bướu', 'UB001', 13, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/phanthanhhai.jpg'),
(20, 'BS. Nguyễn Thị Yến', 'ungbuou.yen@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000020', 'Ung bướu', 'UB002', 9, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyenthiyen.jpg'),
(21, 'BS. Trương Minh Đức', 'noitiet.duc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000021', 'Nội tiết', 'NT001', 11, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/truongminhduc.jpg'),
(22, 'BS. Lê Thanh Hằng', 'noitiet.hang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000022', 'Nội tiết', 'NT002', 8, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/lethanhhang.jpg'),
(23, 'BS. Võ Quốc Khánh', 'tieuhua.khanh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000023', 'Tiêu hóa', 'TH001', 10, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/voquockhanh.jpg'),
(24, 'BS. Phạm Thị Mỹ', 'tieuhua.my@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000024', 'Tiêu hóa', 'TH002', 6, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/phamthimy.jpg'),
(25, 'BS. Nguyễn Văn An', 'noi.nguyenan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000025', 'Nội tổng quát', 'NQ001', 10, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyenvanan.jpg'),
(26, 'BS. Trần Thị Bình', 'noi.tranbinh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000026', 'Nội tổng quát', 'NQ002', 7, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/tranthibinh.jpg'),
(27, 'BS. Đỗ Văn Nam', 'namkhoa.donam@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000027', 'Nam khoa', 'NKH001', 9, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/dovannam.jpg'),
(28, 'BS. Phùng Hữu Phúc', 'namkhoa.phuc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000028', 'Nam khoa', 'NKH002', 6, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/phunghuuphuc.jpg'),
(29, 'BS. Trần Bảo Châu', 'truyennhiem.chau@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000029', 'Truyền nhiễm', 'TN001', 12, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/tranbaochau.jpg'),
(30, 'BS. Nguyễn Tuấn Kiệt', 'truyennhiem.kiet@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000030', 'Truyền nhiễm', 'TN002', 7, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyentuankiet.jpg'),
(31, 'BS. Lê Phước Lộc', 'cdha.loc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000031', 'Chẩn đoán hình ảnh', 'CDHA001', 10, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/lephuocloc.jpg'),
(32, 'BS. Nguyễn Diễm My', 'cdha.my@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000032', 'Chẩn đoán hình ảnh', 'CDHA002', 5, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyendiemmy.jpg'),
(33, 'BS. Phan Thanh Tùng', 'xn.tung@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000033', 'Xét nghiệm', 'XN001', 9, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/phanthanhtung.jpg'),
(34, 'BS. Nguyễn Thị Tú', 'xn.tu@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000034', 'Xét nghiệm', 'XN002', 6, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/nguyenthitu.jpg'),
(35, 'BS. Vũ Văn Phúc', 'ngoai.vuphuc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000035', 'Ngoại khoa', 'NG001', 14, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/vuvanphuc.jpg'),
(36, 'BS. Hoàng Thị Dung', 'ngoai.hoangdung@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000036', 'Ngoại khoa', 'NG002', 9, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/hoangthidung.jpg');

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
(28, 'Việt', 'caoduongvietquoc@gmail.com', '$2y$10$ujZnPMbxVgX1tgQaPWMnr.yU07aISutD3ehgWy5OiMhaIQWh5imWe', '84913998110', 1, NULL, NULL, '2003-03-22', 'Nam', '51/16A Phạm Văn Chiêu', 'A+', '2025-09-15 11:19:11', '2025-09-15 11:22:23');

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
(80, 21, 1, '2025-09-12', '08:20:00', '', 'Tư vấn', NULL, 'https://us05web.zoom.us/j/82047639256?pwd=I4eHbOIPx1xoIb73V6QRwlVV5T0gvT.1', 'Đã xác nhận', '', '2025-09-10 20:43:02', '2025-09-10 20:43:08'),
(81, 21, 1, '2025-09-12', '08:00:00', 'zxc', 'Tư vấn', NULL, 'https://us05web.zoom.us/j/85103772356?pwd=ZsEtGeNC2SlNcNPfdkTC8v5QUY3fx9.1', 'Đã xác nhận', '', '2025-09-10 20:45:24', '2025-09-10 20:45:29'),
(82, 21, 1, '2025-09-12', '07:20:00', 'zcbv', 'Tư vấn', NULL, 'https://us05web.zoom.us/j/85653100932?pwd=Cab2e1CPeUk4mDa2Llw6omSbxtRbqp.1', 'Đã xác nhận', '', '2025-09-10 21:04:16', '2025-09-10 21:04:32');

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
(1, 1, 'Thứ 2', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-06 08:38:38', '2025-09-06 08:38:38'),
(3, 1, 'Thứ 6', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-06 08:38:52', '2025-09-06 08:38:52'),
(4, 1, 'Thứ 3', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-06 08:43:54', '2025-09-06 08:43:54'),
(8, 1, 'Thứ 7', '18:00:00', '23:59:00', 'Ca tối', '', 'active', '2025-09-06 12:17:05', '2025-09-06 12:17:05'),
(9, 1, 'Chủ nhật', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-06 12:25:07', '2025-09-06 12:25:07'),
(10, 1, 'Thứ 4', '06:00:00', '12:00:00', 'Ca sáng', '', 'active', '2025-09-07 21:51:05', '2025-09-07 21:51:05'),
(11, 1, 'Thứ 2', '12:00:00', '18:00:00', 'Ca chiều', '', 'active', '2025-09-08 08:24:08', '2025-09-08 08:24:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD UNIQUE KEY `email` (`email`);

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
-- Chỉ mục cho bảng `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone_number` (`phone_number`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_is_used` (`is_used`);

--
-- Chỉ mục cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_qtv_email` (`email`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
-- AUTO_INCREMENT cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_hen`
--
ALTER TABLE `lich_hen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT cho bảng `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
-- Các ràng buộc cho các bảng đã đổ
--

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
