/// <reference path="../pb_data/types.d.ts" />

/**
 * Root cause: PocketBase's plain "text" field type has a *system-level*
 * safe-max fallback of 5000 characters that applies whenever `max` is 0 —
 * setting `max: 0` explicitly (see migrations 1700000018/1700000019) does
 * NOT mean "unlimited" for this field type, it just falls back to that
 * same 5000 default. That's why the cap came back even after those fixes.
 *
 * `about_content` and news `content` never hit this because they were
 * correctly created as the "editor" field type (rich text/HTML), which
 * has no such fallback cap — matching what these four fields actually
 * are: TinyMCE-authored HTML, not plain text.
 *
 * This converts the remaining four to "editor" type as well, so they
 * behave exactly like About Us / News content (truly unlimited):
 *   - site_settings.discover_description   (Discover More Page description)
 *   - machinery_items.description          (Category Description)
 *   - machinery_items.bottom_description   (Bottom Description)
 *   - machinery_listings.detail_description (Read More Page Description)
 *
 * Existing values are preserved — the underlying SQLite column stores
 * text either way; only the field's schema type/validation changes.
 */
migrate((app) => {
    const siteSettings = app.findCollectionByNameOrId("site_settings");
    let f = siteSettings.fields.getByName("discover_description");
    if (f) siteSettings.fields.removeById(f.id);
    siteSettings.fields.add(new Field({ type: "editor", name: "discover_description", convertUrls: false }));
    app.save(siteSettings);

    const categories = app.findCollectionByNameOrId("machinery_items");
    ["description", "bottom_description"].forEach((name) => {
        const cf = categories.fields.getByName(name);
        if (cf) categories.fields.removeById(cf.id);
        categories.fields.add(new Field({ type: "editor", name, convertUrls: false }));
    });
    app.save(categories);

    const listings = app.findCollectionByNameOrId("machinery_listings");
    const lf = listings.fields.getByName("detail_description");
    if (lf) listings.fields.removeById(lf.id);
    listings.fields.add(new Field({ type: "editor", name: "detail_description", convertUrls: false }));
    app.save(listings);
}, (app) => {
    try {
        const siteSettings = app.findCollectionByNameOrId("site_settings");
        let f = siteSettings.fields.getByName("discover_description");
        if (f) siteSettings.fields.removeById(f.id);
        siteSettings.fields.add(new Field({ type: "text", name: "discover_description", max: 5000 }));
        app.save(siteSettings);

        const categories = app.findCollectionByNameOrId("machinery_items");
        ["description", "bottom_description"].forEach((name) => {
            const cf = categories.fields.getByName(name);
            if (cf) categories.fields.removeById(cf.id);
            categories.fields.add(new Field({ type: "text", name, max: 5000 }));
        });
        app.save(categories);

        const listings = app.findCollectionByNameOrId("machinery_listings");
        const lf = listings.fields.getByName("detail_description");
        if (lf) listings.fields.removeById(lf.id);
        listings.fields.add(new Field({ type: "text", name: "detail_description", max: 5000 }));
        app.save(listings);
    } catch (e) { /* ignore */ }
})
