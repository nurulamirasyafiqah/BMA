<?php
require_once 'config.php';
require_once 'includes/functions.php';

$country = get_country();
$news_id = trim($_POST['news_id'] ?? '');
$token   = get_visitor_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $news_id !== '' && get_news_item($news_id, $country)) {
    $existing = PocketBase::list('news_likes', [
        'filter'  => "news = '" . PocketBase::escape($news_id) . "' && token = '" . PocketBase::escape($token) . "'",
        'perPage' => 1,
    ]);

    if ($existing) {
        // Already liked -> unlike. deleteRule requires this same token as
        // a query param, since there's no admin session on the public site.
        PocketBase::request('DELETE', '/api/collections/news_likes/records/' . rawurlencode($existing[0]['id'])
            . '?token=' . rawurlencode($token));
    } else {
        PocketBase::create('news_likes', [
            'country' => $country,
            'news'    => $news_id,
            'token'   => $token,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }
}

header('Location: news-article.php?id=' . rawurlencode($news_id) . '#engagement');
exit;
