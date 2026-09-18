<?php
require_once 'includes/auth.php';
require_once '../config.php';

$admin_title = 'Auction Images';
// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$message_is_error = false;
$edit_row = null;

if (isset($_GET['delete'])) {
    PocketBase::delete('auction_images', $_GET['delete'], $pb_token);
    header("Location: auction-images.php?deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = trim($_POST['id'] ?? '');
    $caption    = trim($_POST['caption'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $values = ['caption' => $caption, 'sort_order' => $sort_order];
    if ($id === '') {
        $values['country'] = $country;
    }

    $files = [];
    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $files['image'] = $_FILES['image'];
    } elseif (!empty($_POST['remove_image'])) {
        $values['image'] = '';
    }

    $saved = PocketBase::saveWithFiles('auction_images', $id !== '' ? $id : null, $values, $files, $pb_token);
    if ($saved) {
        $message = $id !== '' ? 'Auction image updated.' : 'Auction image added.';
    } else {
        $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
        $message_is_error = true;
    }
}

if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('auction_images', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'Auction image deleted.'; }

$items = PocketBase::list('auction_images', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'sort'    => '+sort_order,+id',
    'perPage' => 100,
], $pb_token);

include 'includes/admin_header.php';
?>
<style>
  .thumb{width:60px;height:44px;object-fit:cover;border:1px solid var(--grey-line);background:#fff;}
  .image-preview img{max-width:180px;max-height:110px;object-fit:contain;border:1px solid var(--grey-line);background:#fff;padding:4px;margin:8px 0;}
  .checkbox-line{display:flex;align-items:center;gap:6px;font-weight:400;font-size:13px;margin:6px 0;}
</style>

<h1>Auction Images</h1>
<div class="sub">Manage the photo carousel shown beside "Leading Machinery Auction Platform" on the homepage. Visitors can click the arrows or dots to move between photos. Add as many as you like — order controls which one shows first. If this list is empty, the single "Auction Image" from Site Settings is shown instead with no arrows.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit Image' : 'Add New Image' ?></h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">

    <div class="field">
      <label>Photo</label>
      <?php $currentUrl = $edit_row ? PocketBase::fileUrl($edit_row, 'image', 'auction_images') : null; ?>
      <?php if ($currentUrl): ?>
        <div class="image-preview"><img src="<?= htmlspecialchars($currentUrl) ?>" alt=""></div>
        <label class="checkbox-line"><input type="checkbox" name="remove_image" value="1"> Remove this photo</label>
      <?php else: ?>
        <div class="sub" style="margin:6px 0;">No photo uploaded yet — this slide won't show on the site until you upload one.</div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/*">
    </div>

    <div class="field">
      <label>Caption (optional)</label>
      <textarea name="caption" rows="3" placeholder="Shown as a small label on top of the photo, e.g.&#10;Mazak (JP) - CNC&#10;Model : VTC-160A&#10;Date : 2000-06"><?= htmlspecialchars($edit_row['caption'] ?? '') ?></textarea>
    </div>

    <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? count($items) + 1) ?>"></div>

    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add Image' ?></button>
    <?php if ($edit_row): ?> <a href="auction-images.php">Cancel</a><?php endif; ?>
  </form>
</div>

<table>
  <tr><th>Photo</th><th>Order</th><th>Caption</th><th>Actions</th></tr>
  <?php if (!$items): ?><tr><td colspan="4">No images yet.</td></tr><?php endif; ?>
  <?php foreach ($items as $a): ?>
    <?php $thumb = PocketBase::fileUrl($a, 'image', 'auction_images'); ?>
    <tr>
      <td><?php if ($thumb): ?><img class="thumb" src="<?= htmlspecialchars($thumb) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
      <td><?= (int)$a['sort_order'] ?></td>
      <td><?= htmlspecialchars($a['caption'] ?: '—') ?></td>
      <td>
        <a class="btn btn-sm" href="auction-images.php?edit=<?= $a['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="auction-images.php?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this image?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
