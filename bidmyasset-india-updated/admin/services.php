<?php
require_once 'includes/auth.php';
require_once '../config.php';

$admin_title = 'Services';
// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$message_is_error = false;
$edit_row = null;

// Handle delete
if (isset($_GET['delete'])) {
    PocketBase::delete('services', $_GET['delete'], $pb_token);
    header("Location: services.php?deleted=1");
    exit;
}

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = trim($_POST['id'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);

    if ($id !== '') {
        $saved = PocketBase::update('services', $id, [
            'title' => $title, 'description' => $description, 'sort_order' => $sort_order,
        ], $pb_token);
        if ($saved) {
            $message = 'Service updated.';
        } else {
            $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
            $message_is_error = true;
        }
    } else {
        $saved = PocketBase::create('services', [
            'country' => $country, 'title' => $title, 'description' => $description, 'sort_order' => $sort_order,
        ], $pb_token);
        if ($saved) {
            $message = 'Service added.';
        } else {
            $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
            $message_is_error = true;
        }
    }
}

// Handle edit-load
if (isset($_GET['edit'])) {
    $edit_row = PocketBase::view('services', $_GET['edit'], $pb_token);
}

if (isset($_GET['deleted'])) { $message = 'Service deleted.'; }

$services = PocketBase::list('services', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'sort'    => '+sort_order,+id',
    'perPage' => 100,
], $pb_token);

include 'includes/admin_header.php';
?>

<h1>Services</h1>
<div class="sub">Manage the service cards shown on the homepage.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-top:0;"><?= $edit_row ? 'Edit Service' : 'Add New Service' ?></h3>
  <form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id'] ?? '') ?>">
    <div class="field"><label>Title</label><input type="text" name="title" required value="<?= htmlspecialchars($edit_row['title'] ?? '') ?>"></div>
    <div class="field"><label>Description</label><textarea name="description" rows="3" required><?= htmlspecialchars($edit_row['description'] ?? '') ?></textarea></div>
    <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($edit_row['sort_order'] ?? count($services) + 1) ?>"></div>
    <button type="submit" class="btn"><?= $edit_row ? 'Save Changes' : 'Add Service' ?></button>
    <?php if ($edit_row): ?> <a href="services.php">Cancel</a><?php endif; ?>
  </form>
</div>

<table>
  <tr><th>Order</th><th>Title</th><th>Description</th><th>Actions</th></tr>
  <?php foreach ($services as $s): ?>
    <tr>
      <td><?= (int)$s['sort_order'] ?></td>
      <td><?= htmlspecialchars($s['title']) ?></td>
      <td><?= htmlspecialchars(admin_truncate($s['description'], 90)) ?></td>
      <td>
        <a class="btn btn-sm" href="services.php?edit=<?= $s['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="services.php?delete=<?= $s['id'] ?>" onclick="return confirm('Delete this service?');">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include 'includes/admin_footer.php'; ?>
