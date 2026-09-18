<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$page_title = 'Discover More — ' . $settings['site_title'];
$image_url  = pb_image($settings, 'discover_image', 'site_settings', '');
include 'includes/header.php';
?>

<section class="category-page">
  <div class="container">
    <h1><?= h($settings['discover_title'] ?: 'Discover More') ?></h1>

    <?php if ($image_url): ?>
      <img class="article-image" src="<?= h($image_url) ?>" alt="<?= h($settings['discover_title'] ?: 'Discover More') ?>">
    <?php endif; ?>

    <?php if (!empty($settings['discover_description'])): ?>
      <?php if (strpos($settings['discover_description'], '<') === false): ?>
        <!-- Content saved before this page had a rich-text editor: it's
             plain text with blank-line paragraph breaks, not HTML yet. -->
        <?php foreach (preg_split('/\r?\n\r?\n/', trim($settings['discover_description'])) as $para): ?>
          <?php if (trim($para) !== ''): ?><p><?= nl2br(h($para)) ?></p><?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="article-content"><?= $settings['discover_description'] ?></div>
      <?php endif; ?>
    <?php else: ?>
      <p>More detail about the <?= h($settings['site_title']) ?> process and ESG commitments will go here — an admin can add this from the Site Settings page.</p>
    <?php endif; ?>
    <a class="back-link" href="index.php">&larr; Back to Home</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
