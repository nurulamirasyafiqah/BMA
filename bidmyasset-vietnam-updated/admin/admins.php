<?php
require_once 'includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

require_superuser();

$admin_title = 'Manage Admins';
$message = '';
$message_is_error = false;

// Handle delete (superuser only — enforced again here and by the PocketBase
// collection rule itself, so this stays safe even if someone hits the URL
// directly without going through this page).
if (isset($_GET['delete'])) {
    if ($_GET['delete'] === ($_SESSION['admin_id'] ?? '')) {
        $message = "You can't delete your own account while logged in as it.";
        $message_is_error = true;
    } else {
        $ok = PocketBase::delete('admins', $_GET['delete'], $pb_token);
        header('Location: admins.php?' . ($ok ? 'deleted=1' : 'delete_failed=1'));
        exit;
    }
}
if (isset($_GET['deleted'])) { $message = 'Admin account deleted.'; }
if (isset($_GET['delete_failed'])) { $message = 'Could not delete that admin account.'; $message_is_error = true; }
if (isset($_GET['created'])) { $message = 'New admin account created.'; }
if (isset($_GET['updated'])) { $message = 'Admin account updated.'; }
if (isset($_GET['not_found'])) { $message = 'That admin account no longer exists.'; $message_is_error = true; }

$admins = PocketBase::list('admins', ['sort' => '+created', 'perPage' => 200], $pb_token);

include 'includes/admin_header.php';
?>

<h1>Manage Admins</h1>
<div class="sub">Superusers can create new admin logins and remove ones that are no longer needed. Regular admins can manage site content but can't see this page.</div>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<p><a class="btn" href="register.php">+ Add New Admin</a></p>

<table>
  <tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr>
  <?php foreach ($admins as $a): ?>
    <tr>
      <td><?= htmlspecialchars(($a['name'] ?? '') ?: '—') ?></td>
      <td><?= htmlspecialchars($a['email'] ?? '—') ?></td>
      <td><span class="role-pill <?= htmlspecialchars($a['role'] ?? 'admin') ?>"><?= htmlspecialchars($a['role'] ?? 'admin') ?></span></td>
      <td><?= htmlspecialchars(pb_date($a['created'] ?? null)) ?></td>
      <td>
        <a class="btn btn-sm" href="register.php?id=<?= htmlspecialchars($a['id']) ?>" style="margin-right:8px;">Edit</a>
        <?php if ($a['id'] !== ($_SESSION['admin_id'] ?? '')): ?>
          <a class="btn btn-sm btn-danger" href="admins.php?delete=<?= htmlspecialchars($a['id']) ?>" onclick="return confirm('Delete this admin account? They will no longer be able to log in.');">Delete</a>
        <?php else: ?>
          <span class="sub" style="margin:0;">(you)</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
