<?php
$page = 'Reset password';
require_once __DIR__ . '/includes/csrf.php';
$err = '';
$email = $_SESSION['reset_email'] ?? '';
if ($email === '') { header('Location: forgot-password.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $code = trim($_POST['code'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (strlen($pass) < 8) {
        $err = 'Password must be at least 8 characters.';
    } else {
        $stmt = db()->prepare('SELECT * FROM password_resets WHERE email = ? AND code = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email, $code]);
        $row = $stmt->fetch();
        if (!$row) {
            $err = 'Invalid or expired code.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            db()->prepare('UPDATE users SET password_hash = ? WHERE email = ?')->execute([$hash, $email]);
            db()->prepare('UPDATE password_resets SET used = 1 WHERE id = ?')->execute([$row['id']]);
            unset($_SESSION['reset_email']);
            header('Location: login.php?reset=1');
            exit;
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<form class="form" method="post">
    <h1>Reset password</h1>
    <p class="sub">Enter the code sent to <strong><?= sanitize($email) ?></strong> and a new password.</p>
    <?php if ($err): ?><div class="alert alert-error"><?= sanitize($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label>Reset code</label><input name="code" inputmode="numeric" pattern="[0-9]{6}" required></div>
    <div class="field"><label>New password</label><input name="password" type="password" minlength="8" required></div>
    <button class="btn btn-block" type="submit">Update password</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
