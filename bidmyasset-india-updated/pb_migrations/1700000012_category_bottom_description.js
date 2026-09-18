/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds `bottom_description` to `machinery_items` (the category itself), so
 * an admin can add a second, free-form block of content that's shown at the
 * BOTTOM of the category detail page — below the "Click to join the
 * auction" button (which uses `site_settings.live_auction_url`) and below
 * the grid of used machine listings. This mirrors `description` (the
 * intro shown at the top of the page) and `detail_description` on
 * `machinery_listings` (the same idea, one level down, for a single
 * machine).
 */
migrate((app) => {
    const categories = app.findCollectionByNameOrId("machinery_items");
    categories.fields.add(new Field({ type: "text", name: "bottom_description" }));
    app.save(categories);
}, (app) => {
    try {
        const categories = app.findCollectionByNameOrId("machinery_items");
        const f = categories.fields.getByName("bottom_description");
        if (f) categories.fields.removeById(f.id);
        app.save(categories);
    } catch (e) { /* ignore */ }
})
