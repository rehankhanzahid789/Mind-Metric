<?php
$page = 'Sign in';
require_once __DIR__ . '/includes/csrf.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    if ($email === '' || $pass === '') {
        $err = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid email address.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u) {
            $err = 'No account found for that email.';
        } elseif (!password_verify($pass, $u['password_hash'])) {
            $err = 'Incorrect password.';
        } elseif (!$u['is_verified']) {
            $err = 'Account not verified. Please check your email.';
        } else {
            login_user((int)$u['id']);
            header('Location: dashboard.php');
            exit;
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<form class="form" method="post">
    <h1>Welcome back</h1>
    <p class="sub">Sign in to continue your assessments.</p>
    <?php if ($err): ?><div class="alert alert-error"><?= sanitize($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label>Email</label><input name="email" type="email" required value="<?= sanitize($_POST['email'] ?? '') ?>"></div>
    <div class="field"><label>Password</label><input name="password" type="password" required></div>
    <button class="btn btn-block" type="submit">Sign in</button>
    <div class="form-foot"><a href="forgot-password.php">Forgot password?</a> · <a href="register.php">Create account</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
