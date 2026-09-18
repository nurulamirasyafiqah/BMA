<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$page_title = 'About Us — ' . $settings['site_title'];
$image_url  = pb_image($settings, 'about_image', 'site_settings', '');
include 'includes/header.php';
?>

<section class="category-page">
  <div class="container">
    <h1><?= h($settings['about_title'] ?: 'About Us') ?></h1>

    <?php if ($image_url): ?>
      <img class="article-image" src="<?= h($image_url) ?>" alt="<?= h($settings['about_title'] ?: 'About Us') ?>">
    <?php endif; ?>

    <?php if (!empty($settings['about_content'])): ?>
      <div class="article-content"><?= $settings['about_content'] ?></div>
    <?php else: ?>
      <p>This page is empty for now — content about <?= h($settings['site_title']) ?> will go here. An admin can add this from Site Settings → "About Us" Page.</p>
    <?php endif; ?>

    <a class="back-link" href="index.php">&larr; Back to Home</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
