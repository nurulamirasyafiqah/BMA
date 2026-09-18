<?php
session_start();
require_once '../config.php';

if (isset($_SESSION['pb_token'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$name  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in your name, email, and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Self-registered accounts always get the "admin" role — never
        // "superuser" — enforced both here and at the database level
        // (see pb_migrations/1700000010_public_admin_registration.js), so
        // signing up never grants access to manage other admin accounts.
        $res = PocketBase::request('POST', '/api/collections/admins/records', [
            'name'            => $name,
            'email'           => $email,
            'password'        => $password,
            'passwordConfirm' => $confirm,
            'role'            => 'admin',
        ]);

        if ($res['status'] === 200) {
            // Log the new admin straight in, so they land in the admin
            // panel immediately after registering — no separate login step.
            $auth = PocketBase::authWithPassword('admins', $email, $password);
            if ($auth && isset($auth['token'], $auth['record'])) {
                $_SESSION['pb_token']       = $auth['token'];
                $_SESSION['admin_id']       = $auth['record']['id'];
                $_SESSION['admin_username'] = $auth['record']['email'];
                $_SESSION['admin_name']     = $auth['record']['name'] ?? '';
                $_SESSION['admin_role']     = $auth['record']['role'] ?? 'admin';
                header('Location: dashboard.php?welcome=1');
                exit;
            }
            // Account was created but auto-login failed for some reason —
            // send them to the normal login form instead.
            header('Location: login.php?registered=1');
            exit;
        }

        $error = 'Could not create your account.';
        if (!empty($res['data']['data']) && is_array($res['data']['data'])) {
            $details = [];
            foreach ($res['data']['data'] as $field => $err) {
                $details[] = $field . ': ' . ($err['message'] ?? 'invalid value');
            }
            if ($details) {
                $error .= ' ' . implode('; ', $details);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register — BidMyAsset Admin</title>
<link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
<style>
  body{margin:0;font-family:Arial, Helvetica, sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;}
  .box{background:#fff;border:1px solid #e2e2e2;padding:40px;width:360px;border-radius:4px;}
  .box h1{font-size:18px;margin:0 0 6px 0;text-align:center;color:#1b2a4a;}
  .box .sub{font-size:12.5px;color:#888;text-align:center;margin-bottom:22px;}
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
    <h1>Create Admin Account</h1>
    <div class="sub">Sign up to manage BidMyAsset content — settings, services, machinery, and news.</div>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="field">
      <label>Full Name</label>
      <input type="text" name="name" required autofocus value="<?= htmlspecialchars($name) ?>">
    </div>
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required minlength="8">
    </div>
    <div class="field">
      <label>Confirm Password</label>
      <input type="password" name="password_confirm" required minlength="8">
    </div>
    <button type="submit">Create Account</button>
    <div class="hint">Already have an account? <a href="login.php">Log in here</a>.</div>
  </form>
</body>
</html>
