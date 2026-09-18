/// <reference path="../pb_data/types.d.ts" />

/**
 * The `discover_description` field on `site_settings` was created without
 * an explicit `max`, which left it on PocketBase's implicit default cap of
 * 5000 characters for "text" fields — causing "Must be no more than 5000
 * character(s)" errors when saving longer content from the TinyMCE editor
 * on the Discover page. This explicitly sets `max: 0`, which PocketBase
 * treats as "no limit", so admins can write as much as they want.
 */
migrate((app) => {
    const siteSettings = app.findCollectionByNameOrId("site_settings");

    const field = siteSettings.fields.getByName("discover_description");
    if (field) {
        field.max = 0;
        siteSettings.fields.add(field);
    }

    app.save(siteSettings);
}, (app) => {
    try {
        const siteSettings = app.findCollectionByNameOrId("site_settings");
        const field = siteSettings.fields.getByName("discover_description");
        if (field) {
            field.max = 5000;
            siteSettings.fields.add(field);
        }
        app.save(siteSettings);
    } catch (e) { /* ignore */ }
})
