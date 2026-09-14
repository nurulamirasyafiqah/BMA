<?php
require_once 'includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

$admin_title = 'Dashboard';

function pb_count(string $collection, ?string $token, string $filter = ''): int {
    $params = ['perPage' => 1];
    if ($filter !== '') $params['filter'] = $filter;
    $res = PocketBase::request('GET', "/api/collections/$collection/records?" . http_build_query($params), null, $token);
    return (int)($res['data']['totalItems'] ?? 0);
}

$services_count    = pb_count('services', $pb_token);
$machinery_count   = pb_count('machinery_items', $pb_token);
$trusted_count     = pb_count('trusted_logos', $pb_token);
$news_count        = pb_count('news', $pb_token);
$submissions_count = pb_count('contact_submissions', $pb_token);
$countries_count   = pb_count('site_settings', $pb_token);

$recent = PocketBase::list('contact_submissions', [
    'sort'    => '-created',
    'perPage' => 5,
], $pb_token);

include 'includes/admin_header.php';
?>

<h1>Dashboard</h1>
<?php if (isset($_GET['welcome'])): ?>
  <div class="msg">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'there') ?>! Your admin account is ready — you can start managing site content right away.</div>
<?php endif; ?>
<div class="sub">Logged in as <?= htmlspecialchars($_SESSION['admin_username']) ?></div>

<div class="stat-grid">
  <div class="stat-box"><div class="num"><?= $countries_count ?></div><div class="label">Sites (countries)</div></div>
  <div class="stat-box"><div class="num"><?= $services_count ?></div><div class="label">Services listed</div></div>
  <div class="stat-box"><div class="num"><?= $machinery_count ?></div><div class="label">Machinery items</div></div>
  <div class="stat-box"><div class="num"><?= $trusted_count ?></div><div class="label">Trusted-by logos</div></div>
  <div class="stat-box"><div class="num"><?= $news_count ?></div><div class="label">News articles</div></div>
  <div class="stat-box"><div class="num"><?= $submissions_count ?></div><div class="label">Contact submissions</div></div>
</div>

<div class="card">
  <h3 style="margin-top:0;">Latest Contact Submissions</h3>
  <table>
    <tr><th>Date</th><th>Country</th><th>Name</th><th>Email</th><th>Message</th></tr>
    <?php if (!$recent): ?>
      <tr><td colspan="5">No submissions yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($recent as $r): ?>
      <tr>
        <td><?= htmlspecialchars(pb_date($r['created'] ?? null, 'd/m/Y H:i')) ?></td>
        <td><?= htmlspecialchars(ucfirst($r['country'])) ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars(admin_truncate($r['message'] ?? '', 60)) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p style="margin-top:14px;"><a href="submissions.php">View all submissions →</a></p>
</div>

<?php include 'includes/admin_footer.php'; ?>
