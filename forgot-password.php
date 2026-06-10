<?php
$page = 'Forgot password';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/mailer.php';
$msg = ''; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid email.';
    } else {
        $stmt = db()->prepare('SELECT id, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u) {
            $code = (string)random_int(100000, 999999);
            $token = bin2hex(random_bytes(24));
            db()->prepare('INSERT INTO password_resets (email, code, token, expires_at) VALUES (?,?,?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')
                ->execute([$email, $code, $token]);
            $body = mail_template('Reset your MindMetric password',
                '<p>Hi ' . sanitize($u['name']) . ',</p>
                 <p>Your password reset code is:</p>
                 <p style="font-size:28px;font-weight:800;letter-spacing:6px;color:#151511;background:#F8BC7C;padding:14px 18px;border-radius:10px;display:inline-block">' . $code . '</p>
                 <p style="margin-top:14px">It expires in 30 minutes.</p>');
            send_mail($email, 'MindMetric password reset', $body);
            $_SESSION['reset_email'] = $email;
            header('Location: reset-password.php');
            exit;
        }
        $msg = 'If that email exists, a reset code has been sent.';
    }
}
require __DIR__ . '/includes/header.php';
?>
<form class="form" method="post">
    <h1>Forgot password?</h1>
    <p class="sub">Enter your email and we'll send you a reset code.</p>
    <?php if ($err): ?><div class="alert alert-error"><?= sanitize($err) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert alert-ok"><?= sanitize($msg) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label>Email</label><input name="email" type="email" required></div>
    <button class="btn btn-block" type="submit">Send reset code</button>
    <div class="form-foot"><a href="login.php">Back to sign in</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
