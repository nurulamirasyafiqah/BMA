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
include 'includes/header.php';
?>

<section class="placeholder-page">
  <div class="container">
    <h1>About Us</h1>
    <p>This page is empty for now — content about <?= h($settings['site_title']) ?> will go here.</p>
    <a class="btn" href="index.php">Back to Home</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
