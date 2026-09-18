<?php
require_once 'includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

$admin_title = 'News';
// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$message_is_error = false;
$edit_row = null;

if (isset($_GET['delete'])) {
    PocketBase::delete('news', $_GET['delete'], $pb_token);
    header("Location: news.php?deleted=1");
    exit;
}

if (isset($_GET['delete_comment']) && isset($_GET['edit'])) {
    PocketBase::delete('news_comments', $_GET['delete_comment'], $pb_token);
    header("Location: news.php?edit=" . urlencode($_GET['edit']) . "&comment_deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = trim($_POST['id'] ?? '');
    $title      = trim($_POST['title'] ?? '');
    $news_date  = trim($_POST['news_date'] ?? date('Y-m-d'));
    $author     = trim($_POST['author'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $values = [
        'title'      => $title,
        'news_date'  => $news_date,
        'author'     => $author,
        'content'    => $content,
        'sort_order' => $sort_order,
    ];
    if ($id === '') {
        $values['country'] = $country;
    }

    $files = [];
    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $files['image'] = $_FILES['image'];
    } elseif (!empty($_POST['remove_image'])) {
        $values['image'] = '';
    }

    $saved = PocketBase::saveWithFiles('news', $id !== '' ? $id : null, $values, $files, $pb_token);
    if ($saved) {
        $message = $id !== '' ? 'News item updated.' : 'News item added.';
    } else {
        $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
        $message_is_error = true;
    }
}

if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('news', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'News item deleted.'; }
if (isset($_GET['comment_deleted'])) { $message = 'Comment deleted.'; }

$news_list = PocketBase::list('news', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'sort'    => '-news_date,+sort_order',
    'perPage' => 100,
], $pb_token);

// Engagement detail for the item currently being edited: totals plus the
// individual like/share/comment rows, so an admin can see who liked,
// shared, or commented (as best as possible without a public login
// system — identified by IP address / comment name + timestamp).
$engagement = null;
if ($edit_row) {
    $eid = $edit_row['id'];
    $engagement = [
        'like_count'    => PocketBase::count('news_likes', ['filter' => "news = '" . PocketBase::escape($eid) . "'"], $pb_token),
        'likes'         => PocketBase::list('news_likes', ['filter' => "news = '" . PocketBase::escape($eid) . "'", 'sort' => '-created', 'perPage' => 200], $pb_token),
        'share_counts'  => get_news_share_counts($eid),
        'shares'        => PocketBase::list('news_shares', ['filter' => "news = '" . PocketBase::escape($eid) . "'", 'sort' => '-created', 'perPage' => 200], $pb_token),
        'comments'      => PocketBase::list('news_comments', ['filter' => "news = '" . PocketBase::escape($eid) . "'", 'sort' => '-created', 'perPage' => 200], $pb_token),
    ];
}

include 'includes/admin_header.php';
?>
<style>
  .thumb{width:60px;height:44px;object-fit:cover;border:1px solid var(--grey-line);background:#fff;}
  .image-preview img{max-width:180px;max-height:110px;object-fit:contain;border:1px solid var(--grey-line);background:#fff;padding:4px;margin:8px 0;}
  .checkbox-line{display:flex;align-items:center;gap:6px;font-weight:400;font-size:13px;margin:6px 0;}
</style>

<h1>News</h1>
<div class="sub">Manage the news articles shown on the homepage (latest 3 are displayed). Each title links through to its own full article page.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit News Item' : 'Add News Item' ?></h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">
    <div class="field"><label>Title</label><input type="text" name="title" required value="<?= htmlspecialchars($edit_row['title'] ?? '') ?>"></div>
    <div class="grid-2">
      <div class="field"><label>Date</label><input type="date" name="news_date" value="<?= htmlspecialchars($edit_row ? substr($edit_row['news_date'], 0, 10) : date('Y-m-d')) ?>"></div>
      <div class="field"><label>Author</label><input type="text" name="author" placeholder="e.g. John Tan" value="<?= htmlspecialchars($edit_row['author'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? 1) ?>"></div>

    <div class="field">
      <label>Photo</label>
      <?php $currentUrl = $edit_row ? PocketBase::fileUrl($edit_row, 'image', 'news') : null; ?>
      <?php if ($currentUrl): ?>
        <div class="image-preview"><img src="<?= htmlspecialchars($currentUrl) ?>" alt=""></div>
        <label class="checkbox-line"><input type="checkbox" name="remove_image" value="1"> Remove this photo</label>
      <?php else: ?>
        <div class="sub" style="margin:6px 0;">No photo set — the article page shows no header image until you upload one.</div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/*">
    </div>

    <div class="field">
      <label>Content (shown on the full article page)</label>
      <textarea id="content-editor" name="content"><?= htmlspecialchars($edit_row['content'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add News Item' ?></button>
    <?php if ($edit_row): ?> <a href="news.php">Cancel</a><?php endif; ?>
  </form>
</div>

<?php if ($edit_row && $engagement): ?>
<div class="card">
  <h3 style="margin-top:0;">Engagement for "<?= htmlspecialchars($edit_row['title']) ?>"</h3>
  <div class="sub">
    <?= (int)$engagement['like_count'] ?> like<?= $engagement['like_count'] === 1 ? '' : 's' ?> &nbsp;&middot;&nbsp;
    <?= (int)$engagement['share_counts']['total'] ?> share<?= $engagement['share_counts']['total'] === 1 ? '' : 's' ?>
    (X: <?= $engagement['share_counts']['by_platform']['x'] ?>, Facebook: <?= $engagement['share_counts']['by_platform']['facebook'] ?>,
    WhatsApp: <?= $engagement['share_counts']['by_platform']['whatsapp'] ?>) &nbsp;&middot;&nbsp;
    <?= count($engagement['comments']) ?> comment<?= count($engagement['comments']) === 1 ? '' : 's' ?>
  </div>

  <h4>Who liked</h4>
  <?php if (!$engagement['likes']): ?>
    <p class="sub">No likes yet.</p>
  <?php else: ?>
    <table>
      <tr><th>When</th><th>IP Address</th></tr>
      <?php foreach ($engagement['likes'] as $like): ?>
        <tr><td><?= htmlspecialchars(pb_date($like['created'] ?? null, 'd/m/Y')) ?></td><td><?= htmlspecialchars($like['ip'] ?: '—') ?></td></tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h4>Who shared</h4>
  <?php if (!$engagement['shares']): ?>
    <p class="sub">No shares yet.</p>
  <?php else: ?>
    <table>
      <tr><th>When</th><th>Platform</th><th>IP Address</th></tr>
      <?php foreach ($engagement['shares'] as $share): ?>
        <tr><td><?= htmlspecialchars(pb_date($share['created'] ?? null, 'd/m/Y')) ?></td><td><?= htmlspecialchars(ucfirst($share['platform'])) ?></td><td><?= htmlspecialchars($share['ip'] ?: '—') ?></td></tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h4>Comments</h4>
  <?php if (!$engagement['comments']): ?>
    <p class="sub">No comments yet.</p>
  <?php else: ?>
    <table>
      <tr><th>When</th><th>Name</th><th>Comment</th><th>IP Address</th><th>Actions</th></tr>
      <?php foreach ($engagement['comments'] as $c): ?>
        <tr>
          <td><?= htmlspecialchars(pb_date($c['created'] ?? null, 'd/m/Y')) ?></td>
          <td><?= htmlspecialchars($c['name']) ?><?= !empty($c['parent']) ? ' <span class="sub">(reply)</span>' : '' ?></td>
          <td><?= nl2br(htmlspecialchars($c['content'])) ?></td>
          <td><?= htmlspecialchars($c['ip'] ?: '—') ?></td>
          <td><a class="btn btn-sm btn-danger" href="news.php?edit=<?= htmlspecialchars($edit_row['id']) ?>&delete_comment=<?= htmlspecialchars($c['id']) ?>" onclick="return confirm('Delete this comment?');">Delete</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
<?php endif; ?>

<table>
  <tr><th>Photo</th><th>Date</th><th>Title</th><th>Author</th><th>Likes</th><th>Shares</th><th>Comments</th><th>Actions</th></tr>
  <?php if (!$news_list): ?><tr><td colspan="8">No news items yet.</td></tr><?php endif; ?>
  <?php foreach ($news_list as $n): ?>
    <?php $thumb = PocketBase::fileUrl($n, 'image', 'news'); ?>
    <tr>
      <td><?php if ($thumb): ?><img class="thumb" src="<?= htmlspecialchars($thumb) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
      <td><?= htmlspecialchars(date('d/m/Y', strtotime($n['news_date']))) ?></td>
      <td><?= htmlspecialchars($n['title']) ?></td>
      <td><?= htmlspecialchars($n['author'] ?: '—') ?></td>
      <td><?= PocketBase::count('news_likes', ['filter' => "news = '" . PocketBase::escape($n['id']) . "'"], $pb_token) ?></td>
      <td><?= PocketBase::count('news_shares', ['filter' => "news = '" . PocketBase::escape($n['id']) . "'"], $pb_token) ?></td>
      <td><?= PocketBase::count('news_comments', ['filter' => "news = '" . PocketBase::escape($n['id']) . "'"], $pb_token) ?></td>
      <td>
        <a class="btn btn-sm" href="news.php?edit=<?= $n['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="news.php?delete=<?= $n['id'] ?>" onclick="return confirm('Delete this news item?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<!-- Rich text editor for the Content field: lets an admin write plain
     paragraphs, numbered/bullet lists, tables, bold/italic text, and
     links without knowing any HTML. Self-hosted build via jsDelivr, so
     no TinyMCE Cloud account or API key is needed. -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#content-editor',
    height: 420,
    menubar: false,
    plugins: 'lists link table image code autolink',
    toolbar: 'undo redo | blocks | bold italic underline | forecolor | ' +
             'bullist numlist | link image table | alignleft aligncenter alignright | code',
    branding: false,
    promotion: false,
    license_key: 'gpl'
  });
</script>

<?php include 'includes/admin_footer.php'; ?>
