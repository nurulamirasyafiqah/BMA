<?php
require_once 'includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

$admin_title = 'Contact Submissions';
$message = '';
$message_is_error = false;

if (isset($_GET['delete'])) {
    PocketBase::delete('contact_submissions', $_GET['delete'], $pb_token);
    header("Location: submissions.php?deleted=1");
    exit;
}
if (isset($_GET['deleted'])) { $message = 'Submission deleted.'; }

// This deployment only ever has submissions for its own country (see
// SITE_COUNTRY in config.php and contact_submit.php), so there's no
// country filter to choose here — just show everything.
$rows = PocketBase::list('contact_submissions', ['sort' => '-created', 'perPage' => 200], $pb_token);

include 'includes/admin_header.php';
?>

<h1>Contact Submissions</h1>
<div class="sub">Messages submitted through the public contact form.</div>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<table>
  <tr><th>Date</th><th>Name</th><th>Company</th><th>Phone</th><th>Email</th><th>Message</th><th>Actions</th></tr>
  <?php if (!$rows): ?>
    <tr><td colspan="7">No submissions yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars(pb_date($r['created'] ?? null, 'd/m/Y H:i')) ?></td>
      <td><?= htmlspecialchars($r['name']) ?></td>
      <td><?= htmlspecialchars($r['company']) ?></td>
      <td><?= htmlspecialchars($r['phone']) ?></td>
      <td><?= htmlspecialchars($r['email']) ?></td>
      <td><?= htmlspecialchars($r['message']) ?></td>
      <td><a class="btn btn-sm btn-danger" href="submissions.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this submission?');">Delete</a></td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
