<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailService
{
    private $config;

    public function __construct()
    {
        $this->config = @include __DIR__ . '/../config/mail.php';
        if (!is_array($this->config)) {
            $this->config = [];
        }
    }

    /**
     * Send an email using PHPMailer with SMTP and optional DKIM.
     * Returns true on success, false otherwise. Logs errors to error_log.
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->CharSet = 'UTF-8';
            $debug = !empty($this->config['debug']);
            // Always direct PHPMailer debug output to error_log to avoid breaking headers
            $mail->SMTPDebug = $debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
            $mail->Debugoutput = static function ($str, $level) {
                error_log('SMTP Debug (' . $level . '): ' . $str);
            };
            if (($this->config['driver'] ?? 'smtp') === 'smtp') {
                $mail->isSMTP();
                $mail->Host = $this->config['host'] ?? 'smtp.gmail.com';
                $mail->Port = (int)($this->config['port'] ?? 587);
                $enc = $this->config['encryption'] ?? 'tls';
                if ($enc === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($enc === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPSecure = false; // no encryption
                }
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['username'] ?? '';
                $mail->Password = $this->config['password'] ?? '';
            }

            // From
            $fromEmail = $this->config['from_email'] ?? 'no-reply@yourdomain.com';
            $fromName = $this->config['from_name'] ?? 'Hospital Management';
            $mail->setFrom($fromEmail, $fromName);

            // To
            $mail->addAddress($toEmail, $toName ?: $toEmail);

            // DKIM (optional but helps spam)
            $dkimDomain = trim((string)($this->config['dkim_domain'] ?? ''));
            $dkimSelector = trim((string)($this->config['dkim_selector'] ?? ''));
            $dkimPrivateKey = trim((string)($this->config['dkim_private_key'] ?? ''));
            if ($dkimDomain && $dkimSelector && $dkimPrivateKey) {
                $mail->DKIM_domain = $dkimDomain;
                $mail->DKIM_selector = $dkimSelector;
                $mail->DKIM_private = $dkimPrivateKey; // PEM formatted string
                $mail->DKIM_identity = $fromEmail;
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

            if (empty($toEmail)) {
                throw new Exception('Missing recipient email');
            }

            return $mail->send();
        } catch (Exception $e) {
            error_log('MailService send error: ' . $e->getMessage());
            return false;
        }
    }

    private function renderTemplate(string $title, string $bodyHtml, ?string $ctaText = null, ?string $ctaUrl = null): string
    {
        $brand = htmlspecialchars($this->config['from_name'] ?? 'Hospital Management');
        $cta = '';
        if ($ctaText && $ctaUrl) {
            $cta = '<div style="margin-top:20px;text-align:center">'
                . '<a href="' . htmlspecialchars($ctaUrl) . '" '
                . 'style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;"'
                . '>' . htmlspecialchars($ctaText) . '</a></div>';
        }
        return '
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . htmlspecialchars($title) . '</title>
  <style>
    body{background:#f6f7fb;margin:0;padding:0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111827}
    .container{max-width:640px;margin:0 auto;padding:24px}
    .card{background:#fff;border-radius:14px;box-shadow:0 6px 18px rgba(17,24,39,.08);overflow:hidden}
    .header{display:flex;align-items:center;gap:12px;padding:18px 22px;background:linear-gradient(135deg,#eef2ff,#eff6ff)}
    .logo{width:40px;height:40px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:700;font-size:16px}
    h1{font-size:20px;margin:0;color:#111827}
    .content{padding:22px;font-size:16px}
    .content p{line-height:1.7;margin:12px 0;font-size:16px}
    .footer{padding:16px 22px;color:#6b7280;font-size:12px;background:#fafafa;border-top:1px solid #f0f0f0}
  </style>
  <!--[if mso]><style>.content p{mso-line-height-rule:exactly;}</style><![endif]-->
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="header">
        <div class="logo">HM</div>
        <div>
          <div style="font-weight:700;font-size:16px">' . $brand . '</div>
          <div style="font-size:12px;color:#6b7280">Thông báo cuộc hẹn</div>
        </div>
      </div>
      <div class="content">
        ' . $bodyHtml . '
        ' . $cta . '
      </div>
      <div class="footer">Đây là email tự động. Vui lòng không trả lời email này.</div>
    </div>
  </div>
</body>
</html>';
    }

    public function sendAppointmentBookedToDoctor(
        array $doctor,
        array $patient,
        string $dateYmd,
        string $time,
        string $loaiLich = 'Trực tiếp',
        ?string $zoomLink = null
    ): bool {
        $toEmail = $doctor['email'] ?? '';
        $toName = $doctor['ten'] ?? 'Bác sĩ';
        if (!$toEmail) return false;

        $patientName = $patient['ten'] ?? 'Bệnh nhân';
        $dateVn = $dateYmd;
        $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
        if ($dt) $dateVn = $dt->format('d-m-Y');

        $subject = '[HM] Lịch hẹn mới từ ' . $patientName;
        $body = '<p>Xin chào Bác sĩ <strong>' . htmlspecialchars($toName) . '</strong>,</p>' .
            '<p>Bệnh nhân <strong>' . htmlspecialchars($patientName) . '</strong> đã đặt lịch <strong>' . htmlspecialchars($loaiLich) . '</strong> vào ngày <strong>' . $dateVn . '</strong> và lúc <strong>' . htmlspecialchars($time) . '</strong>.</p>';
        if ($zoomLink) {
            $body .= '<div style="margin:16px 0;text-align:center">'
                . '<a href="' . htmlspecialchars($zoomLink) . '" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600">Mở phòng tư vấn</a>'
                . '</div>';
        }
        $body .= '<p>Vui lòng đăng nhập hệ thống để xem chi tiết và xác nhận.</p>' .
            '<p>Trân trọng,</p><p>Hospital Management</p>';
        $html = $this->renderTemplate('Lịch hẹn mới', $body, null, null);
        return $this->send($toEmail, $toName, $subject, $html);
    }

    public function sendAppointmentConfirmedToPatient(array $patient, array $doctor, string $dateYmd, string $time, string $loaiLich = 'Trực tiếp', ?string $zoomLink = null): bool
    {
        $toEmail = $patient['email'] ?? '';
        $toName = $patient['ten'] ?? 'Bạn';
        if (!$toEmail) return false;

        $doctorName = $doctor['ten'] ?? 'Bác sĩ';
        $dateVn = $dateYmd;
        $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
        if ($dt) $dateVn = $dt->format('d-m-Y');

        $subject = '[HM] Lịch hẹn của bạn đã được xác nhận';
        // Tiếng Việt: Lịch hẹn của bạn với ... đã được xác nhận. Vui lòng có mặt vào ngày ... vào lúc ...
        // English: Your appointment with ... has been confirmed. Please be present on ... at ...
        $body = '<p>Xin chào <strong>' . htmlspecialchars($toName) . '</strong>,</p>' .
            '<p>Lịch hẹn của bạn với <strong>' . htmlspecialchars($doctorName) . '</strong> đã được xác nhận.</p>' .
            '<p>Vui lòng có mặt vào ngày <strong>' . $dateVn . '</strong> vào lúc <strong>' . htmlspecialchars($time) . '</strong> (' . htmlspecialchars($loaiLich) . ').</p>';
        if ($zoomLink) {
            $body .= '<div style="margin:16px 0;text-align:center">'
                . '<a href="' . htmlspecialchars($zoomLink) . '" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600">Tham gia buổi tư vấn</a>'
                . '</div>';
        }
        $body .= '<p>Trân trọng,</p><p>Hospital Management</p>';
        $html = $this->renderTemplate('Xác nhận lịch hẹn', $body, null, null);
        return $this->send($toEmail, $toName, $subject, $html);
    }

    public function sendAppointmentConfirmedReminderToDoctor(
        array $doctor,
        array $patient,
        string $dateYmd,
        string $time,
        string $loaiLich = 'Trực tiếp',
        ?string $zoomLink = null
    ): bool {
        $toEmail = $doctor['email'] ?? '';
        $toName = $doctor['ten'] ?? 'Bác sĩ';
        if (!$toEmail) return false;

        $patientName = $patient['ten'] ?? 'Bệnh nhân';
        $dateVn = $dateYmd;
        $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
        if ($dt) $dateVn = $dt->format('d-m-Y');

        $subject = '[HM] Đã xác nhận lịch hẹn với ' . $patientName;
        $body = '<p>Xin chào Bác sĩ <strong>' . htmlspecialchars($toName) . '</strong>,</p>'
            . '<p>Bạn vừa xác nhận lịch hẹn <strong>' . htmlspecialchars($loaiLich) . '</strong> với <strong>' . htmlspecialchars($patientName) . '</strong> vào ngày <strong>' . $dateVn . '</strong> và lúc <strong>' . htmlspecialchars($time) . '</strong>.</p>'
            . '<p>Vui lòng chuẩn bị trước nội dung cần thiết cho cuộc hẹn.</p>';
        if ($zoomLink) {
            $body .= '<div style="margin:16px 0;text-align:center">'
                . '<a href="' . htmlspecialchars($zoomLink) . '" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600">Tham gia buổi tư vấn</a>'
                . '</div>';
        }
        $body .= '<p>Trân trọng,</p><p>Hospital Management</p>';
        $html = $this->renderTemplate('Nhắc lịch: Bạn đã xác nhận', $body, null, null);
        return $this->send($toEmail, $toName, $subject, $html);
    }

    public function sendPatientCancelledToDoctor(
        array $doctor,
        array $patient,
        string $dateYmd,
        string $time,
        string $loaiLich = 'Trực tiếp',
        ?string $cancelDateYmd = null,
        ?string $cancelTime = null
    ): bool {
        $toEmail = $doctor['email'] ?? '';
        $toName = $doctor['ten'] ?? 'Bác sĩ';
        if (!$toEmail) return false;

        $patientName = $patient['ten'] ?? 'Bệnh nhân';
        $dateVn = $dateYmd;
        $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
        if ($dt) $dateVn = $dt->format('d-m-Y');

        $subject = '[HM] Bệnh nhân đã hủy lịch hẹn';
        $cancelDateVn = $cancelDateYmd;
        if ($cancelDateYmd) {
            $d2 = DateTime::createFromFormat('Y-m-d', $cancelDateYmd);
            if ($d2) $cancelDateVn = $d2->format('d-m-Y');
        }
        $body = '<p>Xin chào Bác sĩ <strong>' . htmlspecialchars($toName) . '</strong>,</p>'
            . '<p><strong>Rất tiếc</strong>, Lịch hẹn của bệnh nhân <strong>' . htmlspecialchars($patientName) . '</strong> vào ngày '
            . '<strong>' . $dateVn . '</strong> và lúc <strong>' . htmlspecialchars($time) . '</strong> (' . htmlspecialchars($loaiLich) . ')'
            . ' đã bị <strong>bệnh nhân hủy</strong>'
            . ($cancelDateYmd && $cancelTime ? ' vào ngày <strong>' . $cancelDateVn . '</strong> và lúc <strong>' . htmlspecialchars($cancelTime) . '</strong>' : '')
            . '.</p>'
            . '<p>Vui lòng cập nhật lịch làm việc nếu cần thiết.</p>'
            . '<p>Trân trọng,</p><p>Hospital Management</p>';
        $html = $this->renderTemplate('Thông báo hủy lịch hẹn', $body, null, null);
        return $this->send($toEmail, $toName, $subject, $html);
    }

    public function sendDoctorCancelledToPatient(
        array $patient,
        array $doctor,
        string $dateYmd,
        string $time,
        string $loaiLich = 'Trực tiếp',
        ?string $cancelDateYmd = null,
        ?string $cancelTime = null
    ): bool {
        $toEmail = $patient['email'] ?? '';
        $toName = $patient['ten'] ?? 'Bạn';
        if (!$toEmail) return false;

        $doctorName = $doctor['ten'] ?? 'Bác sĩ';
        $dateVn = $dateYmd;
        $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
        if ($dt) $dateVn = $dt->format('d-m-Y');

        $subject = '[HM] Lịch hẹn của bạn đã bị hủy';
        $cancelDateVn = $cancelDateYmd;
        if ($cancelDateYmd) {
            $d2 = DateTime::createFromFormat('Y-m-d', $cancelDateYmd);
            if ($d2) $cancelDateVn = $d2->format('d-m-Y');
        }
        $body = '<p>Xin chào <strong>' . htmlspecialchars($toName) . '</strong>,</p>'
            . '<p><strong>Rất tiếc</strong>, Lịch hẹn của bạn vào ngày <strong>' . $dateVn . '</strong> và lúc <strong>' . htmlspecialchars($time) . '</strong> (' . htmlspecialchars($loaiLich) . ')'
            . ' đã bị <strong>' . htmlspecialchars($doctorName) . '</strong> từ chối'
            . ($cancelDateYmd && $cancelTime ? ' vào ngày <strong>' . $cancelDateVn . '</strong> và lúc <strong>' . htmlspecialchars($cancelTime) . '</strong>' : '')
            . '.</p>'
            . '<p>Vui lòng đặt lịch lại vào thời gian khác phù hợp.</p>'
            . '<p>Trân trọng,</p><p>Hospital Management</p>';
        $html = $this->renderTemplate('Thông báo hủy lịch hẹn', $body, null, null);
        return $this->send($toEmail, $toName, $subject, $html);
    }
}