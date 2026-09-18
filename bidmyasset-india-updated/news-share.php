<?php
require_once 'config.php';
require_once 'includes/functions.php';

$country  = get_country();
$news_id  = trim($_GET['news_id'] ?? '');
$platform = trim($_GET['platform'] ?? '');
$allowed  = ['x', 'facebook', 'whatsapp'];

$redirect = 'news-article.php' . ($news_id !== '' ? '?id=' . rawurlencode($news_id) : '');

if ($news_id !== '' && in_array($platform, $allowed, true)) {
    $item = get_news_item($news_id, $country);
    if ($item) {
        PocketBase::create('news_shares', [
            'country'  => $country,
            'news'     => $news_id,
            'platform' => $platform,
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $page_url = $scheme . ($_SERVER['HTTP_HOST'] ?? '') . '/news-article.php?id=' . rawurlencode($news_id);
        $title    = $item['title'] ?? '';

        switch ($platform) {
            case 'x':
                $redirect = 'https://twitter.com/intent/tweet?url=' . rawurlencode($page_url) . '&text=' . rawurlencode($title);
                break;
            case 'facebook':
                $redirect = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($page_url);
                break;
            case 'whatsapp':
                $redirect = 'https://wa.me/?text=' . rawurlencode($title . ' ' . $page_url);
                break;
        }
    }
}

header('Location: ' . $redirect);
exit;
