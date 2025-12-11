<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu khám bệnh vào viện - MS: <?= (int)$record['id'] ?></title>
    <style>
    @page {
        size: A4;
        margin: 1.5cm;
    }

    body {
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

    .sub {
        text-align: center;
        margin-bottom: 8px;
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

    @media print {
        .no-print {
            display: none
        }
    }
    </style>
    <?php
    $printSquares = function ($value, $len = 2) {
        $v = str_pad((string)($value ?? ''), $len, ' ', STR_PAD_LEFT);
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= '<span class="sq"></span>';
        }
        return '<span class="squares">' . $out . '</span>';
    };
    $yes = function ($cond) {
        return $cond ? 'checked' : '';
    };
    $safe = function ($v) {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    };
    ?>
</head>

<body onload="window.print()">
    <div style="text-align:center; margin-bottom:10px;">
        <img src="assets/img/logophieu/gen-n-logophieu.jpg" alt="Logo" style="height:60px;object-fit:contain;">
    </div>
    <div class="topline small">
        <div>Sở y tế: <span class="fill"
                style="min-width:180px; display:inline-block;"><?= $safe($record['so_y_te'] ?? '') ?></span></div>
        <div>MS: <strong><?= (int)$record['id'] ?></strong></div>
    </div>
    <div class="topline small">
        <div>Bệnh viện: <span class="fill"
                style="min-width:220px; display:inline-block;"><?= $safe($record['benh_vien'] ?? '') ?></span></div>
        <div>Mã bệnh nhân: <span class="fill"
                style="min-width:160px; display:inline-block;"><?= $safe($record['ma_benh_nhan'] ?? '') ?></span></div>
    </div>
    <div class="topline small">
        <div></div>
        <div>BUỒNG KHÁM BỆNH: <span class="fill"
                style="min-width:200px; display:inline-block;"><?= $safe($record['buong_kham'] ?? '') ?></span></div>
    </div>
    <div class="title">PHIẾU KHÁM BỆNH VÀO VIỆN</div>
    <div class="hr"></div>

    <div class="section-title">I. HÀNH CHÍNH:</div>
    <div class="line"><span class="idx">1.</span><span class="lbl">Họ và tên (in hoa):</span><span
            class="fill"><?= $safe($record['ho_ten'] ?? '') ?></span>
        <span class="gap"></span><span class="lbl" style="min-width:60px;">Tuổi</span><span class="fill"
            style="max-width:80px;"></span>
    </div>
    <div class="line"><span class="idx">2.</span><span class="lbl">Sinh ngày:</span>
        <span class="num"><?= $safe($record['ngay_sinh'] ?? '') ?></span>
        <span class="gap"></span>Tháng
        <span class="num" style="min-width:40px;">
            <?= $safe($record['thang_sinh'] ?? '') ?>
        </span>
        <span class="gap"></span>Năm
        <span class="num" style="min-width:60px;">
            <?= $safe($record['nam_sinh'] ?? '') ?>
        </span>
        <span class="gap" style="width:16px"></span>Giới: 1. Nam <span
            class="box <?= $yes(trim($record['gioi_tinh'] ?? '') === 'Nam') ?>"></span>
        <span class="gap"></span>2. Nữ <span
            class="box <?= $yes(trim($record['gioi_tinh'] ?? '') === 'Nữ' || trim($record['gioi_tinh'] ?? '') === 'Nu') ?>"></span>
    </div>
    <div class="line"><span class="idx">3.</span><span class="lbl">Nghề nghiệp:</span><span
            class="fill"><?= $safe($record['nghe_nghiep'] ?? '') ?></span></div>
    <div class="line"><span class="idx">4.</span><span class="lbl">Dân tộc:</span><span
            class="fill"><?= $safe($record['dan_toc'] ?? '') ?></span>
        <span class="gap"></span><span class="lbl" style="min-width:100px;">Ngoại kiều:</span><span
            class="fill"><?= $safe($record['ngoai_kieu'] ?? '') ?></span>
    </div>
    <div class="line"><span class="idx">5.</span><span class="lbl">Nơi làm việc:</span><span
            class="fill"><?= $safe($record['noi_lam_viec'] ?? '') ?></span></div>
    <div class="line"><span class="idx">6.</span><span class="lbl">Địa chỉ:</span><span
            class="fill"><?= $safe($record['dia_chi'] ?? '') ?></span></div>
    <div class="line"><span class="idx">7.</span><span class="lbl">Đối tượng:</span>
        <span class="checkboxes">
            <span class="cb">1. BHYT <span class="box <?= $yes($record['doi_tuong_bhyt'] ?? 0) ?>"></span></span>
            <span class="cb">2. Thu phí <span class="box <?= $yes($record['doi_tuong_thu_phi'] ?? 0) ?>"></span></span>
            <span class="cb">3. Miễn <span class="box <?= $yes($record['doi_tuong_mien'] ?? 0) ?>"></span></span>
            <span class="cb">4. Khác <span class="box <?= $yes($record['doi_tuong_khac'] ?? 0) ?>"></span></span>
        </span>
    </div>
    <div class="line"><span class="idx">10.</span><span class="lbl">BHYT giá trị đến:</span>
        Ngày <span class="num"><?= $safe($record['bhyt_ngay'] ?? '') ?></span>
        Tháng <span class="num"><?= $safe($record['bhyt_thang'] ?? '') ?></span>
        Năm <span class="num" style="min-width:60px;"><?= $safe($record['bhyt_nam'] ?? '') ?></span>
        <span class="gap" style="width:16px"></span>Số thẻ BHYT: <span class="fill"
            style="min-width:220px; display:inline-block;"><?= $safe($record['so_the_bhyt'] ?? '') ?></span>
    </div>
    <div class="line"><span class="idx">11.</span><span class="lbl">Điện thoại người báo tin:</span><span
            class="fill"><?= $safe($record['dien_thoai_bao_tin'] ?? '') ?></span></div>
    <div class="line"><span class="idx">12.</span><span class="lbl">Đến khám bệnh lúc:</span>
        Giờ <span class="num"><?= $safe($record['gio_kham'] ?? '') ?></span>
        Phút <span class="num"><?= $safe($record['phut_kham'] ?? '') ?></span>
        Ngày <span class="num"><?= $safe($record['ngay_kham'] ?? '') ?></span>
        Tháng <span class="num"><?= $safe($record['thang_kham'] ?? '') ?></span>
        Năm <span class="num" style="min-width:60px;"><?= $safe($record['nam_kham'] ?? '') ?></span>
    </div>
    <div class="line"><span class="idx">13.</span><span class="lbl">Chẩn đoán nơi giới thiệu:</span><span
            class="fill"><?= $safe($record['chan_doan_gioi_thieu'] ?? '') ?></span></div>

    <div class="section-title">II. LÝ DO VÀO VIỆN:</div>
    <div class="textarea"><?= $safe($record['tom_tat_lam_sang'] ?? '') ?></div>

    <div class="section-title">III. HỎI BỆNH:</div>
    <div class="line"><span class="idx">1.</span><span class="lbl">Quá trình bệnh lý:</span></div>
    <div class="textarea"><?= $safe($record['qua_trinh_benh_li'] ?? '') ?></div>
    <div class="line"><span class="idx">2.</span><span class="lbl">Tiền sử bản thân:</span></div>
    <div class="textarea"><?= $safe($record['tien_su_ban_than'] ?? '') ?></div>
    <div class="line"><span class="idx">3.</span><span class="lbl">Gia đình:</span></div>
    <div class="textarea"><?= $safe($record['tien_su_gia_dinh'] ?? '') ?></div>

    <div class="section-title" style="page-break-before: always; margin-top: 20px;">IV. KHÁM XÉT:</div>
    <div class="two-col">
        <div class="col">
            <div class="line"><span class="idx">1.</span><span class="lbl">Toàn thân:</span></div>
            <div class="textarea"><?= $safe($record['kham_toan_than'] ?? '') ?></div>
            <div class="line"><span class="idx">2.</span><span class="lbl">Các bộ phận:</span></div>
            <div class="textarea"><?= $safe($record['kham_cac_bo_phan'] ?? '') ?></div>
            <div class="line"><span class="idx">3.</span><span class="lbl">Tóm tắt lâm sàng:</span></div>
            <div class="textarea"><?= $safe($record['tom_tat_lam_sang'] ?? '') ?></div>
            <div class="line"><span class="idx">4.</span><span class="lbl">Chẩn đoán vào viện:</span></div>
            <div class="textarea"><?= $safe($record['chan_doan_vao_vien'] ?? '') ?></div>
            <div class="line"><span class="idx">5.</span><span class="lbl">Đã xử lý (thuốc, chăm sóc...):</span></div>
            <div class="textarea"><?= $safe($record['da_xu_li'] ?? '') ?></div>
            <div class="line"><span class="idx">6.</span><span class="lbl">Hướng xử trí:</span><span
                    class="fill"><?= $safe($record['khoa_dieu_tri'] ?? '') ?></span></div>
            <div class="line"><span class="idx">7.</span><span class="lbl">Chú ý:</span><span
                    class="fill"><?= $safe($record['chu_y'] ?? '') ?></span></div>
        </div>
        <div class="vitals">
            <div class="vrow"><span class="vlabel">Mạch:</span><span class="fill"
                    style="border:0;border-bottom:1px dotted #000;"><?= $safe($record['mach'] ?? '') ?></span> lần/phút
            </div>
            <div class="vrow"><span class="vlabel">Nhiệt độ:</span><span class="fill"
                    style="border:0;border-bottom:1px dotted #000;"><?= $safe($record['nhiet_do'] ?? '') ?></span> °C
            </div>
            <div class="vrow"><span class="vlabel">Huyết áp:</span><span class="fill"
                    style="border:0;border-bottom:1px dotted #000;"><?= $safe($record['huyet_ap_tam_thu'] ?? '') ?></span>/<span
                    class="fill"
                    style="border:0;border-bottom:1px dotted #000; max-width:60px; display:inline-block;"><?= $safe($record['huyet_ap_tam_truong'] ?? '') ?></span>
                mmHg</div>
            <div class="vrow"><span class="vlabel">Nhịp thở:</span><span class="fill"
                    style="border:0;border-bottom:1px dotted #000;"><?= $safe($record['nhip_tho'] ?? '') ?></span>
                lần/phút</div>
        </div>
    </div>

    <div class="sign">
        <div class="sign-box">
            <div>Ngày <?= $safe($record['ngay_ky'] ?? '') ?> tháng <?= $safe($record['thang_ky'] ?? '') ?> năm
                <?= $safe($record['nam_ky'] ?? '') ?></div>
        </div>
        <div class="sign-box">
            <div><strong>BÁC SĨ KHÁM BỆNH</strong></div>
            <div class="sign-line"></div>
            <div><?= $safe($record['ten_bac_si'] ?? '') ?></div>
        </div>
    </div>
</body>

</html>