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
include 'includes/header.php';
?>

<section class="placeholder-page">
  <div class="container">
    <h1><?= h($settings['discover_title'] ?: 'Discover More') ?></h1>
    <?php if (!empty($settings['discover_description'])): ?>
      <?php foreach (preg_split('/\r?\n\r?\n/', trim($settings['discover_description'])) as $para): ?>
        <?php if (trim($para) !== ''): ?><p><?= nl2br(h($para)) ?></p><?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p>More detail about the <?= h($settings['site_title']) ?> process and ESG commitments will go here — an admin can add this from the Site Settings page.</p>
    <?php endif; ?>
    <a class="btn" href="index.php">Back to Home</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
