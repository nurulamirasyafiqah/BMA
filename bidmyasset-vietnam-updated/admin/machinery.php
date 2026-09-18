<?php
require_once 'includes/auth.php';
require_once '../config.php';

$admin_title = 'Used Machinery Listed';
// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$message_is_error = false;
$edit_row = null;

// Delete
if (isset($_GET['delete'])) {
    PocketBase::delete('machinery_items', $_GET['delete'], $pb_token);
    header("Location: machinery.php?deleted=1");
    exit;
}

// Add / update (with optional image upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = trim($_POST['id'] ?? '');
    $title      = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $bottom_title = trim($_POST['bottom_title'] ?? '');
    $bottom_description = trim($_POST['bottom_description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $values = ['title' => $title, 'description' => $description, 'bottom_title' => $bottom_title, 'bottom_description' => $bottom_description, 'sort_order' => $sort_order];
    if ($id === '') {
        $values['country'] = $country;
    }

    $files = [];
    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $files['image'] = $_FILES['image'];
    } elseif (!empty($_POST['remove_image'])) {
        $values['image'] = '';
    }

    $saved = PocketBase::saveWithFiles('machinery_items', $id !== '' ? $id : null, $values, $files, $pb_token);
    if ($saved) {
        $message = $id !== '' ? 'Machinery item updated.' : 'Machinery item added.';
    } else {
        $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
        $message_is_error = true;
    }
}

// Load a row into the edit form
if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('machinery_items', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'Machinery item deleted.'; }

$items = PocketBase::list('machinery_items', [
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

<h1>Used Machinery Listed</h1>
<div class="sub">Manage the machinery photo grid on the homepage. Each row keeps its position — you can add, edit, reorder (via Sort Order), or delete rows. Clicking a category on the website now opens a detail page — add a description here, and manage the actual machines for sale under <a href="machinery-listings.php">Machinery Listings</a>.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit Item' : 'Add New Item' ?></h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">
    <div class="field"><label>Title</label><input type="text" name="title" required value="<?= htmlspecialchars($edit_row['title'] ?? '') ?>"></div>
    <div class="field">
      <label>Category Description</label>
      <textarea id="category-description-editor" name="description" rows="8"><?= htmlspecialchars($edit_row['description'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label>Bottom Title</label>
      <input type="text" name="bottom_title" placeholder="e.g. What is a plastic injection molding machine?" value="<?= htmlspecialchars($edit_row['bottom_title'] ?? '') ?>">
    </div>
    <div class="field">
      <label>Bottom Description</label>
      <textarea id="bottom-description-editor" name="bottom_description" rows="8"><?= htmlspecialchars($edit_row['bottom_description'] ?? '') ?></textarea>
    </div>
    <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? count($items) + 1) ?>"></div>

    <div class="field">
      <label>Photo</label>
      <?php $currentUrl = $edit_row ? PocketBase::fileUrl($edit_row, 'image', 'machinery_items') : null; ?>
      <?php if ($currentUrl): ?>
        <div class="image-preview"><img src="<?= htmlspecialchars($currentUrl) ?>" alt=""></div>
        <label class="checkbox-line"><input type="checkbox" name="remove_image" value="1"> Remove this photo</label>
      <?php else: ?>
        <div class="sub" style="margin:6px 0;">No photo set — a default placeholder photo is shown on the site until you upload one.</div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add Item' ?></button>
    <?php if ($edit_row): ?> <a href="machinery.php">Cancel</a><?php endif; ?>
  </form>
</div>

<!-- Rich text editors for Category Description / Bottom Description —
     same self-hosted TinyMCE setup used elsewhere in the admin panel. -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#category-description-editor, #bottom-description-editor',
    height: 360,
    menubar: false,
    plugins: 'lists link table image code autolink',
    toolbar: 'undo redo | blocks | bold italic underline | forecolor | ' +
             'bullist numlist | link image table | alignleft aligncenter alignright | code',
    branding: false,
    promotion: false,
    license_key: 'gpl'
  });
</script>

<table>
  <tr><th>Photo</th><th>Order</th><th>Title</th><th>Actions</th></tr>
  <?php if (!$items): ?><tr><td colspan="4">No items yet.</td></tr><?php endif; ?>
  <?php foreach ($items as $m): ?>
    <?php $thumb = PocketBase::fileUrl($m, 'image', 'machinery_items'); ?>
    <tr>
      <td><?php if ($thumb): ?><img class="thumb" src="<?= htmlspecialchars($thumb) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
      <td><?= (int)$m['sort_order'] ?></td>
      <td><?= htmlspecialchars($m['title']) ?></td>
      <td>
        <a class="btn btn-sm" href="machinery.php?edit=<?= $m['id'] ?>">Edit</a>
        <a class="btn btn-sm" href="machinery-listings.php?category=<?= $m['id'] ?>">Listings</a>
        <a class="btn btn-sm btn-danger" href="machinery.php?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this item? Its listings will be deleted too.');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
