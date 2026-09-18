<?php
require_once 'includes/auth.php';
require_once '../config.php';

$admin_title = 'Machinery Listings';
// This deployment only ever manages its own country's content — see
// SITE_COUNTRY in config.php.
$country = SITE_COUNTRY;

$message  = '';
$message_is_error = false;
$edit_row = null;

// The categories (Injection Molding, CNC, etc.) for this country — used to
// populate the "Category" dropdown and to filter the table below.
$categories = PocketBase::list('machinery_items', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'sort'    => '+sort_order,+id',
    'perPage' => 100,
], $pb_token);

$selected_category = trim($_GET['category'] ?? '');
if ($selected_category !== '' && !array_filter($categories, fn($c) => $c['id'] === $selected_category)) {
    $selected_category = ''; // ignore an id that doesn't belong to this country
}

// Delete
if (isset($_GET['delete'])) {
    PocketBase::delete('machinery_listings', $_GET['delete'], $pb_token);
    header("Location: machinery-listings.php?category=" . urlencode($selected_category) . "&deleted=1");
    exit;
}

// Add / update (with optional image upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = trim($_POST['id'] ?? '');
    $category         = trim($_POST['category'] ?? '');
    $brand            = trim($_POST['brand'] ?? '');
    $model            = trim($_POST['model'] ?? '');
    $origin           = trim($_POST['origin'] ?? '');
    $year             = trim($_POST['year'] ?? '');
    $key_specs        = trim($_POST['key_specs'] ?? '');
    $indicative_price = trim($_POST['indicative_price'] ?? '');
    $detail_description = trim($_POST['detail_description'] ?? '');
    $sort_order       = (int)($_POST['sort_order'] ?? 0);

    $values = [
        'category'          => $category,
        'brand'             => $brand,
        'model'             => $model,
        'origin'            => $origin,
        'year'              => $year,
        'key_specs'         => $key_specs,
        'indicative_price'  => $indicative_price,
        'detail_description' => $detail_description,
        'sort_order'        => $sort_order,
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

    if ($category === '') {
        $message = 'Please choose a category for this listing.';
        $message_is_error = true;
    } else {
        $saved = PocketBase::saveWithFiles('machinery_listings', $id !== '' ? $id : null, $values, $files, $pb_token);
        if ($saved) {
            $message = $id !== '' ? 'Listing updated.' : 'Listing added.';
        } else {
            $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
            $message_is_error = true;
        }
        $selected_category = $category;
    }
}

// Load a row into the edit form
if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('machinery_listings', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'Listing deleted.'; }

$filter = "country = '" . PocketBase::escape($country) . "'";
if ($selected_category !== '') {
    $filter .= " && category = '" . PocketBase::escape($selected_category) . "'";
}
$listings = PocketBase::list('machinery_listings', [
    'filter'  => $filter,
    'sort'    => '+sort_order,+id',
    'perPage' => 200,
    'expand'  => 'category',
], $pb_token);

/** Look up a category's title by id, for display in the table. */
function category_title(array $categories, string $id): string {
    foreach ($categories as $c) {
        if ($c['id'] === $id) return $c['title'];
    }
    return '—';
}

