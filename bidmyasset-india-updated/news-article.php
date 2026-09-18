<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$news_id = trim($_GET['id'] ?? '');
$item    = $news_id !== '' ? get_news_item($news_id, $country) : null;

if (!$item) {
    http_response_code(404);
    $page_title = 'Not Found — ' . $settings['site_title'];
    include 'includes/header.php';
    ?>
    <section class="placeholder-page">
      <div class="container">
        <h1>Article Not Found</h1>
        <p>This news article doesn't exist or isn't available for this site.</p>
        <a class="btn" href="news.php">Back to News</a>
      </div>
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

$page_title = h($item['title']) . ' — ' . $settings['site_title'];
$image_url  = pb_image($item, 'image', 'news', '');

$visitor_token = get_visitor_token();
$like_count    = get_news_like_count($news_id);
$has_liked     = visitor_has_liked($news_id, $visitor_token);
$share_counts  = get_news_share_counts($news_id);
$comments      = get_news_comments($news_id);
$adjacent      = get_adjacent_news($news_id, $country);

include 'includes/header.php';

/** Deterministic avatar colour for a name, so the same person always gets the same colour. */
function avatar_color(string $name): string {
    $palette = ['#7b2d5e', '#2d3a7b', '#8e3f2d', '#2d7b5e', '#5e2d7b', '#7b5e2d'];
    $sum = 0;
    foreach (str_split($name) as $ch) { $sum += ord($ch); }
    return $palette[$sum % count($palette)];
}

/** Total number of comments including every level of replies. */
function count_comments_recursive(array $rows, array $replies_map): int {
    $total = count($rows);
    foreach ($rows as $row) {
        if (!empty($replies_map[$row['id']])) {
            $total += count_comments_recursive($replies_map[$row['id']], $replies_map);
        }
    }
    return $total;
}

/** Render one comment, then recursively render every reply underneath it (any depth). */
function render_comment(array $c, array $replies_map, string $news_id, string $visitor_token): void {
    $mine = ($c['token'] ?? '') === $visitor_token;
    $initial = mb_strtoupper(mb_substr($c['name'], 0, 1));
    ?>
    <div class="comment" id="comment-<?= h($c['id']) ?>">
      <div class="comment-avatar" style="background:<?= h(avatar_color($c['name'])) ?>;"><?= h($initial) ?></div>
      <div class="comment-body-wrap">
        <div class="comment-head">
          <span class="comment-name"><?= h($c['name']) ?></span>
          <span class="comment-date"><?= h(pb_date($c['created'] ?? null, 'd/m/Y')) ?></span>
        </div>
        <p class="comment-body"><?= nl2br(h($c['content'])) ?></p>
        <div class="comment-actions">
          <button type="button" class="comment-reply-toggle" data-target="reply-form-<?= h($c['id']) ?>">Reply</button>
          <?php if ($mine): ?>
            <a class="comment-delete" href="news-comment-delete.php?id=<?= h($c['id']) ?>&news_id=<?= h($news_id) ?>"
               onclick="return confirm('Delete your comment?');">Delete</a>
          <?php endif; ?>
        </div>

        <form class="reply-form" id="reply-form-<?= h($c['id']) ?>" method="post" action="news-comment.php" hidden>
          <input type="hidden" name="news_id" value="<?= h($news_id) ?>">
          <input type="hidden" name="parent_id" value="<?= h($c['id']) ?>">
          <input type="text" name="name" maxlength="100" placeholder="Your name" required>
          <textarea name="content" maxlength="2000" rows="2" placeholder="Write a reply…" required></textarea>
          <button type="submit" class="btn btn-sm">Post Reply</button>
        </form>

        <?php if (!empty($replies_map[$c['id']])): ?>
          <div class="comment-replies">
            <?php foreach ($replies_map[$c['id']] as $reply): render_comment($reply, $replies_map, $news_id, $visitor_token); endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
}
?>

<section class="category-page">
  <div class="container">
    <a class="back-link" href="news.php">&larr; Back to News</a>
    <h1><?= h($item['title']) ?></h1>

    <div class="article-meta">
      <?= h(date('d/m/Y', strtotime($item['news_date']))) ?>
      <?php if (!empty($item['author'])): ?>
        &nbsp;&middot;&nbsp; by <?= h($item['author']) ?>
      <?php endif; ?>
    </div>

    <?php if ($image_url): ?>
      <img class="article-image" src="<?= h($image_url) ?>" alt="<?= h($item['title']) ?>">
    <?php endif; ?>

    <?php if (!empty($item['content'])): ?>
      <div class="article-content"><?= $item['content'] ?></div>
    <?php else: ?>
      <p>More detail about this article will go here — an admin can add this from News in the admin panel.</p>
    <?php endif; ?>

    <div id="engagement" class="engagement-bar">
      <form method="post" action="news-like.php" class="like-form">
        <input type="hidden" name="news_id" value="<?= h($news_id) ?>">
        <button type="submit" class="like-btn<?= $has_liked ? ' liked' : '' ?>" aria-pressed="<?= $has_liked ? 'true' : 'false' ?>">
          <svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-10.2-9.2C.3 8.9 1.4 5 5 4.1c2-.5 4 .3 5.2 2.1 1.2-1.8 3.2-2.6 5.2-2.1 3.6.9 4.7 4.8 3.2 7.7C19.5 16.4 12 21 12 21Z"/></svg>
          <span><?= $has_liked ? 'Liked' : 'Like' ?></span>
        </button>
        <span class="like-count"><?= (int)$like_count ?> like<?= $like_count === 1 ? '' : 's' ?></span>
      </form>

      <div class="share-row">
        <span class="share-label">Share:</span>
        <a class="share-btn share-x" href="news-share.php?news_id=<?= h($news_id) ?>&platform=x" target="_blank" rel="noopener">
          X <span class="share-count"><?= (int)$share_counts['by_platform']['x'] ?></span>
        </a>
        <a class="share-btn share-facebook" href="news-share.php?news_id=<?= h($news_id) ?>&platform=facebook" target="_blank" rel="noopener">
          Facebook <span class="share-count"><?= (int)$share_counts['by_platform']['facebook'] ?></span>
        </a>
        <a class="share-btn share-whatsapp" href="news-share.php?news_id=<?= h($news_id) ?>&platform=whatsapp" target="_blank" rel="noopener">
          WhatsApp <span class="share-count"><?= (int)$share_counts['by_platform']['whatsapp'] ?></span>
        </a>
        <span class="share-total"><?= (int)$share_counts['total'] ?> share<?= $share_counts['total'] === 1 ? '' : 's' ?> total</span>
      </div>
    </div>

    <div id="comments" class="comments-section">
      <h2>Comments (<?= count_comments_recursive($comments['top'], $comments['replies']) ?>)</h2>

      <form class="comment-form" method="post" action="news-comment.php">
        <input type="hidden" name="news_id" value="<?= h($news_id) ?>">
        <div class="field"><input type="text" name="name" maxlength="100" placeholder="Your name" required></div>
        <div class="field comment-textarea-wrap">
          <textarea name="content" maxlength="2000" rows="4" placeholder="Write a comment…" required></textarea>
          <button type="submit" class="send-btn" aria-label="Post comment">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 11.5 21 3l-6.5 18-3.2-7.3L3 11.5Z"/></svg>
          </button>
        </div>
      </form>

      <div class="comment-list">
        <?php if (!$comments['top']): ?>
          <p class="sub">No comments yet — be the first to comment.</p>
        <?php endif; ?>
        <?php foreach ($comments['top'] as $c): render_comment($c, $comments['replies'], $news_id, $visitor_token); endforeach; ?>
      </div>
    </div>

    <?php if ($adjacent['prev'] || $adjacent['next']): ?>
      <nav class="article-nav" aria-label="Article navigation">
        <?php if ($adjacent['prev']): ?>
          <a class="article-nav-link article-nav-prev" href="news-article.php?id=<?= h($adjacent['prev']['id']) ?>">
            <span class="article-nav-label">&larr; Previous</span>
            <span class="article-nav-title"><?= h($adjacent['prev']['title']) ?></span>
          </a>
        <?php endif; ?>
        <?php if ($adjacent['next']): ?>
          <a class="article-nav-link article-nav-next" href="news-article.php?id=<?= h($adjacent['next']['id']) ?>">
            <span class="article-nav-label">Next &rarr;</span>
            <span class="article-nav-title"><?= h($adjacent['next']['title']) ?></span>
          </a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

  </div>
</section>

<script>
  document.querySelectorAll('.comment-reply-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById(btn.getAttribute('data-target'));
      if (form) form.hidden = !form.hidden;
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
