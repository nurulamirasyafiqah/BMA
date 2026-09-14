<?php
session_start();
require_once '../config.php';

if (isset($_SESSION['pb_token'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $auth = PocketBase::authWithPassword('admins', $identity, $password);

    if ($auth && isset($auth['token'], $auth['record'])) {
        $_SESSION['pb_token']       = $auth['token'];
        $_SESSION['admin_id']       = $auth['record']['id'];
        $_SESSION['admin_username'] = $auth['record']['email'];
        $_SESSION['admin_name']     = $auth['record']['name'] ?? '';
        $_SESSION['admin_role']     = $auth['record']['role'] ?? 'admin';
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login — BidMyAsset</title>
<style>
  body{margin:0;font-family:Arial, Helvetica, sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;height:100vh;}
  .box{background:#fff;border:1px solid #e2e2e2;padding:40px;width:340px;border-radius:4px;}
  .box h1{font-size:18px;margin:0 0 22px 0;text-align:center;color:#1b2a4a;}
  .field{margin-bottom:16px;}
  .field label{display:block;font-size:13px;margin-bottom:6px;font-weight:600;}
  .field input{width:100%;padding:10px;border:1px solid #e2e2e2;font-size:14px;border-radius:2px;}
  button{width:100%;padding:11px;background:#111;color:#fff;border:none;font-size:14px;border-radius:2px;cursor:pointer;}
  .error{background:#fbe9e7;color:#a3352a;border:1px solid #f3c2ba;padding:10px 14px;font-size:13px;margin-bottom:16px;border-radius:3px;}
  .hint{font-size:12px;color:#888;margin-top:14px;text-align:center;}
</style>
</head>
<body>
  <form class="box" method="post">
    <h1>BidMyAsset Admin Panel</h1>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="field">
      <label>Email</label>
      <input type="text" name="username" required autofocus placeholder="admin@bidmyasset.com">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit">Log In</button>
    <div class="hint">Logs in via PocketBase's "admins" collection.</div>
    <div class="hint">Don't have an account? <a href="public-register.php">Register here</a>.</div>
  </form>
</body>
</html>