include 'includes/admin_header.php';
?>
<style>
  .thumb{width:60px;height:44px;object-fit:cover;border:1px solid var(--grey-line);background:#fff;}
  .image-preview img{max-width:180px;max-height:110px;object-fit:contain;border:1px solid var(--grey-line);background:#fff;padding:4px;margin:8px 0;}
  .checkbox-line{display:flex;align-items:center;gap:6px;font-weight:400;font-size:13px;margin:6px 0;}
</style>

<h1>Machinery Listings</h1>
<div class="sub">The individual used machines shown on each category's detail page (e.g. what someone sees after clicking "Injection Molding" on the homepage). Manage the categories themselves from <a href="machinery.php">Used Machinery Listed</a>.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if (!$categories): ?>
  <div class="msg error">No machinery categories exist yet for this country. Add one first from <a href="machinery.php">Used Machinery Listed</a>, then come back here to add listings under it.</div>
<?php else: ?>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="country-tabs">
  <a href="machinery-listings.php" class="<?= $selected_category === '' ? 'active' : '' ?>">All Categories</a>
  <?php foreach ($categories as $c): ?>
    <a href="machinery-listings.php?category=<?= $c['id'] ?>" class="<?= $selected_category === $c['id'] ? 'active' : '' ?>"><?= htmlspecialchars($c['title']) ?></a>
  <?php endforeach; ?>
</div>
<br>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit Listing' : 'Add New Listing' ?></h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">

    <div class="field">
      <label>Category</label>
      <select name="category" required>
        <option value="">— Choose a category —</option>
        <?php foreach ($categories as $c): ?>
          <?php $sel = ($edit_row['category'] ?? $selected_category) === $c['id']; ?>
          <option value="<?= $c['id'] ?>" <?= $sel ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="grid-2">
      <div class="field"><label>Brand</label><input type="text" name="brand" value="<?= htmlspecialchars($edit_row['brand'] ?? '') ?>"></div>
      <div class="field"><label>Model</label><input type="text" name="model" value="<?= htmlspecialchars($edit_row['model'] ?? '') ?>"></div>
      <div class="field"><label>Origin</label><input type="text" name="origin" placeholder="e.g. Japan" value="<?= htmlspecialchars($edit_row['origin'] ?? '') ?>"></div>
      <div class="field"><label>Year</label><input type="text" name="year" placeholder="e.g. 2011" value="<?= htmlspecialchars($edit_row['year'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>Key Specs</label><input type="text" name="key_specs" placeholder="e.g. CNC Machining Center" value="<?= htmlspecialchars($edit_row['key_specs'] ?? '') ?>"></div>
    <div class="field"><label>Indicative Price</label><input type="text" name="indicative_price" placeholder="e.g. Contact us, or a figure" value="<?= htmlspecialchars($edit_row['indicative_price'] ?? '') ?>"></div>
    <div class="field">
      <label>Read More Page Description</label>
      <textarea id="detail-description-editor" name="detail_description" rows="10"><?= htmlspecialchars($edit_row['detail_description'] ?? '') ?></textarea>
    </div>
    <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? count($listings) + 1) ?>"></div>

    <div class="field">
      <label>Photo</label>
      <?php $currentUrl = $edit_row ? PocketBase::fileUrl($edit_row, 'image', 'machinery_listings') : null; ?>
      <?php if ($currentUrl): ?>
        <div class="image-preview"><img src="<?= htmlspecialchars($currentUrl) ?>" alt=""></div>
        <label class="checkbox-line"><input type="checkbox" name="remove_image" value="1"> Remove this photo</label>
      <?php else: ?>
        <div class="sub" style="margin:6px 0;">No photo set — a default placeholder photo is shown on the site until you upload one.</div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add Listing' ?></button>
    <?php if ($edit_row): ?> <a href="machinery-listings.php?category=<?= htmlspecialchars($selected_category) ?>">Cancel</a><?php endif; ?>
  </form>
</div>

<!-- Rich text editor for Read More Page Description — same self-hosted
     TinyMCE setup used elsewhere in the admin panel. -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#detail-description-editor',
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

<table>
  <tr><th>Photo</th><th>Order</th><th>Category</th><th>Brand / Model</th><th>Origin</th><th>Year</th><th>Price</th><th>Actions</th></tr>
  <?php if (!$listings): ?><tr><td colspan="8">No listings yet.</td></tr><?php endif; ?>
  <?php foreach ($listings as $l): ?>
    <?php $thumb = PocketBase::fileUrl($l, 'image', 'machinery_listings'); ?>
    <tr>
      <td><?php if ($thumb): ?><img class="thumb" src="<?= htmlspecialchars($thumb) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
      <td><?= (int)$l['sort_order'] ?></td>
      <td><?= htmlspecialchars(category_title($categories, $l['category'])) ?></td>
      <td><?= htmlspecialchars(trim(($l['brand'] ?? '') . ' ' . ($l['model'] ?? '')) ?: '—') ?></td>
      <td><?= htmlspecialchars($l['origin'] ?: '—') ?></td>
      <td><?= htmlspecialchars($l['year'] ?: '—') ?></td>
      <td><?= htmlspecialchars($l['indicative_price'] ?: '—') ?></td>
      <td>
        <a class="btn btn-sm" href="machinery-listings.php?category=<?= htmlspecialchars($selected_category) ?>&edit=<?= $l['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="machinery-listings.php?category=<?= htmlspecialchars($selected_category) ?>&delete=<?= $l['id'] ?>" onclick="return confirm('Delete this listing?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>
