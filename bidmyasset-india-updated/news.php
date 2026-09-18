<?php
require_once 'config.php';
require_once 'includes/functions.php';

$base_path = '';
$country   = get_country();
$settings  = get_settings($country);

if (!$settings) {
    die('Site settings not found for this country. Please check the database.');
}

$page   = max(1, (int)($_GET['page'] ?? 1));
$result = get_news_list($country, $page, 9);

// If someone requests a page past the end, just show the last real page.
if ($page > $result['totalPages']) {
    $page   = $result['totalPages'];
    $result = get_news_list($country, $page, 9);
}

$page_title = 'News & Articles — ' . $settings['site_title'];
include 'includes/header.php';
?>

<section class="category-page news-page">
  <div class="container">
    <a class="back-link" href="index.php">&larr; Back to Home</a>
    <h1>News &amp; Articles</h1>

    <?php if ($result['items']): ?>
      <div class="news-list">
        <?php foreach ($result['items'] as $item): ?>
          <article class="news-list-item">
            <h3><a href="news-article.php?id=<?= h($item['id']) ?>"><?= h($item['title']) ?></a></h3>
            <?php $excerpt = news_excerpt($item['content'] ?? ''); ?>
            <?php if ($excerpt): ?>
              <p class="excerpt"><?= h($excerpt) ?></p>
            <?php endif; ?>
            <a class="keep-reading" href="news-article.php?id=<?= h($item['id']) ?>">Keep reading</a>
            <div class="date"><?= h(date('d/m/Y', strtotime($item['news_date']))) ?></div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($result['totalPages'] > 1): ?>
        <nav class="news-pagination">
          <?php if ($page > 1): ?>
            <a class="btn btn-outline" href="news.php?page=<?= $page - 1 ?>">&larr; Newer</a>
          <?php endif; ?>
          <span class="page-indicator">Page <?= $page ?> of <?= $result['totalPages'] ?></span>
          <?php if ($page < $result['totalPages']): ?>
            <a class="btn btn-outline" href="news.php?page=<?= $page + 1 ?>">Older &rarr;</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <p class="no-listings">No news articles yet — check back soon.</p>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
