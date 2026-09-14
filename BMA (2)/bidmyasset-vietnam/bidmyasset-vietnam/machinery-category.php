<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$category_id = trim($_GET['id'] ?? '');
$category    = $category_id !== '' ? get_machinery_category($category_id, $country) : null;

if (!$category) {
    http_response_code(404);
    $page_title = 'Not Found — ' . $settings['site_title'];
    include 'includes/header.php';
    ?>
    <section class="placeholder-page">
      <div class="container">
        <h1>Category Not Found</h1>
        <p>This machinery category doesn't exist or isn't available for this site.</p>
        <a class="btn" href="index.php#machinery">Back to Home</a>
      </div>
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

$listings = get_machinery_listings($category['id'], $country);

$page_title = h($category['title']) . ' — ' . $settings['site_title'];
include 'includes/header.php';
?>

<section class="category-page">
  <div class="container">
    <a class="back-link" href="index.php#machinery">&larr; Back to Used Machinery</a>
    <h1><?= h($category['title']) ?></h1>

    <?php if (!empty($category['description'])): ?>
      <div class="category-desc">
        <?php foreach (preg_split('/\r?\n\r?\n/', trim($category['description'])) as $para): ?>
          <?php if (trim($para) !== ''): ?><p><?= nl2br(h($para)) ?></p><?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h2>Used <?= h($category['title']) ?> For Sale</h2>

    <?php if ($listings): ?>
      <div class="listing-grid">
        <?php foreach ($listings as $l): ?>
          <?php
            $specs = [
                'Brand'             => $l['brand'] ?? '',
                'Model'             => $l['model'] ?? '',
                'Origin'            => $l['origin'] ?? '',
                'Year'              => $l['year'] ?? '',
                'Key Specs'         => $l['key_specs'] ?? '',
                'Indicative Price'  => $l['indicative_price'] ?? '',
            ];
          ?>
          <div class="listing-card">
            <img src="<?= h(pb_image($l, 'image', 'machinery_listings', $base_path . 'assets/placeholder.svg')) ?>" alt="<?= h($category['title']) ?> - <?= h($l['brand'] ?: 'listing') ?>">
            <div class="specs">
              <?php foreach ($specs as $label => $value): ?>
                <div class="row"><?= h($label) ?><?= $value !== '' ? ': ' . h($value) : '' ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="no-listings">No <?= h($category['title']) ?> listings available right now — check back soon, or <a href="index.php#contact">contact us</a> for the latest availability.</p>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
