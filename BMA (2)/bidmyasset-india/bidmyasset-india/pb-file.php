<?php
/**
 * Streams an uploaded file from PocketBase back to the browser.
 *
 * Why this exists: PB_URL is the address the *web server* uses to reach
 * PocketBase (e.g. the Docker-internal "http://pocketbase:8090"), which is
 * usually NOT reachable from a visitor's browser. If <img> tags pointed at
 * PB_URL directly, every uploaded picture would show as a broken image
 * (this was happening before this proxy was added). This script runs on
 * the web server — which *can* reach PB_URL — and re-serves the bytes from
 * the website's own origin, so the browser never needs to know PB_URL at all.
 *
 * Usage: /pb-file.php?collection=site_settings&id=RECORD_ID&filename=FILE.jpg[&thumb=100x100]
 */
require_once __DIR__ . '/config.php';

// Only the collections that actually have public file fields may be served
// through this proxy — keeps it from being used as an open relay.
$allowed_collections = ['site_settings', 'machinery_items', 'machinery_listings', 'trusted_logos'];

$collection = $_GET['collection'] ?? '';
$id         = $_GET['id'] ?? '';
$filename   = $_GET['filename'] ?? '';
$thumb      = $_GET['thumb'] ?? '';

$valid = in_array($collection, $allowed_collections, true)
    && preg_match('/^[A-Za-z0-9]+$/', $id)
    && preg_match('/^[A-Za-z0-9._-]+$/', $filename)
    && ($thumb === '' || preg_match('/^[0-9]+x[0-9]+[a-z]*$/', $thumb));

if (!$valid) {
    http_response_code(404);
    exit;
}

$url = rtrim(PB_URL, '/') . "/api/files/$collection/$id/" . rawurlencode($filename);
if ($thumb !== '') {
    $url .= '?thumb=' . rawurlencode($thumb);
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_TIMEOUT        => 15,
]);
$response = curl_exec($ch);

if ($response === false) {
    curl_close($ch);
    http_response_code(502);
    exit;
}

$status       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$contentType  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

$body = substr($response, $headerSize);

http_response_code($status);
if ($status === 200) {
    header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
    // PocketBase gives every re-uploaded file a brand-new random filename,
    // so a URL never goes stale — it's always either the current file or a
    // 404. That makes it safe (and good for performance) to cache it hard.
    header('Cache-Control: public, max-age=31536000, immutable');
}
echo $body;
