<?php
/** Escape output safely. */
function h($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Validate and return the current country from the query string. */
/**
 * Which country this deployment serves. This used to read `?country=` from
 * the URL so one shared site could switch between India and Vietnam; now
 * that each country is its own fully separate website (own database, own
 * domain), it simply returns whichever country this deployment was
 * configured for — see SITE_COUNTRY in config.php.
 */
function get_country() {
    return SITE_COUNTRY;
}

/** Fetch the site_settings row for a country from PocketBase. */
function get_settings(string $country): ?array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    $items = PocketBase::list('site_settings', [
        'filter'   => $filter,
        'perPage'  => 1,
    ]);
    return $items[0] ?? null;
}

/** Fetch the services list for a country from PocketBase. */
function get_services(string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('services', [
        'filter'  => $filter,
        'sort'    => '+sort_order,+id',
        'perPage' => 100,
    ]);
}

/** Fetch the latest 3 news items for a country from PocketBase (for the homepage). */
function get_news(string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('news', [
        'filter'  => $filter,
        'sort'    => '-news_date,+sort_order',
        'perPage' => 3,
    ]);
}

/**
 * Fetch a page of news items for a country, for the full News & Articles
 * listing page. Returns ['items' => [...], 'page' => int, 'totalPages' => int].
 */
function get_news_list(string $country, int $page = 1, int $perPage = 9): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    $params = [
        'filter'  => $filter,
        'sort'    => '-news_date,+sort_order',
        'perPage' => $perPage,
        'page'    => max(1, $page),
    ];
    $items      = PocketBase::list('news', $params);
    $totalItems = PocketBase::count('news', ['filter' => $filter]);
    $totalPages = $totalItems > 0 ? (int)ceil($totalItems / $perPage) : 1;
    return [
        'items'      => $items,
        'page'       => max(1, $page),
        'perPage'    => $perPage,
        'totalItems' => $totalItems,
        'totalPages' => $totalPages,
    ];
}

/** Plain-text excerpt from a news item's rich-text content, for list/preview cards. */
function news_excerpt(string $html, int $length = 160): string {
    $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length)) . '…';
}

/**
 * Fetch a single news item by id, for its full article page. Returns null
 * if the id doesn't exist or belongs to a different country (so a link
 * can't be used to peek at another country's data).
 */
function get_news_item(string $id, string $country): ?array {
    $item = PocketBase::view('news', $id);
    if (!$item || ($item['country'] ?? '') !== $country) {
        return null;
    }
    return $item;
}

/**
 * Find the news items immediately before/after a given one, in the same
 * order as the News & Articles listing page (news.php) — newest first.
 * Returns ['prev' => older item or null, 'next' => newer item or null].
 */
function get_adjacent_news(string $news_id, string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    $all = PocketBase::list('news', [
        'filter'  => $filter,
        'sort'    => '-news_date,+sort_order',
        'perPage' => 500,
    ]);

    $index = null;
    foreach ($all as $i => $item) {
        if ($item['id'] === $news_id) {
            $index = $i;
            break;
        }
    }
    if ($index === null) {
        return ['prev' => null, 'next' => null];
    }

    return [
        'prev' => $all[$index + 1] ?? null, // older article
        'next' => $index > 0 ? $all[$index - 1] : null, // newer article
    ];
}

/** Fetch the "Used Machinery Listed" items for a country from PocketBase. */
function get_machinery(string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('machinery_items', [
        'filter'  => $filter,
        'sort'    => '+sort_order,+id',
        'perPage' => 100,
    ]);
}

/**
 * Fetch a single machinery category (e.g. "Injection Molding") by id, for
 * its detail page. Returns null if the id doesn't exist or belongs to a
 * different country (so a link can't be used to peek at another country's
 * data).
 */
function get_machinery_category(string $id, string $country): ?array {
    $category = PocketBase::view('machinery_items', $id);
    if (!$category || ($category['country'] ?? '') !== $country) {
        return null;
    }
    return $category;
}

/** Fetch the individual used-machine listings that belong to a category. */
function get_machinery_listings(string $category_id, string $country): array {
    $filter = "category = '" . PocketBase::escape($category_id) . "' && country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('machinery_listings', [
        'filter'  => $filter,
        'sort'    => '+sort_order,+id',
        'perPage' => 200,
    ]);
}

/** Count words in a plain-text (possibly multi-paragraph) description. */
function word_count(string $text): int {
    $text = trim($text);
    if ($text === '') return 0;
    return count(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY));
}

/** First $limit words of a plain-text description, for the collapsed preview shown when a description is long. */
function description_preview(string $text, int $limit = 100): string {
    $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    return implode(' ', array_slice($words, 0, $limit)) . '…';
}

/**
 * Echo an admin-written description (one or more paragraphs, separated by
 * a blank line) as HTML. If it runs longer than $limit words, only the
 * first $limit words are shown, with a "More" button that reveals the
 * rest — the full text stays in the page (for SEO/accessibility) but
 * hidden until expanded.
 */
