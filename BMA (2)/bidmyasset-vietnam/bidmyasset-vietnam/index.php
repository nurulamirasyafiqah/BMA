<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);
$services  = get_services($country);
$news      = get_news($country);
$machinery = get_machinery($country);
$trusted   = get_trusted_logos($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$page_title = $settings['site_title'];
include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-text">
      <h1><?= h($settings['hero_title']) ?></h1>
      <div class="sub"><?= h($settings['hero_subtitle']) ?></div>
      <p><?= h($settings['hero_text']) ?></p>
      <a class="btn" href="about.php">About Us</a>
    </div>
    <div class="hero-img">
      <img src="<?= h(pb_image($settings, 'hero_image', 'site_settings', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="Industrial machinery">
    </div>
  </div>
</section>

<hr class="divider">

<!-- SERVICES -->
<section class="services" id="services">
  <div class="container">
    <h2>Services</h2>
    <p><?= h($settings['services_intro']) ?></p>
    <div class="service-grid">
      <?php foreach ($services as $svc): ?>
      <div class="service-card">
        <span class="star">✳</span>
        <h3><?= h($svc['title']) ?></h3>
        <p><?= h($svc['description']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PROCESS / ESG -->
<section class="process">
  <div class="container">
    <div class="process-diagram">
      <img src="<?= h(pb_image($settings, 'process_image', 'site_settings', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="Process diagram illustrating BidMyAsset's workflow">
    </div>
    <a class="btn" href="discover.php">Discover More</a>
  </div>
</section>

<!-- AUCTION -->
<section class="auction" id="auction">
  <div class="container auction-inner">
    <div class="auction-img">
      <img src="<?= h(pb_image($settings, 'auction_image', 'site_settings', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="Used industrial machinery at auction">
    </div>
    <div class="auction-text">
      <span class="eyebrow">BidMyAsset Auction</span>
      <h2>Leading Machinery Auction Platform</h2>
      <p><?= h($settings['site_title']) ?> has over 20 years of combined experience helping businesses buy and sell used machinery, second hand machinery, and used industrial equipment through live and online auctions.</p>
      <p>We handle everything from single machine lots to complete shutdown factory inventories, connecting sellers with verified buyers across the region. Every auction runs with full transparency, so you always know what you're getting.</p>
      <a class="btn" href="<?= h($settings['live_auction_url']) ?>" target="_blank" rel="noopener">Live Auctions</a>
    </div>
  </div>
</section>

<!-- QUOTE -->
<section class="quote">
  <blockquote>"<?= h($settings['founder_quote']) ?>"</blockquote>
  <img class="quote-avatar" src="<?= h(pb_image($settings, 'founder_avatar', 'site_settings', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="Founder portrait">
  <div class="name"><?= h($settings['founder_name']) ?></div>
  <div class="role">Founder, BidMyAsset</div>
</section>

<!-- CONTACT -->
<section class="contact" id="contact">
  <div class="container">
    <h2>Contact Us</h2>
    <p><?= h($settings['site_title']) ?>. Looking to buy cheap machinery, source used industrial equipment, or sell machine assets from a shutdown factory? We're here to help. Get in touch and our team will point you in the right direction with no obligation, no runaround.</p>

    <?php if (isset($_GET['sent']) && $_GET['sent'] === '1'): ?>
      <div class="form-msg success">Thanks — your message has been sent. Our team will get back to you shortly.</div>
    <?php elseif (isset($_GET['sent']) && $_GET['sent'] === '0'): ?>
      <div class="form-msg error">Something went wrong sending your message. Please check the form and try again.</div>
    <?php endif; ?>

    <div class="contact-grid">
      <div class="contact-form">
        <h3>Contact Us</h3>
        <form action="contact_submit.php" method="post">
          <input type="hidden" name="country" value="<?= h($country) ?>">
          <div class="field"><label>Name</label><input type="text" name="name" required></div>
          <div class="field"><label>Company</label><input type="text" name="company"></div>
          <div class="field"><label>Phone</label><input type="text" name="phone"></div>
          <div class="field"><label>Email</label><input type="email" name="email" required></div>
          <div class="field"><label>Question / Comment</label><textarea name="message" rows="3"></textarea></div>
          <div class="form-actions">
            <button type="reset" class="clear-link">↻ Clear form</button>
            <button type="submit" class="btn">Submit</button>
          </div>
        </form>
      </div>
      <div class="contact-map">
        <?php if (!empty($settings['map_embed_url'])): ?>
          <div class="map-embed">
            <iframe src="<?= h($settings['map_embed_url']) ?>" width="100%" height="280" style="border:0;" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="<?= h($settings['office_name']) ?> location"></iframe>
          </div>
        <?php else: ?>
          <div class="map-box">Map — <?= h($settings['office_name']) ?></div>
        <?php endif; ?>
        <div class="contact-details">
          <p><strong><?= h($settings['office_name']) ?></strong></p>
          <p><?= h($settings['address_line1']) ?></p>
          <p><?= h($settings['address_line2']) ?></p>
          <p><?= h($settings['address_line3']) ?></p>
          <p><?= h($settings['email']) ?></p>
          <p><?= h($settings['phone']) ?></p>
        </div>
        <div class="social-row">
          <a href="<?= h($settings['facebook_url']) ?>" target="_blank" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
          </a>
          <a href="<?= h($settings['linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
            <svg viewBox="0 0 24 24"><path d="M6.9 8.4H3.6V20H6.9V8.4ZM5.3 3.5A1.9 1.9 0 1 0 5.3 7.3 1.9 1.9 0 0 0 5.3 3.5ZM20.4 20H17.1V14C17.1 12.5 16.6 11.5 15.3 11.5c-1 0-1.6.7-1.9 1.3-.1.2-.1.5-.1.8V20H10c0-11 0-11.6 0-11.6h3.3v1.6c.4-.7 1.2-1.7 3-1.7 2.2 0 3.9 1.4 3.9 4.5V20Z"/></svg>
          </a>
          <a href="<?= h($settings['tiktok_url']) ?>" target="_blank" rel="noopener" aria-label="TikTok">
            <svg viewBox="0 0 24 24"><path d="M16.7 3h-3v13a2.7 2.7 0 1 1-1.9-2.6v-3.1a5.8 5.8 0 1 0 4.9 5.7V9.4a7.7 7.7 0 0 0 4.3 1.3V7.6a4.8 4.8 0 0 1-4.3-4.6Z"/></svg>
          </a>
          <a href="<?= h($settings['youtube_url']) ?>" target="_blank" rel="noopener" aria-label="YouTube">
            <svg viewBox="0 0 24 24"><path d="M23 12s0-3.4-.4-5a2.9 2.9 0 0 0-2-2C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.6.5a2.9 2.9 0 0 0-2 2C1 8.6 1 12 1 12s0 3.4.4 5a2.9 2.9 0 0 0 2 2c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.9 2.9 0 0 0 2-2c.4-1.6.4-5 .4-5ZM9.8 15.5v-7l6 3.5Z"/></svg>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TRUSTED BY -->
<section class="trusted" id="reference">
  <div class="container trusted-inner">
    <div class="trusted-text">
      <span class="eyebrow">Recognised &amp; Trusted</span>
      <h2>Trusted By</h2>
      <p><?= h($settings['site_title']) ?> is trusted by manufacturers, factories, and industrial businesses across the region.</p>
      <a class="btn" href="index.php#contact">Contact Us</a>
    </div>
    <div class="trusted-logos">
      <?php foreach ($trusted as $logo): ?>
      <div class="logo-item">
        <?php $logoUrl = pb_image($logo, 'logo', 'trusted_logos', ''); ?>
        <?php if ($logoUrl): ?>
          <img src="<?= h($logoUrl) ?>" alt="<?= h($logo['name']) ?>">
        <?php else: ?>
          <?= h($logo['name']) ?>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BANNER STRIP -->
<div class="banner-strip">
  <img src="<?= h(pb_image($settings, 'factory_image_1', 'site_settings', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="Factory interior with idle machinery">
  <img src="<?= h(pb_image($settings, 'factory_image_2', 'site_settings', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="Empty shutdown factory floor">
</div>

<!-- MACHINERY LISTED -->
<section class="listed" id="machinery">
  <div class="container">
    <h2>Used Machinery Listed</h2>
    <div class="listed-grid">
      <?php foreach ($machinery as $m): ?>
      <div class="item">
        <a href="machinery-category.php?id=<?= h($m['id']) ?>">
          <img src="<?= h(pb_image($m, 'image', 'machinery_items', ($base_path ?? '') . 'assets/placeholder.svg')) ?>" alt="<?= h($m['title']) ?>">
          <span><?= h($m['title']) ?></span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- NEWS -->
<section class="news" id="news">
  <div class="container">
    <h2>News</h2>
    <div class="news-grid">
      <?php foreach ($news as $item): ?>
      <div class="news-card">
        <h4><?= h($item['title']) ?></h4>
        <div class="date"><?= h(date('d/m/Y', strtotime($item['news_date']))) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
