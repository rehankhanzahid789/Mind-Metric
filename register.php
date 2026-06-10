<?php
$page = 'Create account';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/mailer.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $pass === '') {
        $err = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid email address.';
    } elseif (strlen($pass) < 8) {
        $err = 'Password must be at least 8 characters.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $err = 'An account with that email already exists.';
        } else {
            $code = (string)random_int(100000, 999999);
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            db()->prepare('DELETE FROM email_verifications WHERE email = ?')->execute([$email]);
            db()->prepare('INSERT INTO email_verifications (email, name, password_hash, code, expires_at) VALUES (?,?,?,?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')
                ->execute([$email, $name, $hash, $code]);

            $body = mail_template('Verify your MindMetric account',
                '<p>Welcome, ' . sanitize($name) . '.</p>
                 <p>Your verification code is:</p>
                 <p style="font-size:28px;font-weight:800;letter-spacing:6px;color:#151511;background:#F8BC7C;padding:14px 18px;border-radius:10px;display:inline-block">' . $code . '</p>
                 <p style="margin-top:14px">The code expires in 30 minutes.</p>');
            send_mail($email, 'Your MindMetric verification code', $body);

            $_SESSION['pending_email'] = $email;
            header('Location: verify.php');
            exit;
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<form class="form" method="post" novalidate>
    <h1>Create your account</h1>
    <p class="sub">Start measuring your cognitive performance.</p>
    <?php if ($err): ?><div class="alert alert-error"><?= sanitize($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label>Name</label><input name="name" required maxlength="120" value="<?= sanitize($_POST['name'] ?? '') ?>"></div>
    <div class="field"><label>Email</label><input name="email" type="email" required maxlength="190" value="<?= sanitize($_POST['email'] ?? '') ?>"></div>
    <div class="field"><label>Password</label><input name="password" type="password" required minlength="8"></div>
    <button class="btn btn-block" type="submit">Send verification code</button>
    <div class="form-foot">Already have an account? <a href="login.php">Sign in</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
