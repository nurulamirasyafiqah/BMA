<?php
require_once 'includes/auth.php';
require_once '../config.php';

$admin_title = 'News';
// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$edit_row = null;

if (isset($_GET['delete'])) {
    PocketBase::delete('news', $_GET['delete'], $pb_token);
    header("Location: news.php?deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = trim($_POST['id'] ?? '');
    $title      = trim($_POST['title'] ?? '');
    $news_date  = trim($_POST['news_date'] ?? date('Y-m-d'));
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($id !== '') {
        PocketBase::update('news', $id, [
            'title' => $title, 'news_date' => $news_date, 'sort_order' => $sort_order,
        ], $pb_token);
        $message = 'News item updated.';
    } else {
        PocketBase::create('news', [
            'country' => $country, 'title' => $title, 'news_date' => $news_date, 'sort_order' => $sort_order,
        ], $pb_token);
        $message = 'News item added.';
    }
}

if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('news', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'News item deleted.'; }

$news_list = PocketBase::list('news', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'sort'    => '-news_date,+sort_order',
    'perPage' => 100,
], $pb_token);

include 'includes/admin_header.php';
?>

<h1>News</h1>
<div class="sub">Manage the news articles shown on the homepage (latest 3 are displayed).</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit News Item' : 'Add News Item' ?></h3>
  <form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">
    <div class="field"><label>Title</label><input type="text" name="title" required value="<?= htmlspecialchars($edit_row['title'] ?? '') ?>"></div>
    <div class="grid-2">
      <div class="field"><label>Date</label><input type="date" name="news_date" value="<?= htmlspecialchars($edit_row ? substr($edit_row['news_date'], 0, 10) : date('Y-m-d')) ?>"></div>
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? 1) ?>"></div>
    </div>
    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add News Item' ?></button>
    <?php if ($edit_row): ?> <a href="news.php">Cancel</a><?php endif; ?>
  </form>
</div>

<table>
  <tr><th>Date</th><th>Title</th><th>Actions</th></tr>
  <?php foreach ($news_list as $n): ?>
    <tr>
      <td><?= htmlspecialchars(date('d/m/Y', strtotime($n['news_date']))) ?></td>
      <td><?= htmlspecialchars($n['title']) ?></td>
      <td>
        <a class="btn btn-sm" href="news.php?edit=<?= $n['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="news.php?delete=<?= $n['id'] ?>" onclick="return confirm('Delete this news item?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
