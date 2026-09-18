<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$listing_id = trim($_GET['id'] ?? '');
$listing    = $listing_id !== '' ? PocketBase::view('machinery_listings', $listing_id) : null;

// Make sure the listing actually belongs to this site's country, and load
// its category so the "Back" link can send people to the right place.
$category = null;
if ($listing && ($listing['country'] ?? '') === $country) {
    $category = get_machinery_category($listing['category'], $country);
} else {
    $listing = null;
}

if (!$listing || !$category) {
    http_response_code(404);
    $page_title = 'Not Found — ' . $settings['site_title'];
    include 'includes/header.php';
    ?>
    <section class="placeholder-page">
      <div class="container">
        <h1>Listing Not Found</h1>
        <p>This machine listing doesn't exist or isn't available for this site.</p>
        <a class="btn" href="index.php#machinery">Back to Home</a>
      </div>
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

$listing_name = trim(($listing['brand'] ?? '') . ' ' . ($listing['model'] ?? ''));
$page_title = ($listing_name ?: $category['title']) . ' — ' . $settings['site_title'];
include 'includes/header.php';
?>

<section class="category-page">
  <div class="container">
    <a class="back-link" href="machinery-category.php?id=<?= h($category['id']) ?>">&larr; Back to <?= h($category['title']) ?></a>
    <h1><?= h($listing_name ?: $category['title']) ?></h1>

    <div class="listing-detail">
      <img src="<?= h(pb_image($listing, 'image', 'machinery_listings', $base_path . 'assets/placeholder.svg')) ?>" alt="<?= h($listing_name ?: $category['title']) ?>">
      <div class="specs">
        <?php
          $specs = [
              'Brand'             => $listing['brand'] ?? '',
              'Model'             => $listing['model'] ?? '',
              'Origin'            => $listing['origin'] ?? '',
              'Year'              => $listing['year'] ?? '',
              'Key Specs'         => $listing['key_specs'] ?? '',
              'Indicative Price'  => $listing['indicative_price'] ?? '',
          ];
        ?>
        <?php foreach ($specs as $label => $value): ?>
          <div class="row"><?= h($label) ?><?= $value !== '' ? ': ' . h($value) : '' ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!empty($listing['detail_description'])): ?>
      <div class="category-desc article-content rich-collapse">
        <?php render_rich_content($listing['detail_description']); ?>
      </div>
      <button type="button" class="rich-collapse-toggle">Show more</button>
    <?php else: ?>
      <p>More detail about this machine will go here — an admin can add this from Machinery Listings in the admin panel.</p>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
