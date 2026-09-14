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

/** Fetch the latest 3 news items for a country from PocketBase. */
function get_news(string $country): array {
    $filter = "country = '" . PocketBase::escape($country) . "'";
    return PocketBase::list('news', [
        'filter'  => $filter,
        'sort'    => '-news_date,+sort_order',
        'perPage' => 3,
    ]);
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
