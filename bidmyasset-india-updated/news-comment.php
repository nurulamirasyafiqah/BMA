<?php
require_once 'config.php';
require_once 'includes/functions.php';

$country   = get_country();
$news_id   = trim($_POST['news_id'] ?? '');
$name      = trim($_POST['name'] ?? '');
$content   = trim($_POST['content'] ?? '');
$parent_id = trim($_POST['parent_id'] ?? '');
$token     = get_visitor_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $news_id !== '' && $name !== '' && $content !== '' && get_news_item($news_id, $country)) {
    $values = [
        'country' => $country,
        'news'    => $news_id,
        'name'    => mb_substr($name, 0, 100),
        'content' => mb_substr($content, 0, 2000),
        'token'   => $token,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
    ];
    if ($parent_id !== '') {
        $values['parent'] = $parent_id;
    }
    PocketBase::create('news_comments', $values);
}

header('Location: news-article.php?id=' . rawurlencode($news_id) . '#comments');
exit;
