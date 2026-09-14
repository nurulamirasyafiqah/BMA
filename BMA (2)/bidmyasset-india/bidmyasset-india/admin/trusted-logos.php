<?php
require_once 'includes/auth.php';
require_once '../config.php';

$admin_title = 'Trusted By Logos';
// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$edit_row = null;

if (isset($_GET['delete'])) {
    PocketBase::delete('trusted_logos', $_GET['delete'], $pb_token);
    header("Location: trusted-logos.php?deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = trim($_POST['id'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $values = ['name' => $name, 'sort_order' => $sort_order];
    if ($id === '') {
        $values['country'] = $country;
    }

    $files = [];
    if (isset($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $files['logo'] = $_FILES['logo'];
    } elseif (!empty($_POST['remove_logo'])) {
        $values['logo'] = '';
    }

    PocketBase::saveWithFiles('trusted_logos', $id !== '' ? $id : null, $values, $files, $pb_token);
    $message = $id !== '' ? 'Logo updated.' : 'Logo added.';
}

if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('trusted_logos', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'Logo deleted.'; }

$items = PocketBase::list('trusted_logos', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'sort'    => '+sort_order,+id',
    'perPage' => 100,
], $pb_token);

include 'includes/admin_header.php';
?>
<style>
  .thumb{width:60px;height:44px;object-fit:contain;border:1px solid var(--grey-line);background:#fff;}
  .image-preview img{max-width:180px;max-height:110px;object-fit:contain;border:1px solid var(--grey-line);background:#fff;padding:4px;margin:8px 0;}
  .checkbox-line{display:flex;align-items:center;gap:6px;font-weight:400;font-size:13px;margin:6px 0;}
</style>

<h1>Trusted By Logos</h1>
<div class="sub">Manage the "Trusted By" logo grid on the homepage. If no logo image is uploaded, the company name is shown as plain text instead.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit Logo' : 'Add New Logo' ?></h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">
    <div class="field"><label>Company Name</label><input type="text" name="name" required value="<?= htmlspecialchars($edit_row['name'] ?? '') ?>"></div>
    <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? count($items) + 1) ?>"></div>

    <div class="field">
      <label>Logo Image</label>
      <?php $currentUrl = $edit_row ? PocketBase::fileUrl($edit_row, 'logo', 'trusted_logos') : null; ?>
      <?php if ($currentUrl): ?>
        <div class="image-preview"><img src="<?= htmlspecialchars($currentUrl) ?>" alt=""></div>
        <label class="checkbox-line"><input type="checkbox" name="remove_logo" value="1"> Remove this logo (show name as text instead)</label>
      <?php else: ?>
        <div class="sub" style="margin:6px 0;">No logo uploaded — the company name above is shown as text on the site.</div>
      <?php endif; ?>
      <input type="file" name="logo" accept="image/*">
    </div>

    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add Logo' ?></button>
    <?php if ($edit_row): ?> <a href="trusted-logos.php">Cancel</a><?php endif; ?>
  </form>
</div>

<table>
  <tr><th>Logo</th><th>Order</th><th>Name</th><th>Actions</th></tr>
  <?php if (!$items): ?><tr><td colspan="4">No logos yet.</td></tr><?php endif; ?>
  <?php foreach ($items as $t): ?>
    <?php $thumb = PocketBase::fileUrl($t, 'logo', 'trusted_logos'); ?>
    <tr>
      <td><?php if ($thumb): ?><img class="thumb" src="<?= htmlspecialchars($thumb) ?>" alt=""><?php else: ?>— (text)<?php endif; ?></td>
      <td><?= (int)$t['sort_order'] ?></td>
      <td><?= htmlspecialchars($t['name']) ?></td>
      <td>
        <a class="btn btn-sm" href="trusted-logos.php?edit=<?= $t['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="trusted-logos.php?delete=<?= $t['id'] ?>" onclick="return confirm('Delete this logo?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
