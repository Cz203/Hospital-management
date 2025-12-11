<?php

/**
 * View phiếu khám bệnh cho Patient - PDF-like interface
 */

$exam = $data['exam'] ?? [];

function escapeHtml($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$yes = function ($cond) {
    return $cond ? 'checked' : '';
};

$printSquares = function ($value, $len = 2) {
    $v = str_pad((string)($value ?? ''), $len, ' ', STR_PAD_LEFT);
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= '<span class="sq"></span>';
    }
    return '<span class="squares">' . $out . '</span>';
};

$safe = function ($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
};

// Lấy mã bệnh nhân từ exam
$maBenhNhan = $exam['ma_benh_nhan'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Khám Bệnh</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Times New Roman', serif;
        background-color: #525252;
        padding: 20px;
        min-height: 100vh;
    }

    .pdf-viewer-container {
        max-width: 1200px;
        margin: 0 auto;
        background-color: #525252;
        padding: 20px;
    }

    .pdf-toolbar {
        background-color: #2d2d2d;
        padding: 10px 20px;
        border-radius: 4px 4px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #fff;
    }

    .pdf-toolbar-left {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .pdf-toolbar-right {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .pdf-toolbar button {
        background-color: #3d3d3d;
        border: 1px solid #555;
        color: #fff;
        padding: 6px 12px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }

    .pdf-toolbar button:hover {
        background-color: #4d4d4d;
    }

    .pdf-content {
        background-color: #fff;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        min-height: 800px;
        font-family: 'Times New Roman', serif;
        color: #000;
        font-size: 12pt;
    }

    .topline {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .small {
        font-size: 11pt;
    }

    .title {
        text-align: center;
        font-weight: bold;
        font-size: 16pt;
        text-transform: uppercase;
    }

    .hr {
        border-bottom: 2px solid #000;
        margin: 6px 0 10px;
    }

    .line {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
    }

    .idx {
        width: 18px;
    }

    .lbl {
        min-width: 140px;
        font-weight: bold;
    }

    .fill {
        flex: 1;
        border-bottom: 1px dotted #000;
        min-height: 18px;
    }

    .gap {
        width: 10px;
    }

    .squares {
        display: inline-flex;
        gap: 2px;
    }

    .sq {
        width: 14px;
        height: 18px;
        border: 1px solid #000;
        display: inline-block;
    }

    .num {
        display: inline-block;
        min-width: 40px;
        border-bottom: 1px dotted #000;
        text-align: center;
        padding: 0 4px;
    }

    .checkboxes {
        display: flex;
        gap: 16px;
    }

    .cb {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .box {
        width: 14px;
        height: 14px;
        border: 1px solid #000;
        display: inline-block;
        text-align: center;
        line-height: 14px;
    }

    .checked::after {
        content: '✓';
        font-weight: bold;
        font-size: 11px;
    }

    .section-title {
        font-weight: bold;
        margin: 8px 0 6px;
    }

    .textarea {
        border: 1px solid #000;
        min-height: 80px;
        padding: 6px;
        margin-bottom: 6px;
        white-space: pre-wrap;
    }

    .two-col {
        display: flex;
        gap: 10px;
    }

    .col {
        flex: 1;
    }

    .vitals {
        border: 1px solid #000;
        padding: 6px;
        width: 230px;
    }

    .vrow {
        display: flex;
        margin-bottom: 4px;
    }

    .vrow .vlabel {
        width: 120px;
    }

    .sign {
        display: flex;
        justify-content: space-between;
        margin-top: 18px;
    }

    .sign-box {
        width: 40%;
        text-align: center;
    }

    .sign-line {
        height: 42px;
        border-bottom: 1px solid #000;
        margin-bottom: 4px;
    }

    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: #666;
        font-size: 16px;
    }

    @media print {
        body {
            background-color: #fff;
            padding: 0;
        }

        .pdf-viewer-container {
            padding: 0;
        }

        .pdf-toolbar {
            display: none;
        }

        .pdf-content {
            box-shadow: none;
            padding: 20px;
        }
    }
    </style>
</head>

<body>
    <div class="pdf-viewer-container">
        <!-- PDF Toolbar -->
        <div class="pdf-toolbar">
            <div class="pdf-toolbar-left">
                <button onclick="window.print()">
                    <i class="fas fa-print"></i> In
                </button>
                <button onclick="window.close()">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
            <div class="pdf-toolbar-right">
                <span>Trang 1</span>
            </div>
        </div>

        <!-- PDF Content -->
        <div class="pdf-content">
            <?php if ($exam && !empty($exam['id'])): ?>
            <div style="text-align:center; margin-bottom:10px;">
                <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;">
            </div>
            <div class="topline small">
                <div>Sở y tế: <span class="fill"
                        style="min-width:180px; display:inline-block;"><?= $safe($exam['so_y_te'] ?? '') ?></span></div>
                <div>MS: <strong><?= (int)$exam['id'] ?></strong></div>
            </div>
            <div class="topline small">
                <div>Bệnh viện: <span class="fill"
                        style="min-width:220px; display:inline-block;"><?= $safe($exam['benh_vien'] ?? '') ?></span>
                </div>
                <div>Mã bệnh nhân: <span class="fill"
                        style="min-width:160px; display:inline-block;"><?= $safe($maBenhNhan) ?></span></div>
            </div>
            <div class="topline small">
                <div></div>
                <div>BUỒNG KHÁM BỆNH: <span class="fill"
                        style="min-width:200px; display:inline-block;"><?= $safe($exam['buong_kham'] ?? '') ?></span>
                </div>
            </div>
            <div class="title">PHIẾU KHÁM BỆNH VÀO VIỆN</div>
            <div class="hr"></div>

            <div class="section-title">I. HÀNH CHÍNH:</div>
            <div class="line">
                <span class="idx">1.</span>
                <span class="lbl">Họ và tên (in hoa):</span>
                <span class="fill"><?= $safe($exam['ho_ten'] ?? '') ?></span>
                <span class="gap"></span>
                <span class="lbl" style="min-width:60px;">Tuổi</span>
                <span class="fill" style="max-width:80px;"><?= $safe($exam['tuoi'] ?? '') ?></span>
            </div>
            <div class="line">
                <span class="idx">2.</span>
                <span class="lbl">Sinh ngày:</span>
                <span class="num"><?= $safe($exam['ngay_sinh'] ?? '') ?></span>
                <span class="gap"></span>Tháng
                <span class="num" style="min-width:40px;"><?= $safe($exam['thang_sinh'] ?? '') ?></span>
                <span class="gap"></span>Năm
                <span class="num" style="min-width:60px;"><?= $safe($exam['nam_sinh'] ?? '') ?></span>
                <span class="gap" style="width:16px"></span>Giới: 1. Nam <span
                    class="box <?= $yes(trim($exam['gioi_tinh'] ?? '') === 'Nam') ?>"></span>
                <span class="gap"></span>2. Nữ <span
                    class="box <?= $yes(trim($exam['gioi_tinh'] ?? '') === 'Nữ' || trim($exam['gioi_tinh'] ?? '') === 'Nu') ?>"></span>
            </div>
            <div class="line"><span class="idx">3.</span><span class="lbl">Nghề nghiệp:</span><span
                    class="fill"><?= $safe($exam['nghe_nghiep'] ?? '') ?></span></div>
            <div class="line"><span class="idx">4.</span><span class="lbl">Dân tộc:</span><span
                    class="fill"><?= $safe($exam['dan_toc'] ?? '') ?></span>
                <span class="gap"></span><span class="lbl" style="min-width:100px;">Ngoại kiều:</span><span
                    class="fill"><?= $safe($exam['ngoai_kieu'] ?? '') ?></span>
            </div>
            <div class="line"><span class="idx">5.</span><span class="lbl">Nơi làm việc:</span><span
                    class="fill"><?= $safe($exam['noi_lam_viec'] ?? '') ?></span></div>
            <div class="line"><span class="idx">6.</span><span class="lbl">Địa chỉ:</span><span
                    class="fill"><?= $safe($exam['dia_chi'] ?? '') ?></span></div>
            <div class="line"><span class="idx">7.</span><span class="lbl">Đối tượng:</span>
                <span class="checkboxes">
                    <span class="cb">1. BHYT <span class="box <?= $yes($exam['doi_tuong_bhyt'] ?? 0) ?>"></span></span>
                    <span class="cb">2. Thu phí <span
                            class="box <?= $yes($exam['doi_tuong_thu_phi'] ?? 0) ?>"></span></span>
                    <span class="cb">3. Miễn <span class="box <?= $yes($exam['doi_tuong_mien'] ?? 0) ?>"></span></span>
                    <span class="cb">4. Khác <span class="box <?= $yes($exam['doi_tuong_khac'] ?? 0) ?>"></span></span>
                </span>
            </div>
            <div class="line"><span class="idx">10.</span><span class="lbl">BHYT giá trị đến:</span>
                Ngày <span class="num"><?= $safe($exam['bhyt_ngay'] ?? '') ?></span>
                Tháng <span class="num"><?= $safe($exam['bhyt_thang'] ?? '') ?></span>
                Năm <span class="num" style="min-width:60px;"><?= $safe($exam['bhyt_nam'] ?? '') ?></span>
                <span class="gap" style="width:16px"></span>Số thẻ BHYT: <span class="fill"
                    style="min-width:220px; display:inline-block;"><?= $safe($exam['so_the_bhyt'] ?? '') ?></span>
            </div>
            <div class="line"><span class="idx">11.</span><span class="lbl">Điện thoại người báo tin:</span><span
                    class="fill"><?= $safe($exam['dien_thoai_bao_tin'] ?? '') ?></span></div>
            <div class="line"><span class="idx">12.</span><span class="lbl">Đến khám bệnh lúc:</span>
                Giờ <span class="num"><?= $safe($exam['gio_kham'] ?? '') ?></span>
                Phút <span class="num"><?= $safe($exam['phut_kham'] ?? '') ?></span>
                Ngày <span class="num"><?= $safe($exam['ngay_kham'] ?? '') ?></span>
                Tháng <span class="num"><?= $safe($exam['thang_kham'] ?? '') ?></span>
                Năm <span class="num" style="min-width:60px;"><?= $safe($exam['nam_kham'] ?? '') ?></span>
            </div>
            <div class="line"><span class="idx">13.</span><span class="lbl">Chẩn đoán nơi giới thiệu:</span><span
                    class="fill"><?= $safe($exam['chan_doan_gioi_thieu'] ?? '') ?></span></div>

            <div class="section-title">II. LÝ DO VÀO VIỆN:</div>
            <div class="textarea"><?= $safe($exam['tom_tat_lam_sang'] ?? '') ?></div>

            <div class="section-title">III. HỎI BỆNH:</div>
            <div class="line"><span class="idx">1.</span><span class="lbl">Quá trình bệnh lý:</span></div>
            <div class="textarea"><?= $safe($exam['qua_trinh_benh_li'] ?? '') ?></div>
            <div class="line"><span class="idx">2.</span><span class="lbl">Tiền sử bản thân:</span></div>
            <div class="textarea"><?= $safe($exam['tien_su_ban_than'] ?? '') ?></div>
            <div class="line"><span class="idx">3.</span><span class="lbl">Gia đình:</span></div>
            <div class="textarea"><?= $safe($exam['tien_su_gia_dinh'] ?? '') ?></div>

            <div class="section-title" style="page-break-before: always; margin-top: 20px;">IV. KHÁM XÉT:</div>
            <div class="two-col">
                <div class="col">
                    <div class="line"><span class="idx">1.</span><span class="lbl">Toàn thân:</span></div>
                    <div class="textarea"><?= $safe($exam['kham_toan_than'] ?? '') ?></div>
                    <div class="line"><span class="idx">2.</span><span class="lbl">Các bộ phận:</span></div>
                    <div class="textarea"><?= $safe($exam['kham_cac_bo_phan'] ?? '') ?></div>
                    <div class="line"><span class="idx">3.</span><span class="lbl">Tóm tắt lâm sàng:</span></div>
                    <div class="textarea"><?= $safe($exam['tom_tat_lam_sang'] ?? '') ?></div>
                    <div class="line"><span class="idx">4.</span><span class="lbl">Chẩn đoán vào viện:</span></div>
                    <div class="textarea"><?= $safe($exam['chan_doan_vao_vien'] ?? '') ?></div>
                    <div class="line"><span class="idx">5.</span><span class="lbl">Đã xử lý (thuốc, chăm sóc...):</span>
                    </div>
                    <div class="textarea"><?= $safe($exam['da_xu_li'] ?? '') ?></div>
                    <div class="line"><span class="idx">6.</span><span class="lbl">Hướng xử trí:</span><span
                            class="fill"><?= $safe($exam['khoa_dieu_tri'] ?? '') ?></span></div>
                    <div class="line"><span class="idx">7.</span><span class="lbl">Chú ý:</span><span
                            class="fill"><?= $safe($exam['chu_y'] ?? '') ?></span></div>
                </div>
                <div class="vitals">
                    <div class="vrow"><span class="vlabel">Mạch:</span><span class="fill"
                            style="border:0;border-bottom:1px dotted #000;"><?= $safe($exam['mach'] ?? '') ?></span>
                        lần/phút</div>
                    <div class="vrow"><span class="vlabel">Nhiệt độ:</span><span class="fill"
                            style="border:0;border-bottom:1px dotted #000;"><?= $safe($exam['nhiet_do'] ?? '') ?></span>
                        °C</div>
                    <div class="vrow"><span class="vlabel">Huyết áp:</span><span class="fill"
                            style="border:0;border-bottom:1px dotted #000;"><?= $safe($exam['huyet_ap_tam_thu'] ?? '') ?></span>/<span
                            class="fill"
                            style="border:0;border-bottom:1px dotted #000; max-width:60px; display:inline-block;"><?= $safe($exam['huyet_ap_tam_truong'] ?? '') ?></span>
                        mmHg</div>
                    <div class="vrow"><span class="vlabel">Nhịp thở:</span><span class="fill"
                            style="border:0;border-bottom:1px dotted #000;"><?= $safe($exam['nhip_tho'] ?? '') ?></span>
                        lần/phút</div>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: right;">
                <div style="display: inline-block; text-align: center;">
                    <div style="margin-bottom: 15px;">
                        <span>Ngày</span>
                        <span
                            style="display: inline-block; width: 60px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?= $safe($exam['ngay_ky'] ?? '') ?></span>
                        <span>tháng</span>
                        <span
                            style="display: inline-block; width: 60px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?= $safe($exam['thang_ky'] ?? '') ?></span>
                        <span>năm</span>
                        <span
                            style="display: inline-block; width: 80px; margin: 0 5px; border-bottom: 1px solid #000; text-align: center;"><?= $safe($exam['nam_ky'] ?? '') ?></span>
                    </div>
                    <div>
                        <div style="font-weight: bold; font-size: 14px;">BÁC SĨ KHÁM BỆNH</div>
                        <div style="margin-top: 10px; font-size: 14px;"><?= $safe($exam['ten_bac_si'] ?? '') ?></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="no-data">
                <p>Chưa có phiếu khám bệnh</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>

</html>