/**
 * Render content saved from a TinyMCE rich-text editor field. If the
 * value has no HTML tags in it at all, it's content that was saved
 * before that field had a rich-text editor (plain text, with blank-line
 * paragraph breaks) — rendered the old way instead of as raw HTML, so
 * nothing written before this upgrade looks broken.
 */
function render_rich_content(string $html): void {
    $html = trim($html);
    if ($html === '') return;

    if (strpos($html, '<') === false) {
        foreach (preg_split('/\r?\n\r?\n/', $html) as $para) {
            $para = trim($para);
            if ($para !== '') echo '<p>' . nl2br(h($para)) . '</p>';
        }
        return;
    }

    echo $html;
}

function render_description(string $text, int $limit = 100): void {
    $text = trim($text);
    if ($text === '') return;

    $paragraphs = array_filter(array_map('trim', preg_split('/\r?\n\r?\n/', $text)));

    if (word_count($text) <= $limit) {
        foreach ($paragraphs as $para) { echo '<p>' . nl2br(h($para)) . '</p>'; }
        return;
    }

    echo '<div class="desc-short"><p>' . nl2br(h(description_preview($text, $limit))) . '</p></div>';
    echo '<div class="desc-full" hidden>';
    foreach ($paragraphs as $para) { echo '<p>' . nl2br(h($para)) . '</p>'; }
    echo '</div>';
    echo '<button type="button" class="desc-toggle" aria-expanded="false">More</button>';
}

/**
 * Every visitor who likes or comments gets a random, anonymous token
 * stored in a long-lived cookie — there is no public login system on this
 * site, so this token is the only way to recognise "the same visitor"
 * later (e.g. to show a filled-in heart, or let them delete their own
 * comment). It identifies a browser, not a real person.
 */
function get_visitor_token(): string {
    if (!empty($_COOKIE['bma_visitor'])) {
        return $_COOKIE['bma_visitor'];
    }
    $token = bin2hex(random_bytes(16));
    setcookie('bma_visitor', $token, time() + 60 * 60 * 24 * 730, '/', '', !empty($_SERVER['HTTPS']), true);
    $_COOKIE['bma_visitor'] = $token; // usable immediately within this same request
    return $token;
}

/** How many likes a news article has. */
function get_news_like_count(string $news_id): int {
    return PocketBase::count('news_likes', ['filter' => "news = '" . PocketBase::escape($news_id) . "'"]);
}

/** Whether this visitor (by token) has already liked this article. */
function visitor_has_liked(string $news_id, string $token): bool {
    $rows = PocketBase::list('news_likes', [
        'filter'  => "news = '" . PocketBase::escape($news_id) . "' && token = '" . PocketBase::escape($token) . "'",
        'perPage' => 1,
    ]);
    return !empty($rows);
}

/** How many times a news article has been shared, in total and per platform. */
function get_news_share_counts(string $news_id): array {
    $total = PocketBase::count('news_shares', ['filter' => "news = '" . PocketBase::escape($news_id) . "'"]);
    $by_platform = [];
    foreach (['x', 'facebook', 'whatsapp'] as $platform) {
        $by_platform[$platform] = PocketBase::count('news_shares', [
            'filter' => "news = '" . PocketBase::escape($news_id) . "' && platform = '" . PocketBase::escape($platform) . "'",
        ]);
    }
    return ['total' => $total, 'by_platform' => $by_platform];
}

/**
 * Fetch comments for a news article, oldest first, grouped into top-level
 * comments and a map of parent id => its replies (one level of threading).
 */
function get_news_comments(string $news_id): array {
    $rows = PocketBase::list('news_comments', [
        'filter'  => "news = '" . PocketBase::escape($news_id) . "'",
        'sort'    => '+created',
        'perPage' => 500,
    ]);
    $top     = [];
    $replies = [];
    foreach ($rows as $row) {
        if (!empty($row['parent'])) {
            $replies[$row['parent']][] = $row;
        } else {
            $top[] = $row;
        }
    }
    return ['top' => $top, 'replies' => $replies];
}


function get_auction_images(string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('auction_images', [
        'filter'  => $filter,
        'sort'    => '+sort_order,+id',
        'perPage' => 100,
    ]);
}

/** Fetch the "Trusted By" logos for a country from PocketBase. */
function get_trusted_logos(string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('trusted_logos', [
        'filter'  => $filter,
        'sort'    => '+sort_order,+id',
        'perPage' => 100,
    ]);
}

/**
 * Resolve the image to show for a given record + file field: the uploaded
 * PocketBase file if the admin has set one, otherwise a fallback (e.g. the
 * original placeholder image or a local asset path). This is what lets an
 * image "slot" stay in the exact same place on the page whether or not an
 * admin has uploaded a real picture for it yet.
 */
function pb_image(array $record, string $field, string $collection, string $fallback): string {
    return PocketBase::fileUrl($record, $field, $collection) ?? $fallback;
}

/**
 * Format a PocketBase "created"/"updated" timestamp for display, safely.
 * Records saved before the timestamp fields existed on a collection (or any
 * other edge case where the value is missing) show as "—" instead of a
 * misleading 01/01/1970 date.
 */
function pb_date(?string $value, string $format = 'd/m/Y'): string {
    if (empty($value)) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date($format, $ts) : '—';
}
