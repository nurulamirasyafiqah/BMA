<?php $bp = $base_path ?? ''; ?>
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <img class="logo" src="<?= h(pb_image($settings ?? [], 'logo_image', 'site_settings', $bp . 'assets/logo_bidmyasset.png')) ?>" alt="BidMyAsset logo">
        <p><?= h($settings['site_title'] ?? 'BidMyAsset') ?><br>Your Asset Disposal Management Partner</p>
      </div>
      <div class="footer-col">
        <h5>About</h5>
        <a href="https://bidmyasset.com/leadership" target="_blank" rel="noopener">Team</a>
        <a href="<?= h($bp) ?>index.php">Record</a>
        <a href="<?= h($bp) ?>index.php">Occupation</a>
      </div>
      <div class="footer-col">
        <h5>Privacy</h5>
        <a href="#" class="footer-modal-trigger" data-modal="privacy-modal">Privacy Policy</a>
        <a href="#" class="footer-modal-trigger" data-modal="terms-modal">Terms and Conditions</a>
        <a href="<?= h($bp) ?>index.php#contact">Contact Us</a>
      </div>
      <div class="footer-col">
        <h5>Society</h5>
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

<!-- Privacy Policy / Terms and Conditions popups: content + "Last Updated"
     are admin-editable in Site Settings → "Privacy Policy & Terms". -->
<div class="site-modal" id="privacy-modal" aria-hidden="true">
  <div class="site-modal-overlay" data-modal-close></div>
  <div class="site-modal-box" role="dialog" aria-modal="true" aria-labelledby="privacy-modal-title">
    <div class="site-modal-header">
      <h2 id="privacy-modal-title">Privacy Policy</h2>
      <button type="button" class="site-modal-close" data-modal-close aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
    <div class="site-modal-body">
      <?php if (!empty($settings['privacy_policy_updated'])): ?>
        <p class="site-modal-updated">Last Updated: <?= h(date('d F Y', strtotime($settings['privacy_policy_updated']))) ?></p>
      <?php endif; ?>
      <?php if (!empty($settings['privacy_policy_content'])): ?>
        <?= $settings['privacy_policy_content'] ?>
      <?php else: ?>
        <p>Content coming soon.</p>
      <?php endif; ?>
    </div>
    <button type="button" class="modal-scroll-top" data-modal-scroll-top aria-label="Scroll back to top" title="Back to top">
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.2"/>
        <path d="M12 16.5V8.2M12 8.2 8 12.2M12 8.2l4 4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</div>
<div class="site-modal" id="terms-modal" aria-hidden="true">
  <div class="site-modal-overlay" data-modal-close></div>
  <div class="site-modal-box" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title">
    <div class="site-modal-header">
      <h2 id="terms-modal-title">Terms and Conditions</h2>
      <button type="button" class="site-modal-close" data-modal-close aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
    <div class="site-modal-body">
      <?php if (!empty($settings['terms_updated'])): ?>
        <p class="site-modal-updated">Last Updated: <?= h(date('d F Y', strtotime($settings['terms_updated']))) ?></p>
      <?php endif; ?>
      <?php if (!empty($settings['terms_content'])): ?>
        <?= $settings['terms_content'] ?>
      <?php else: ?>
        <p>Content coming soon.</p>
      <?php endif; ?>
    </div>
    <button type="button" class="modal-scroll-top" data-modal-scroll-top aria-label="Scroll back to top" title="Back to top">
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.2"/>
        <path d="M12 16.5V8.2M12 8.2 8 12.2M12 8.2l4 4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</div>

<button type="button" id="scrollTopBtn" class="scroll-top-btn" aria-label="Scroll back to top" title="Back to top">
  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.2"/>
    <path d="M12 16.5V8.2M12 8.2 8 12.2M12 8.2l4 4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</button>

