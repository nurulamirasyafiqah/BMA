<?php
$admin_title = $admin_title ?? 'Admin Panel';
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$is_superuser = $is_superuser ?? (($_SESSION['admin_role'] ?? 'admin') === 'superuser');

function nav_active($page, $current) {
    return $page === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($admin_title) ?> — BidMyAsset Admin</title>
<style>
  :root{ --navy:#1b2a4a; --grey-bg:#f4f4f4; --grey-line:#e2e2e2; --muted:#6b6f76; --accent:#c94f3d; }
  *{box-sizing:border-box;}
  body{margin:0;font-family:Arial, Helvetica, sans-serif;background:var(--grey-bg);color:#222;}
  a{color:inherit;text-decoration:none;}
  .admin-shell{display:flex;min-height:100vh;}

  /* ---------------- Topbar (mobile only) ---------------- */
  .topbar{display:none;align-items:center;gap:14px;background:var(--navy);color:#fff;padding:12px 16px;position:sticky;top:0;z-index:40;}
  .hamburger{background:none;border:none;cursor:pointer;padding:6px;display:flex;flex-direction:column;gap:4px;justify-content:center;align-items:center;width:34px;height:34px;border-radius:4px;}
  .hamburger:hover{background:rgba(255,255,255,.1);}
  .hamburger span{display:block;width:20px;height:2px;background:#fff;border-radius:2px;}
  .topbar .brand{font-weight:800;font-size:15px;}

  /* ---------------- Sidebar ---------------- */
  .sidebar{width:220px;background:var(--navy);color:#fff;padding:24px 0;flex-shrink:0;}
  .sidebar .brand{padding:0 20px 20px 20px;font-weight:800;font-size:16px;border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:14px;}
  .sidebar a{display:block;padding:12px 20px;font-size:14px;color:#dfe4ee;}
  .sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,.08);color:#fff;}
  .sidebar .who{padding:4px 20px 16px 20px;font-size:12px;color:#aab2c4;border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:6px;}
  .sidebar .who .role-badge{display:inline-block;margin-top:4px;padding:2px 8px;border-radius:10px;font-size:10.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;background:rgba(255,255,255,.12);color:#fff;}
  .sidebar .logout{margin-top:20px;border-top:1px solid rgba(255,255,255,.15);}
  .sidebar-overlay{display:none;}

  .main{flex:1;padding:32px;max-width:1100px;}
  .main h1{font-size:22px;margin:0 0 6px 0;}
  .main .sub{color:var(--muted);font-size:13.5px;margin-bottom:26px;}
  table{width:100%;border-collapse:collapse;background:#fff;font-size:13.5px;}
  th, td{padding:10px 12px;border-bottom:1px solid var(--grey-line);text-align:left;vertical-align:top;}
  th{background:#fafafa;font-weight:700;}
  .card{background:#fff;border:1px solid var(--grey-line);padding:24px;margin-bottom:24px;border-radius:4px;}
  .field{margin-bottom:16px;}
  .field label{display:block;font-size:13px;margin-bottom:6px;font-weight:600;}
  .field input, .field textarea, .field select{width:100%;padding:9px;border:1px solid var(--grey-line);font-size:13.5px;font-family:inherit;border-radius:2px;}
  .field .hint{font-size:11.5px;color:var(--muted);margin-top:4px;}
  .btn{display:inline-block;background:#111;color:#fff;border:none;padding:10px 22px;font-size:13.5px;cursor:pointer;border-radius:2px;}
  .btn-danger{background:var(--accent);}
  .btn-sm{padding:6px 12px;font-size:12.5px;}
  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:18px;margin-bottom:30px;}
  .stat-box{background:#fff;border:1px solid var(--grey-line);padding:20px;border-radius:4px;}
  .stat-box .num{font-size:28px;font-weight:800;color:var(--navy);}
  .stat-box .label{font-size:12.5px;color:var(--muted);}
  .msg{padding:10px 14px;background:#e6f4ea;border:1px solid #b7e0c3;color:#1e6b34;font-size:13.5px;margin-bottom:18px;border-radius:3px;}
  .msg.error{background:#fbe9e7;border-color:#f3c2ba;color:#a3352a;}
  .country-tabs a{display:inline-block;padding:8px 16px;background:#fff;border:1px solid var(--grey-line);margin-right:8px;border-radius:3px;font-size:13px;}
  .country-tabs a.active{background:var(--navy);color:#fff;border-color:var(--navy);}
  .country-badge{display:inline-block;padding:8px 16px;background:var(--navy);color:#fff;border-radius:3px;font-size:13px;font-weight:600;}
  .role-pill{display:inline-block;padding:2px 9px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;}
  .role-pill.superuser{background:#fde8d6;color:#a3591c;}
  .role-pill.admin{background:#e3ecf7;color:#1b2a4a;}

  /* ---------------- Mobile ---------------- */
  @media (max-width: 860px) {
    .topbar{display:flex;}
    .admin-shell{position:relative;}
    .sidebar{
      position:fixed; top:0; left:0; height:100%; z-index:60;
      transform:translateX(-100%);
      transition:transform .2s ease;
      overflow-y:auto;
    }
    .sidebar.open{transform:translateX(0);}
    .sidebar-overlay{
      display:none;
      position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:50;
    }
    .sidebar-overlay.open{display:block;}
    .main{padding:20px;max-width:100%;}
    .grid-2, .section-layout{grid-template-columns:1fr !important;}
  }
</style>
</head>
<body>

<div class="topbar">
  <button class="hamburger" id="sidebarToggle" aria-label="Open menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
  <div class="brand">BidMyAsset Admin</div>
</div>

<div class="admin-shell">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <div class="sidebar" id="adminSidebar">
    <div class="brand">BidMyAsset Admin</div>
    <?php if (isset($_SESSION['admin_username'])): ?>
    <div class="who">
      Logged in as<br><strong style="color:#fff;"><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
      <div class="role-badge"><?= htmlspecialchars(strtoupper($_SESSION['admin_role'] ?? 'admin')) ?></div>
    </div>
    <?php endif; ?>
    <a href="dashboard.php" class="<?= nav_active('dashboard.php', $current_page) ?>">Dashboard</a>
    <a href="settings.php" class="<?= nav_active('settings.php', $current_page) ?>">Site Settings</a>
    <a href="services.php" class="<?= nav_active('services.php', $current_page) ?>">Services</a>
    <a href="machinery.php" class="<?= nav_active('machinery.php', $current_page) ?>">Used Machinery</a>
    <a href="machinery-listings.php" class="<?= nav_active('machinery-listings.php', $current_page) ?>">Machinery Listings</a>
    <a href="trusted-logos.php" class="<?= nav_active('trusted-logos.php', $current_page) ?>">Trusted By Logos</a>
    <a href="news.php" class="<?= nav_active('news.php', $current_page) ?>">News</a>
    <a href="submissions.php" class="<?= nav_active('submissions.php', $current_page) ?>">Contact Submissions</a>
    <?php if ($is_superuser): ?>
    <a href="admins.php" class="<?= nav_active('admins.php', $current_page) ?>">Manage Admins</a>
    <?php endif; ?>
    <div class="logout"><a href="logout.php">Log out</a></div>
  </div>
  <div class="main">

<script>
  (function () {
    var toggle  = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar || !overlay) return;

    function openMenu() {
      sidebar.classList.add('open');
      overlay.classList.add('open');
      toggle.setAttribute('aria-expanded', 'true');
    }
    function closeMenu() {
      sidebar.classList.remove('open');
      overlay.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    }
    toggle.addEventListener('click', function () {
      sidebar.classList.contains('open') ? closeMenu() : openMenu();
    });
    overlay.addEventListener('click', closeMenu);
    // Close the menu automatically once a nav link is tapped.
    sidebar.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', closeMenu);
    });
  })();
</script>
