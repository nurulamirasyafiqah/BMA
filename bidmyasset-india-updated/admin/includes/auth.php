<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['pb_token'])) {
    header('Location: login.php');
    exit;
}

/** The logged-in admin's PocketBase auth token — pass this to every PocketBase call. */
$pb_token = $_SESSION['pb_token'];

/** True if the logged-in admin is a superuser (can manage other admin accounts). */
$is_superuser = ($_SESSION['admin_role'] ?? 'admin') === 'superuser';

/** Call at the top of a page that only superusers may access. */
if (!function_exists('require_superuser')) {
    function require_superuser(): void {
        if (($_SESSION['admin_role'] ?? 'admin') !== 'superuser') {
            http_response_code(403);
            include __DIR__ . '/admin_header.php';
            echo '<h1>Access denied</h1><div class="sub">Only a superuser can access this page. Ask a superuser on your team for help.</div>';
            include __DIR__ . '/admin_footer.php';
            exit;
        }
    }
}

/** Truncate a string safely, with or without the mbstring extension. */
if (!function_exists('admin_truncate')) {
    function admin_truncate(string $str, int $len): string {
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($str, 0, $len, '…');
        }
        return strlen($str) > $len ? substr($str, 0, $len) . '…' : $str;
    }
}