<script>
  // Footer "Privacy Policy" / "Terms and Conditions" popups.
  document.querySelectorAll('.footer-modal-trigger').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var modal = document.getElementById(link.getAttribute('data-modal'));
      if (!modal) return;
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
    });
  });
  function closeSiteModal(modal) {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }
  document.querySelectorAll('[data-modal-close]').forEach(function (el) {
    el.addEventListener('click', function () {
      var modal = el.closest('.site-modal');
      if (modal) closeSiteModal(modal);
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.site-modal.open').forEach(closeSiteModal);
  });

  // Scroll-to-top button inside each popup: shows once the popup body has
  // been scrolled down a bit, scrolls that popup (not the page) back up.
  document.querySelectorAll('.site-modal').forEach(function (modal) {
    var body = modal.querySelector('.site-modal-body');
    var btn = modal.querySelector('[data-modal-scroll-top]');
    if (!body || !btn) return;
    body.addEventListener('scroll', function () {
      btn.classList.toggle('visible', body.scrollTop > 120);
    });
    btn.addEventListener('click', function () {
      body.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

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

  // Site-wide font-size control (A- / A / A+) in the navbar. Uses CSS zoom
  // so it scales the whole page consistently without needing every
  // stylesheet rule to be rewritten in rem; the choice is saved so it
  // carries over when navigating to any other page.
  (function () {
    var STORAGE_KEY = 'bma_font_zoom';
    var MIN = 0.8, MAX = 1.5, STEP = 0.1;
    var decBtn = document.getElementById('decrease-font');
    var resetBtn = document.getElementById('reset-font');
    var incBtn = document.getElementById('increase-font');
    if (!decBtn || !resetBtn || !incBtn) return;

    function current() {
      var saved = parseFloat(localStorage.getItem(STORAGE_KEY));
      return isNaN(saved) ? 1 : saved;
    }
    function apply(value) {
      value = Math.min(MAX, Math.max(MIN, value));
      document.documentElement.style.zoom = value;
      localStorage.setItem(STORAGE_KEY, value);
    }

    decBtn.addEventListener('click', function () { apply(Math.round((current() - STEP) * 10) / 10); });
    incBtn.addEventListener('click', function () { apply(Math.round((current() + STEP) * 10) / 10); });
    resetBtn.addEventListener('click', function () { apply(1); });
  })();

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

  // Image carousels (e.g. the Auction section photo gallery): click the
  // arrows or a dot to move between images.
  document.querySelectorAll('[data-carousel]').forEach(function (carousel) {
    var slides = carousel.querySelectorAll('.carousel-slide');
    var dots   = carousel.querySelectorAll('.carousel-dots .dot');
    if (slides.length < 2) return;
    var current = 0;

    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (s, i) { s.classList.toggle('active', i === current); });
      dots.forEach(function (d, i) { d.classList.toggle('active', i === current); });
    }

    var prevBtn = carousel.querySelector('.carousel-arrow.prev');
    var nextBtn = carousel.querySelector('.carousel-arrow.next');
    if (prevBtn) prevBtn.addEventListener('click', function () { show(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { show(current + 1); });
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { show(i); });
    });
  });

  // "More" / "Less" toggle for long (100+ word) admin descriptions.
  document.querySelectorAll('.desc-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var wrap  = btn.parentElement;
      var short = wrap.querySelector('.desc-short');
      var full  = wrap.querySelector('.desc-full');
      var expanded = btn.getAttribute('aria-expanded') === 'true';
      short.hidden = !expanded;
      full.hidden  = expanded;
      btn.textContent = expanded ? 'More' : 'Less';
      btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    });
  });

  // Scroll-back-to-top button: only visible once the page has been
  // scrolled down a bit, and smooth-scrolls to the top when clicked.
  (function () {
    var btn = document.getElementById('scrollTopBtn');
    if (!btn) return;
    function toggleBtn() {
      btn.classList.toggle('visible', window.scrollY > 400);
    }
    window.addEventListener('scroll', toggleBtn, { passive: true });
    toggleBtn();
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  })();

  // "Show more" / "Show less" for long rich-text content (Category
  // Description, Bottom Description, Read More Page Description).
  // Height-based (not word-count) so it works safely with tables, lists,
  // and any other HTML from the admin's rich-text editor — it clips the
  // box visually instead of cutting the HTML content itself.
  document.querySelectorAll('.rich-collapse').forEach(function (box) {
    var toggle = box.nextElementSibling;
    if (!toggle || !toggle.classList.contains('rich-collapse-toggle')) return;

    box.classList.add('collapsed');
    requestAnimationFrame(function () {
      if (box.scrollHeight <= box.clientHeight + 4) {
        // Content already fits within the collapsed height — no need
        // for a toggle at all.
        box.classList.remove('collapsed');
        toggle.hidden = true;
      }
    });

    toggle.addEventListener('click', function () {
      var nowCollapsed = box.classList.toggle('collapsed');
      toggle.textContent = nowCollapsed ? 'Show more' : 'Show less';
    });
  });

  // Before/after image comparison slider: drag (mouse, touch, or pen) the
  // divider left/right to reveal more of either photo.
  document.querySelectorAll('[data-compare]').forEach(function (slider) {
    var before = slider.querySelector('.compare-before');
    var handle = slider.querySelector('.compare-handle');
    if (!before || !handle) return;
    var dragging = false;

    // Size the slider to match the uploaded image's own proportions —
    // whatever size/orientation the admin uploads — instead of a fixed
    // aspect ratio, so nothing gets cropped.
    var sizerImg = slider.querySelector('.compare-after img');
    function applySize() {
      if (sizerImg && sizerImg.naturalWidth && sizerImg.naturalHeight) {
        slider.style.aspectRatio = sizerImg.naturalWidth + ' / ' + sizerImg.naturalHeight;
      }
    }
    if (sizerImg) {
      if (sizerImg.complete) applySize();
      else sizerImg.addEventListener('load', applySize);
    }

    function setPosition(clientX) {
      var rect = slider.getBoundingClientRect();
      var pct = ((clientX - rect.left) / rect.width) * 100;
      pct = Math.max(0, Math.min(100, pct));
      handle.style.left = pct + '%';
      before.style.clipPath = 'inset(0 ' + (100 - pct) + '% 0 0)';
    }

    slider.addEventListener('pointerdown', function (e) {
      dragging = true;
      slider.setPointerCapture(e.pointerId);
      setPosition(e.clientX);
    });
    slider.addEventListener('pointermove', function (e) {
      if (dragging) setPosition(e.clientX);
    });
    ['pointerup', 'pointercancel'].forEach(function (evt) {
      slider.addEventListener(evt, function () { dragging = false; });
    });
  });

  // Homepage "Used Machinery Listed" slider: arrows move by one page of
  // cards, and dots below show/jump to the current page.
  document.querySelectorAll('[data-listed-slider]').forEach(function (track) {
    var wrap = track.closest('.listed-slider-wrap');
    var section = track.closest('.listed');
    if (!wrap || !section) return;
    var prevBtn = wrap.querySelector('.listed-slider-prev');
    var nextBtn = wrap.querySelector('.listed-slider-next');
    var dotsWrap = section.querySelector('[data-listed-dots]');
    var cards = track.querySelectorAll('.listed-card');
    if (!cards.length) return;

    function cardStep() {
      var card = track.querySelector('.listed-card');
      return card ? card.getBoundingClientRect().width + 18 : track.clientWidth;
    }
    function visibleCount() {
      return Math.max(1, Math.round(track.clientWidth / cardStep()));
    }
    function pageCount() {
      return Math.max(1, Math.ceil(cards.length / visibleCount()));
    }
    function currentPage() {
      var step = cardStep() * visibleCount();
      return step ? Math.round(track.scrollLeft / step) : 0;
    }

    function updateDots() {
      if (!dotsWrap) return;
      var dots = dotsWrap.querySelectorAll('.dot');
      var page = currentPage();
      dots.forEach(function (d, i) { d.classList.toggle('active', i === page); });
    }

    function buildDots() {
      if (!dotsWrap) return;
      var pages = pageCount();
      dotsWrap.innerHTML = '';
      if (pages < 2) return;
      for (var i = 0; i < pages; i++) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'dot';
        dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        (function (idx) {
          dot.addEventListener('click', function () {
            track.scrollTo({ left: idx * cardStep() * visibleCount(), behavior: 'smooth' });
          });
        })(i);
        dotsWrap.appendChild(dot);
      }
      updateDots();
    }

    function updateArrows() {
      var maxScroll = track.scrollWidth - track.clientWidth - 2;
      if (prevBtn) prevBtn.disabled = track.scrollLeft <= 0;
      if (nextBtn) nextBtn.disabled = track.scrollLeft >= maxScroll;
      updateDots();
    }

    if (prevBtn) prevBtn.addEventListener('click', function () {
      track.scrollBy({ left: -cardStep() * visibleCount(), behavior: 'smooth' });
    });
    if (nextBtn) nextBtn.addEventListener('click', function () {
      track.scrollBy({ left: cardStep() * visibleCount(), behavior: 'smooth' });
    });
    track.addEventListener('scroll', updateArrows);
    window.addEventListener('resize', function () { buildDots(); updateArrows(); });
    buildDots();
    updateArrows();
  });
</script>

</body>
</html>
