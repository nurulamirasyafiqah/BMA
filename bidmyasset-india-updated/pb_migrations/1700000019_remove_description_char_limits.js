/// <reference path="../pb_data/types.d.ts" />

/**
 * Several "text" fields used with the TinyMCE editor were created without
 * an explicit `max`, which left them on PocketBase's implicit default cap
 * of 5000 characters — causing "Must be no more than 5000 character(s)"
 * errors when admins wrote longer content:
 *   - `machinery_items.description`        (Category Description)
 *   - `machinery_items.bottom_description` (Bottom Description)
 *   - `machinery_listings.detail_description` (Read More Page Description)
 *
 * This explicitly sets `max: 0` on each, which PocketBase treats as
 * "no limit" — matching the same fix already applied to
 * `site_settings.discover_description` in migration 1700000018.
 */
migrate((app) => {
    const categories = app.findCollectionByNameOrId("machinery_items");
    ["description", "bottom_description"].forEach((name) => {
        const f = categories.fields.getByName(name);
        if (f) {
            f.max = 0;
            categories.fields.add(f);
        }
    });
    app.save(categories);

    const listings = app.findCollectionByNameOrId("machinery_listings");
    const detailField = listings.fields.getByName("detail_description");
    if (detailField) {
        detailField.max = 0;
        listings.fields.add(detailField);
    }
    app.save(listings);
}, (app) => {
    try {
        const categories = app.findCollectionByNameOrId("machinery_items");
        ["description", "bottom_description"].forEach((name) => {
            const f = categories.fields.getByName(name);
            if (f) {
                f.max = 5000;
                categories.fields.add(f);
            }
        });
        app.save(categories);

        const listings = app.findCollectionByNameOrId("machinery_listings");
        const detailField = listings.fields.getByName("detail_description");
        if (detailField) {
            detailField.max = 5000;
            listings.fields.add(detailField);
        }
        app.save(listings);
    } catch (e) { /* ignore */ }
})
