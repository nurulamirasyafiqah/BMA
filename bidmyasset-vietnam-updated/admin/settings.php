<?php
require_once 'includes/auth.php';
require_once '../config.php';
require_once '../includes/functions.php';

$admin_title = 'Site Settings';

// This deployment only ever manages its own country's content —
// see SITE_COUNTRY in config.php. There is no way to switch to or edit
// the other country's data from here, because that data doesn't even
// exist in this deployment's database.
$country = SITE_COUNTRY;

$message = '';
$message_is_error = false;
$fields = [
    'site_title', 'hero_title', 'hero_subtitle', 'hero_text', 'services_intro',
    'discover_title', 'discover_description',
    'about_title', 'about_content',
    'office_name', 'address_line1', 'address_line2', 'address_line3',
    'email', 'phone', 'live_auction_url', 'map_embed_url',
    'facebook_url', 'linkedin_url', 'tiktok_url', 'youtube_url',
    'founder_name', 'founder_quote',
    'privacy_policy_content', 'privacy_policy_updated', 'terms_content', 'terms_updated'
];

// Every image "slot" an admin can add/replace/remove a picture for.
$image_slot_keys = [
    'logo_image', 'hero_image', 'process_image', 'auction_image',
    'founder_avatar', 'factory_image_1', 'factory_image_2', 'about_image', 'discover_image',
];

// Look up the record id for this country first (PocketBase updates are by id).
$existing = PocketBase::list('site_settings', [
    'filter'  => "country = '" . PocketBase::escape($country) . "'",
    'perPage' => 1,
], $pb_token);
$record_id = $existing[0]['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $record_id) {
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = trim($_POST[$f] ?? '');
    }

    // Image handling: a new uploaded file always wins; otherwise, if the
    // admin ticked "Remove", clear the field; otherwise leave it untouched
    // (key simply not sent, so PocketBase keeps the existing file).
    $files_to_upload = [];
    foreach ($image_slot_keys as $slot) {
        $hasNewFile = isset($_FILES[$slot]) && ($_FILES[$slot]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
        if ($hasNewFile) {
            $files_to_upload[$slot] = $_FILES[$slot];
        } elseif (!empty($_POST['remove_' . $slot])) {
            $values[$slot] = ''; // clears the existing file in PocketBase
        }
    }

    $updated = PocketBase::saveWithFiles('site_settings', $record_id, $values, $files_to_upload, $pb_token);
    if ($updated) {
        $message = 'Settings updated for ' . ucfirst($country) . '. The public website reflects this immediately — no rebuild or restart needed.';
        $existing[0] = $updated;
    } else {
        $message = 'Something went wrong saving — ' . (PocketBase::$lastError ?? 'please try again.');
        $message_is_error = true;
    }
}

$settings = $existing[0] ?? array_fill_keys($fields, '');

include 'includes/admin_header.php';

/**
 * Render one image-slot control: current preview + upload + remove checkbox.
 * Used inline, right next to the text fields for the same section, so it's
 * obvious at a glance which picture belongs to which piece of content.
 */
