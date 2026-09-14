<?php
require_once 'includes/auth.php';
require_once '../config.php';

require_superuser();

$edit_id  = trim($_GET['id'] ?? '');
$is_edit  = $edit_id !== '';
$existing = null;

if ($is_edit) {
    $existing = PocketBase::view('admins', $edit_id, $pb_token);
    if (!$existing) {
        header('Location: admins.php?not_found=1');
        exit;
    }
}

$admin_title = $is_edit ? 'Edit Admin' : 'Add New Admin';
$error = '';

// Values to pre-fill the form with: posted values take priority (so a
// validation error doesn't wipe what was typed), then the existing record
// when editing, then blank defaults for a new account.
$name  = $_POST['name']  ?? ($existing['name']  ?? '');
$email = $_POST['email'] ?? ($existing['email'] ?? '');
$role  = $_POST['role']  ?? ($existing['role']  ?? 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $role     = ($_POST['role'] ?? 'admin') === 'superuser' ? 'superuser' : 'admin';

    // On edit, leaving both password fields blank means "keep the current
    // password" — only validate/send them if the admin actually typed one.
    $changing_password = !$is_edit || $password !== '' || $confirm !== '';

    if ($name === '' || $email === '') {
        $error = 'Please fill in name and email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($changing_password && strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($changing_password && $password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($is_edit && $existing['id'] === ($_SESSION['admin_id'] ?? '') && $role !== 'superuser') {
        $error = "You can't remove your own superuser role while logged in as yourself. Ask another superuser to change it instead.";
    } else {
        $fields = ['name' => $name, 'email' => $email, 'role' => $role];
        if ($changing_password) {
            $fields['password']        = $password;
            $fields['passwordConfirm'] = $confirm;
        }

        if ($is_edit) {
            $res = PocketBase::request('PATCH', '/api/collections/admins/records/' . rawurlencode($edit_id), $fields, $pb_token);
        } else {
            $res = PocketBase::request('POST', '/api/collections/admins/records', $fields, $pb_token);
        }

        if ($res['status'] === 200) {
            header('Location: admins.php?' . ($is_edit ? 'updated=1' : 'created=1'));
            exit;
        }

        // Surface PocketBase's own validation message where possible
        // (e.g. "email already in use") instead of a generic failure.
        $error = $is_edit ? 'Could not update the account.' : 'Could not create the account.';
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

include 'includes/admin_header.php';
?>

<h1><?= $is_edit ? 'Edit Admin' : 'Add New Admin' ?></h1>
<div class="sub">
  <?php if ($is_edit): ?>
    Update this admin's details. Leave the password fields blank to keep their current password.
  <?php else: ?>
    Create a login for someone else on your team. Choose "Superuser" only for people who should also be able to manage other admin accounts.
  <?php endif; ?>
</div>

<?php if ($error): ?><div class="msg error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card" style="max-width:480px;">
  <form method="post">
    <div class="field"><label>Full Name</label><input type="text" name="name" required value="<?= htmlspecialchars($name) ?>"></div>
    <div class="field"><label>Email (used to log in)</label><input type="email" name="email" required value="<?= htmlspecialchars($email) ?>"></div>
    <div class="field">
      <label><?= $is_edit ? 'New Password' : 'Password' ?></label>
      <input type="password" name="password" <?= $is_edit ? '' : 'required' ?> minlength="8" placeholder="<?= $is_edit ? 'Leave blank to keep current password' : '' ?>">
    </div>
    <div class="field">
      <label>Confirm <?= $is_edit ? 'New ' : '' ?>Password</label>
      <input type="password" name="password_confirm" <?= $is_edit ? '' : 'required' ?> minlength="8">
    </div>
    <div class="field">
      <label>Role</label>
      <select name="role">
        <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admin — manages site content</option>
        <option value="superuser" <?= ($role === 'superuser') ? 'selected' : '' ?>>Superuser — also manages admin accounts</option>
      </select>
      <?php if ($is_edit && $existing['id'] === ($_SESSION['admin_id'] ?? '')): ?>
        <div class="hint">This is your own account — it must stay a superuser so you don't lock yourself out.</div>
      <?php endif; ?>
    </div>
    <button type="submit" class="btn"><?= $is_edit ? 'Save Changes' : 'Create Admin Account' ?></button>
    <a href="admins.php" style="margin-left:12px;">Cancel</a>
  </form>
</div>

<?php include 'includes/admin_footer.php'; ?>
