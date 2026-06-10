<?php
require_once __DIR__ . '/../config/config.php';

function send_mail($to, $subject, $html) {
    $phpmailerSrc = __DIR__ . '/../PHPMailer/PHPMailer/src/';

    if (is_dir($phpmailerSrc)) {
        require_once $phpmailerSrc . 'Exception.php';
        require_once $phpmailerSrc . 'PHPMailer.php';
        require_once $phpmailerSrc . 'SMTP.php';

        $m = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $m->isSMTP();
            $m->Host       = env('MAIL_HOST');
            $m->SMTPAuth   = true;
            $m->Username   = env('MAIL_USERNAME');
            $m->Password   = env('MAIL_PASSWORD');
            $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $m->Port       = (int)env('MAIL_PORT', 587);
            $m->setFrom(env('MAIL_FROM_EMAIL', 'no-reply@mindmetric.local'),
                        env('MAIL_FROM_NAME', 'MindMetric'));
            $m->addAddress($to);
            $m->isHTML(true);
            $m->Subject = $subject;
            $m->Body    = $html;
            $m->AltBody = strip_tags($html);
            return $m->send();
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    // Fallback to PHP mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . env('MAIL_FROM_NAME', 'MindMetric') . ' <' . env('MAIL_FROM_EMAIL', 'no-reply@mindmetric.local') . ">\r\n";
    return @mail($to, $subject, $html, $headers);
}

function mail_template($title, $body) {
    return '<div style="font-family:Arial,sans-serif;background:#FCD6AA;padding:32px;color:#151511">
        <div style="max-width:520px;margin:0 auto;background:#D6E4FA;border:1px solid #151511;border-radius:12px;overflow:hidden">
            <div style="background:#151511;color:#F8BC7C;padding:18px 24px;font-weight:700;letter-spacing:1px">MindMetric</div>
            <div style="padding:24px">
                <h2 style="margin:0 0 12px;color:#151511">' . htmlspecialchars($title) . '</h2>
                <div style="font-size:15px;line-height:1.5">' . $body . '</div>
            </div>
        </div>
    </div>';
}