function render_image_slot(string $slot, string $label, string $hint, array $settings): void {
    $url = PocketBase::fileUrl($settings, $slot, 'site_settings');
    ?>
    <div class="image-slot">
      <label class="image-slot-label"><?= htmlspecialchars($label) ?></label>
      <div class="image-slot-hint"><?= htmlspecialchars($hint) ?></div>
      <?php if ($url): ?>
        <div class="image-preview"><img src="<?= htmlspecialchars($url) ?>" alt="<?= htmlspecialchars($label) ?>"></div>
        <label class="checkbox-line"><input type="checkbox" name="remove_<?= $slot ?>" value="1"> Remove this image</label>
      <?php else: ?>
        <div class="image-preview empty">No image set — default placeholder is shown on the site</div>
      <?php endif; ?>
      <input type="file" name="<?= $slot ?>" accept="image/*">
    </div>
    <?php
}
?>
<style>
  .section-block{border:1px solid var(--grey-line);border-radius:6px;padding:20px;margin-bottom:22px;background:#fdfdfd;}
  .section-block h3{margin:0 0 4px 0;font-size:15px;color:var(--navy);}
  .section-block .section-desc{font-size:12px;color:var(--muted);margin-bottom:16px;}
  .section-layout{display:grid;grid-template-columns:1.3fr 1fr;gap:24px;align-items:start;}
  @media (max-width:820px){.section-layout{grid-template-columns:1fr;}}
  .image-slot{border:1px dashed var(--grey-line);padding:14px;border-radius:4px;background:#fafafa;}
  .image-slot-label{display:block;font-size:13px;font-weight:600;margin-bottom:2px;}
  .image-slot-hint{font-size:11.5px;color:var(--muted);margin-bottom:8px;}
  .image-preview{margin:6px 0;}
  .image-preview img{max-width:100%;max-height:130px;object-fit:contain;border:1px solid var(--grey-line);background:#fff;padding:4px;display:block;}
  .image-preview.empty{font-size:12px;color:var(--muted);font-style:italic;background:#fff;border:1px dashed var(--grey-line);padding:14px;text-align:center;border-radius:3px;}
  .checkbox-line{display:flex;align-items:center;gap:6px;font-weight:400;font-size:12.5px;margin:6px 0;}
</style>

<h1>Site Settings</h1>
<div class="sub">Each section below groups its text together with the exact image it controls, so it's clear what you're editing. Images keep their position on the page — you can only add, replace, or remove the picture, never move it.</div>

<div class="country-tabs"><span class="country-badge">Managing: <?= ucfirst($country) ?></span></div>
<br>

<?php if ($message): ?><div class="msg<?= $message_is_error ? ' error' : '' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">

  <!-- BRANDING -->
  <div class="section-block">
    <h3>Branding</h3>
    <div class="section-desc">Site name and the logo used in the header &amp; footer.</div>
    <div class="section-layout">
      <div>
        <div class="field"><label>Site Title</label><input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>"></div>
      </div>
      <?php render_image_slot('logo_image', 'Site Logo', 'Shown in the header and footer on every page.', $settings); ?>
    </div>
  </div>

  <!-- HERO -->
  <div class="section-block">
    <h3>Homepage Hero</h3>
    <div class="section-desc">The big banner at the very top of the homepage.</div>
    <div class="section-layout">
      <div>
        <div class="field"><label>Hero Title</label><input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>"></div>
        <div class="field"><label>Hero Subtitle</label><input type="text" name="hero_subtitle" value="<?= htmlspecialchars($settings['hero_subtitle'] ?? '') ?>"></div>
        <div class="field"><label>Hero Text</label><textarea name="hero_text" rows="4"><?= htmlspecialchars($settings['hero_text'] ?? '') ?></textarea></div>
      </div>
      <?php render_image_slot('hero_image', 'Hero Image', 'The photo shown beside the hero text.', $settings); ?>
    </div>
  </div>

  <!-- SERVICES INTRO -->
  <div class="section-block">
    <h3>Services Section</h3>
    <div class="section-desc">Intro text above the service cards. (The service cards themselves are managed on the separate Services page.)</div>
    <div class="field"><label>Services Section Intro</label><textarea name="services_intro" rows="3"><?= htmlspecialchars($settings['services_intro'] ?? '') ?></textarea></div>
  </div>

  <!-- PROCESS -->
  <div class="section-block">
    <h3>Process Section</h3>
    <div class="section-desc">The diagram shown above the "Discover More" button.</div>
    <div class="section-layout">
      <div class="sub">No text field here — just the diagram image.</div>
      <?php render_image_slot('process_image', 'Process Diagram', 'Shown above the "Discover More" button.', $settings); ?>
    </div>
  </div>

  <!-- DISCOVER MORE PAGE -->
  <div class="section-block">
    <h3>"Discover More" Page</h3>
    <div class="section-desc">Content for the page visitors land on after clicking the "Discover More" button.</div>
    <div class="section-layout">
      <div>
        <div class="field"><label>Page Heading</label><input type="text" name="discover_title" placeholder="e.g. How BidMyAsset Works" value="<?= htmlspecialchars($settings['discover_title'] ?? '') ?>"></div>
      </div>
      <?php render_image_slot('discover_image', 'Header Photo', 'Shown at the top of the Discover More page.', $settings); ?>
    </div>
    <div class="field">
      <label>Page Description</label>
      <textarea id="discover-description-editor" name="discover_description" rows="10"><?= htmlspecialchars($settings['discover_description'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- ABOUT US PAGE -->
  <div class="section-block">
    <h3>"About Us" Page</h3>
    <div class="section-desc">Content for the About Us page, linked from the footer.</div>
    <div class="section-layout">
      <div>
        <div class="field"><label>Page Heading</label><input type="text" name="about_title" placeholder="e.g. About Us" value="<?= htmlspecialchars($settings['about_title'] ?? '') ?>"></div>
      </div>
      <?php render_image_slot('about_image', 'Header Photo', 'Shown at the top of the About Us page.', $settings); ?>
    </div>
    <div class="field">
      <label>Page Content</label>
      <textarea id="about-content-editor" name="about_content" rows="10"><?= htmlspecialchars($settings['about_content'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- AUCTION -->
  <div class="section-block">
    <h3>Auction Section</h3>
    <div class="section-desc">The "Leading Machinery Auction Platform" block. The photo beside it is now a clickable carousel managed on the <a href="auction-images.php">Auction Images</a> page — the single photo below is only used as a fallback if that gallery is empty.</div>
    <div class="section-layout">
      <div>
        <div class="field"><label>Live Auction URL</label><input type="text" name="live_auction_url" value="<?= htmlspecialchars($settings['live_auction_url'] ?? '') ?>"></div>
      </div>
      <?php render_image_slot('auction_image', 'Auction Image (fallback)', 'Only shown if the Auction Images gallery has no photos yet.', $settings); ?>
    </div>
  </div>

  <!-- FOUNDER QUOTE -->
  <div class="section-block">
    <h3>Founder Quote</h3>
    <div class="section-desc">The quote block with the founder's photo, name, and words.</div>
    <div class="section-layout">
      <div>
        <div class="field"><label>Founder Name</label><input type="text" name="founder_name" value="<?= htmlspecialchars($settings['founder_name'] ?? '') ?>"></div>
        <div class="field"><label>Quote</label><textarea name="founder_quote" rows="3"><?= htmlspecialchars($settings['founder_quote'] ?? '') ?></textarea></div>
      </div>
      <?php render_image_slot('founder_avatar', 'Founder Portrait', 'The small round photo above the founder\'s name.', $settings); ?>
    </div>
  </div>

  <!-- BANNER STRIP -->
  <div class="section-block">
    <h3>Banner Strip Photos</h3>
    <div class="section-desc">The two full-width photos between "Trusted By" and "Used Machinery Listed".</div>
    <div class="section-layout">
      <?php render_image_slot('factory_image_1', 'Banner Photo — Left', 'Left half of the strip.', $settings); ?>
      <?php render_image_slot('factory_image_2', 'Banner Photo — Right', 'Right half of the strip.', $settings); ?>
    </div>
  </div>

  <!-- OFFICE / CONTACT -->
  <div class="section-block">
    <h3>Office / Contact</h3>
    <div class="section-desc">Shown in the Contact Us section (no image here).</div>
    <div class="grid-2">
      <div class="field"><label>Office Name</label><input type="text" name="office_name" value="<?= htmlspecialchars($settings['office_name'] ?? '') ?>"></div>
      <div class="field"><label>Email</label><input type="text" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>"></div>
      <div class="field"><label>Address Line 1</label><input type="text" name="address_line1" value="<?= htmlspecialchars($settings['address_line1'] ?? '') ?>"></div>
      <div class="field"><label>Address Line 2</label><input type="text" name="address_line2" value="<?= htmlspecialchars($settings['address_line2'] ?? '') ?>"></div>
      <div class="field"><label>Address Line 3</label><input type="text" name="address_line3" value="<?= htmlspecialchars($settings['address_line3'] ?? '') ?>"></div>
      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>"></div>
    </div>
  </div>

  <!-- MAP -->
  <div class="section-block">
    <h3>Map / Location</h3>
    <div class="section-desc">
      Paste a Google Maps embed link and the map on the Contact Us section will update automatically — no code changes needed.
      To get one: open <a href="https://www.google.com/maps" target="_blank" rel="noopener">Google Maps</a>, search your address, click <strong>Share → Embed a map</strong>, and copy just the URL inside the <code>src="..."</code> part of the code Google gives you (not the whole &lt;iframe&gt; tag).
    </div>
    <div class="field">
      <label>Map Embed URL</label>
      <input type="url" name="map_embed_url" placeholder="https://www.google.com/maps/embed?pb=..." value="<?= htmlspecialchars($settings['map_embed_url'] ?? '') ?>">
      <div class="hint">Leave blank to show a simple text placeholder instead of a map.</div>
    </div>
    <?php if (!empty($settings['map_embed_url'])): ?>
      <div class="field">
        <label>Preview</label>
        <iframe src="<?= htmlspecialchars($settings['map_embed_url']) ?>" width="100%" height="260" style="border:0;border-radius:4px;" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
    <?php endif; ?>
  </div>

  <!-- PRIVACY POLICY & TERMS -->
  <div class="section-block">
    <h3>Privacy Policy &amp; Terms</h3>
    <div class="section-desc">Shown as a popup when a visitor clicks "Privacy Policy" or "Terms and Conditions" in the footer.</div>
    <div class="field">
      <label>Privacy Policy — Last Updated</label>
      <input type="date" name="privacy_policy_updated" value="<?= htmlspecialchars($settings['privacy_policy_updated'] ?? '') ?>">
    </div>
    <div class="field">
      <label>Privacy Policy — Content</label>
      <textarea id="privacy-policy-editor" name="privacy_policy_content" rows="10"><?= htmlspecialchars($settings['privacy_policy_content'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label>Terms and Conditions — Last Updated</label>
      <input type="date" name="terms_updated" value="<?= htmlspecialchars($settings['terms_updated'] ?? '') ?>">
    </div>
    <div class="field">
      <label>Terms and Conditions — Content</label>
      <textarea id="terms-editor" name="terms_content" rows="10"><?= htmlspecialchars($settings['terms_content'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- SOCIAL LINKS -->
  <div class="section-block">
    <h3>Social Links</h3>
    <div class="section-desc">Used by the Follow Us icons in the footer (and the contact section).</div>
    <div class="grid-2">
      <div class="field"><label>Facebook URL</label><input type="text" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>"></div>
      <div class="field"><label>LinkedIn URL</label><input type="text" name="linkedin_url" value="<?= htmlspecialchars($settings['linkedin_url'] ?? '') ?>"></div>
      <div class="field"><label>TikTok URL</label><input type="text" name="tiktok_url" value="<?= htmlspecialchars($settings['tiktok_url'] ?? '') ?>"></div>
      <div class="field"><label>YouTube URL</label><input type="text" name="youtube_url" value="<?= htmlspecialchars($settings['youtube_url'] ?? '') ?>"></div>
    </div>
  </div>

  <button type="submit" class="btn">Save Changes</button>
</form>

<!-- Rich text editors for Discover More / About Us / Privacy / Terms page
     content — same self-hosted TinyMCE setup used for News articles, so
     admins get the same paragraphs/lists/tables/bold/italic/links/headings
     toolbar. -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#discover-description-editor, #about-content-editor, #privacy-policy-editor, #terms-editor',
    height: 420,
    menubar: false,
    plugins: 'lists link table image code autolink',
    toolbar: 'undo redo | blocks | bold italic underline | forecolor | ' +
             'bullist numlist | link image table | alignleft aligncenter alignright | code',
    branding: false,
    promotion: false,
    license_key: 'gpl'
  });
</script>

<?php include 'includes/admin_footer.php'; ?>
