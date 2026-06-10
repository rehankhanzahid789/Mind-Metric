<?php
$page = 'Verify email';
require_once __DIR__ . '/includes/csrf.php';
$err = '';
$email = $_SESSION['pending_email'] ?? '';
if ($email === '') { header('Location: register.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $code = trim($_POST['code'] ?? '');
    $stmt = db()->prepare('SELECT * FROM email_verifications WHERE email = ? AND code = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute([$email, $code]);
    $row = $stmt->fetch();
    if (!$row) {
        $err = 'Invalid or expired code.';
    } else {
        // Now create the account
        $ins = db()->prepare('INSERT INTO users (name, email, password_hash, is_verified) VALUES (?,?,?,1)');
        $ins->execute([$row['name'], $row['email'], $row['password_hash']]);
        $uid = (int)db()->lastInsertId();
        db()->prepare('DELETE FROM email_verifications WHERE email = ?')->execute([$email]);
        unset($_SESSION['pending_email']);
        login_user($uid);
        header('Location: dashboard.php');
        exit;
    }
}
require __DIR__ . '/includes/header.php';
?>
<form class="form" method="post">
    <h1>Verify your email</h1>
    <p class="sub">We sent a 6-digit code to <strong><?= sanitize($email) ?></strong>.</p>
    <?php if ($err): ?><div class="alert alert-error"><?= sanitize($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label>Verification code</label><input name="code" inputmode="numeric" pattern="[0-9]{6}" required></div>
    <button class="btn btn-block" type="submit">Verify &amp; activate</button>
    <div class="form-foot">Wrong email? <a href="register.php">Start over</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
