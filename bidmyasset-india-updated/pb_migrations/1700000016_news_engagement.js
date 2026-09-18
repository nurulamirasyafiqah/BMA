/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds public engagement to news articles:
 *  - `news_likes`   — one row per (article, visitor). A visitor is
 *    identified by an anonymous random token stored in a long-lived
 *    cookie (see get_visitor_token() in includes/functions.php) since
 *    the site has no public login system. A unique index stops the same
 *    visitor liking an article twice.
 *  - `news_shares`  — one row per share-button click (platform: x,
 *    facebook, or whatsapp), for the share counts.
 *  - `news_comments` — public comments on an article. `parent` makes a
 *    comment a reply to another comment (one level of threading). Each
 *    comment also carries the visitor's token, so a visitor can delete
 *    their own comment later without needing an account.
 *
 * Everyone can create/read these (no login required to like/comment),
 * but deleting requires either an admin session OR the same visitor
 * token the record was created with (?token=... query param) — enforced
 * directly by PocketBase's deleteRule, not just app code.
 */
migrate((app) => {
    const news = app.findCollectionByNameOrId("news");
    const adminOrOwnToken = '@request.auth.collectionName = "admins" || (@request.query.token != "" && @request.query.token = token)';

    // ---------------- news_likes ----------------
    const likes = new Collection({
        type: "base",
        name: "news_likes",
        listRule: "",
        viewRule: "",
        createRule: "",
        updateRule: null,
        deleteRule: adminOrOwnToken,
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            {
                type: "relation", name: "news", required: true,
                collectionId: news.id, maxSelect: 1, cascadeDelete: true,
            },
            { type: "text", name: "token", required: true, max: 64 },
            { type: "text", name: "ip", max: 64 },
            { type: "autodate", name: "created", onCreate: true, onUpdate: false },
        ],
        indexes: [
            "CREATE UNIQUE INDEX idx_news_likes_unique ON news_likes (news, token)",
        ],
    });
    app.save(likes);

    // ---------------- news_shares ----------------
    const shares = new Collection({
        type: "base",
        name: "news_shares",
        listRule: "",
        viewRule: "",
        createRule: "",
        updateRule: null,
        deleteRule: '@request.auth.collectionName = "admins"',
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            {
                type: "relation", name: "news", required: true,
                collectionId: news.id, maxSelect: 1, cascadeDelete: true,
            },
            { type: "select", name: "platform", required: true, maxSelect: 1, values: ["x", "facebook", "whatsapp"] },
            { type: "text", name: "ip", max: 64 },
            { type: "autodate", name: "created", onCreate: true, onUpdate: false },
        ],
        indexes: [
            "CREATE INDEX idx_news_shares_news ON news_shares (news)",
        ],
    });
    app.save(shares);

    // ---------------- news_comments ----------------
    const comments = new Collection({
        type: "base",
        name: "news_comments",
        listRule: "",
        viewRule: "",
        createRule: "",
        updateRule: null,
        deleteRule: adminOrOwnToken,
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            {
                type: "relation", name: "news", required: true,
                collectionId: news.id, maxSelect: 1, cascadeDelete: true,
            },
            { type: "text", name: "name", required: true, max: 100 },
            { type: "text", name: "content", required: true, max: 2000 },
            { type: "text", name: "token", required: true, max: 64 },
            { type: "text", name: "ip", max: 64 },
            { type: "autodate", name: "created", onCreate: true, onUpdate: false },
        ],
        indexes: [
            "CREATE INDEX idx_news_comments_news ON news_comments (news)",
        ],
    });
    app.save(comments);

    // `parent` (reply-to) is a self relation, added after creation so the
    // collection already has an id to point back to.
    comments.fields.add(new Field({
        type: "relation", name: "parent",
        collectionId: comments.id, maxSelect: 1, cascadeDelete: true,
    }));
    app.save(comments);
}, (app) => {
    ["news_comments", "news_shares", "news_likes"].forEach((name) => {
        try {
            app.delete(app.findCollectionByNameOrId(name));
        } catch (e) { /* ignore */ }
    });
})
