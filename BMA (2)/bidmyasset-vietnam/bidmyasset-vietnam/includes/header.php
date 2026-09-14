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
<link rel="stylesheet" href="<?= h($base_path ?? '') ?>assets/style.css">
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
      <a href="<?= h($base_path ?? '') ?>index.php#news">News</a>
      <div class="nav-dropdown">
        <a href="#" class="dropdown-toggle">Corporate Links ▾</a>
        <div class="dropdown-menu">
          <a href="https://bidmyasset.online/" target="_blank" rel="noopener">Auction</a>
          <a href="https://bidmyasset.online/" target="_blank" rel="noopener">Corporate Website</a>
        </div>
      </div>
    </nav>
    <div class="lang">
      <?= $flag ?>
    </div>
  </div>
</header>
