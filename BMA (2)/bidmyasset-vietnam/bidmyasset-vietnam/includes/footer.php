<?php $bp = $base_path ?? ''; ?>
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <img class="logo" src="<?= h(pb_image($settings ?? [], 'logo_image', 'site_settings', $bp . 'assets/logo_bidmyasset.png')) ?>" alt="BidMyAsset logo">
        <p><?= h($settings['site_title'] ?? 'BidMyAsset') ?><br>Your Asset Disposal Management Partner</p>
      </div>
      <div class="footer-col">
        <h5>Company</h5>
        <a href="<?= h($bp) ?>about.php">About</a>
        <a href="<?= h($bp) ?>index.php#reference">Reference</a>
      </div>
      <div class="footer-col">
        <h5>Legal</h5>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms and Conditions</a>
        <a href="<?= h($bp) ?>index.php#contact">Contact Us</a>
      </div>
      <div class="footer-col">
        <h5>Follow Us</h5>
        <div class="social-row">
          <a href="<?= h($settings['facebook_url'] ?? '#') ?>" target="_blank" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
          </a>
          <a href="<?= h($settings['linkedin_url'] ?? '#') ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
            <svg viewBox="0 0 24 24"><path d="M6.9 8.4H3.6V20H6.9V8.4ZM5.3 3.5A1.9 1.9 0 1 0 5.3 7.3 1.9 1.9 0 0 0 5.3 3.5ZM20.4 20H17.1V14C17.1 12.5 16.6 11.5 15.3 11.5c-1 0-1.6.7-1.9 1.3-.1.2-.1.5-.1.8V20H10c0-11 0-11.6 0-11.6h3.3v1.6c.4-.7 1.2-1.7 3-1.7 2.2 0 3.9 1.4 3.9 4.5V20Z"/></svg>
          </a>
          <a href="<?= h($settings['tiktok_url'] ?? '#') ?>" target="_blank" rel="noopener" aria-label="TikTok">
            <svg viewBox="0 0 24 24"><path d="M16.7 3h-3v13a2.7 2.7 0 1 1-1.9-2.6v-3.1a5.8 5.8 0 1 0 4.9 5.7V9.4a7.7 7.7 0 0 0 4.3 1.3V7.6a4.8 4.8 0 0 1-4.3-4.6Z"/></svg>
          </a>
          <a href="<?= h($settings['youtube_url'] ?? '#') ?>" target="_blank" rel="noopener" aria-label="YouTube">
            <svg viewBox="0 0 24 24"><path d="M23 12s0-3.4-.4-5a2.9 2.9 0 0 0-2-2C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.6.5a2.9 2.9 0 0 0-2 2C1 8.6 1 12 1 12s0 3.4.4 5a2.9 2.9 0 0 0 2 2c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.9 2.9 0 0 0 2-2c.4-1.6.4-5 .4-5ZM9.8 15.5v-7l6 3.5Z"/></svg>
          </a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">© <?= date('Y') ?> <?= h($settings['site_title'] ?? 'BidMyAsset') ?></div>
  </div>
</footer>

<script>
  // Corporate Links dropdown: click-to-toggle for touch devices, hover still works via CSS.
  document.querySelectorAll('.nav-dropdown > .dropdown-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      var dropdown = toggle.closest('.nav-dropdown');
      var wasOpen = dropdown.classList.contains('open');
      document.querySelectorAll('.nav-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
      if (!wasOpen) dropdown.classList.add('open');
    });
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.nav-dropdown')) {
      document.querySelectorAll('.nav-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
    }
  });

  // Mobile hamburger menu for the main site navigation.
  (function () {
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (!toggle || !nav) return;
    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('open');
      toggle.classList.toggle('open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        if (a.classList.contains('dropdown-toggle')) return; // dropdown handles itself
        nav.classList.remove('open');
        toggle.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  })();
</script>

</body>
</html>
