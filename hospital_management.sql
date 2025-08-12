-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th8 12, 2025 lúc 11:52 AM
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
(1, 'BS. Phạm Văn Dũng', 'tm.phamdung@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000001', 'Tim mạch', 'TM001', 15, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/phamvandung.jpg'),
(2, 'BS. Ngô Thị Giang', 'tm.ngogiang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000002', 'Tim mạch', 'TM002', 12, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/ngothigiang.jpg'),
(3, 'BS. Trịnh Văn Khoa', 'tk.trinhkhoa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901000003', 'Thần kinh', 'TK001', 14, '2025-08-12 09:06:03', '2025-08-12 09:06:03', 'images/bacsi/trinhvankhoa.jpg'),
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
-- Cấu trúc bảng cho bảng `benh_nhan`
--

CREATE TABLE `benh_nhan` (
  `id` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
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

INSERT INTO `benh_nhan` (`id`, `ten`, `email`, `mat_khau`, `so_dien_thoai`, `ngay_sinh`, `gioi_tinh`, `dia_chi`, `nhom_mau`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Quốc Việt', 'caoduongvietquoc@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', '1990-01-01', 'Nu', 'Ha Noi', 'A+', '2025-08-12 07:44:29', '2025-08-12 09:34:22');

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
  `trang_thai` enum('cho_xac_nhan','da_xac_nhan','hoan_thanh','huy') DEFAULT 'cho_xac_nhan',
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(1, 'Admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', '2025-08-12 07:44:29', '2025-08-12 09:34:53');

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
-- Chỉ mục cho bảng `benh_nhan`
--
ALTER TABLE `benh_nhan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_bn_email` (`email`);

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
-- Chỉ mục cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_qtv_email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bac_si`
--
ALTER TABLE `bac_si`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `benh_nhan`
--
ALTER TABLE `benh_nhan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `ho_so_benh_an`
--
ALTER TABLE `ho_so_benh_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lich_hen`
--
ALTER TABLE `lich_hen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
