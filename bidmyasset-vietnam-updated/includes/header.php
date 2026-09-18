<?php
// Expects: $settings (array from get_settings), $country (string), $page_title (optional)
$page_title = $page_title ?? ($settings['site_title'] ?? 'BidMyAsset');
$flag = $country === 'vietnam' ? '🇻🇳' : '🇮🇳';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?></title>
<link rel="icon" type="image/x-icon" href="<?= h($base_path ?? '') ?>assets/favicon.ico">
<link rel="icon" type="image/png" sizes="16x16" href="<?= h($base_path ?? '') ?>assets/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= h($base_path ?? '') ?>assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?= h($base_path ?? '') ?>assets/favicon-192x192.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= h($base_path ?? '') ?>assets/apple-touch-icon.png">
<script>
  // Apply any saved site-wide font-size preference immediately, before the
  // page paints, so navigating between pages doesn't flash back to 100%.
  (function () {
    var saved = localStorage.getItem('bma_font_zoom');
    if (saved) document.documentElement.style.zoom = saved;
  })();
</script>
<link rel="stylesheet" href="<?= h($base_path ?? '') ?>assets/style.css?v=<?= @filemtime(__DIR__ . '/../assets/style.css') ?: '1' ?>">
</head>
<body>

<header>
  <div class="header-inner">
    <div class="logo-block">
      <a href="<?= h($base_path ?? '') ?>index.php">
        <img class="logo" src="<?= h(pb_image($settings ?? [], 'logo_image', 'site_settings', ($base_path ?? '') . 'assets/logo_bidmyasset.png')) ?>" alt="BidMyAsset logo">
      </a>
      <div class="tagline">Your Asset Disposal Management Partner</div>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <nav id="mainNav">
      <a href="<?= h($base_path ?? '') ?>index.php#services">Service</a>
      <a href="<?= h($base_path ?? '') ?>index.php#auction">Auction</a>
      <a href="<?= h($base_path ?? '') ?>index.php#contact">Contact Us</a>
      <a href="<?= h($base_path ?? '') ?>index.php#reference">Reference</a>
      <a href="<?= h($base_path ?? '') ?>index.php#machinery">Machinery</a>
      <a href="<?= h($base_path ?? '') ?>news.php">News</a>
      <div class="nav-dropdown">
        <a href="#" class="dropdown-toggle">Corporate Links ▾</a>
        <div class="dropdown-menu">
          <a href="https://bidmyasset.online/" target="_blank" rel="noopener">Auction</a>
          <a href="https://bidmyasset.online/" target="_blank" rel="noopener">Corporate Website</a>
        </div>
      </div>
    </nav>
    <div class="font-control" role="group" aria-label="Adjust text size">
      <button type="button" id="decrease-font" aria-label="Decrease text size">A-</button>
      <button type="button" id="reset-font" aria-label="Reset text size">A</button>
      <button type="button" id="increase-font" aria-label="Increase text size">A+</button>
    </div>
    <div class="lang">
      <?= $flag ?>
    </div>
  </div>
</header>
