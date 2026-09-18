<?php
require_once 'config.php';
require_once 'includes/functions.php';

$comment_id = trim($_GET['id'] ?? '');
$news_id    = trim($_GET['news_id'] ?? '');
$token      = get_visitor_token();

if ($comment_id !== '') {
    // deleteRule only allows this to succeed if $token matches the token
    // the comment was originally created with (or an admin session) — so
    // a visitor can only ever delete their own comment, enforced by
    // PocketBase itself, not just by hiding the button.
    PocketBase::request('DELETE', '/api/collections/news_comments/records/' . rawurlencode($comment_id)
        . '?token=' . rawurlencode($token));
}

header('Location: news-article.php?id=' . rawurlencode($news_id) . '#comments');
exit;
