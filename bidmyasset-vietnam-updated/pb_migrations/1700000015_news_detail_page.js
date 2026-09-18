/// <reference path="../pb_data/types.d.ts" />

/**
 * Makes each News item clickable and lead to its own full article page,
 * similar to what `1700000011_listing_detail_page.js` did for machinery
 * listings. Adds to `news`:
 *  - `author`  — plain text, shown under the title/date on the article page.
 *  - `image`   — an optional header photo for the article.
 *  - `content` — a rich-text ("editor") field, so an admin can write the
 *    article body with bold/italic text, numbered and bullet lists,
 *    tables, links, etc. via a WYSIWYG editor in the admin panel, instead
 *    of being limited to plain paragraphs.
 */
migrate((app) => {
    const news = app.findCollectionByNameOrId("news");

    news.fields.add(new Field({ type: "text", name: "author", max: 150 }));
    news.fields.add(new Field({
        type: "file", name: "image", maxSelect: 1, maxSize: 5242880,
        mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
    }));
    news.fields.add(new Field({ type: "editor", name: "content", convertUrls: false }));

    app.save(news);
}, (app) => {
    try {
        const news = app.findCollectionByNameOrId("news");
        ["author", "image", "content"].forEach((name) => {
            const f = news.fields.getByName(name);
            if (f) news.fields.removeById(f.id);
        });
        app.save(news);
    } catch (e) { /* ignore */ }
})
