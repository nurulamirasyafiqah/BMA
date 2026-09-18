/// <reference path="../pb_data/types.d.ts" />

/**
 * Gives the "About Us" page (previously a hardcoded placeholder with no
 * admin fields at all) the same admin-editable shape as a News article:
 * a heading, a header photo, and a rich-text body (paragraphs, headings,
 * bold/italic/underline, bullet & numbered lists, tables, links,
 * alignment, blockquotes...) written with a WYSIWYG editor in the admin
 * panel (see Site Settings → "About Us" Page).
 */
migrate((app) => {
    const siteSettings = app.findCollectionByNameOrId("site_settings");

    siteSettings.fields.add(new Field({ type: "text", name: "about_title", max: 255 }));
    siteSettings.fields.add(new Field({
        type: "file", name: "about_image", maxSelect: 1, maxSize: 5242880,
        mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
    }));
    siteSettings.fields.add(new Field({ type: "editor", name: "about_content", convertUrls: false }));

    siteSettings.fields.add(new Field({
        type: "file", name: "discover_image", maxSelect: 1, maxSize: 5242880,
        mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
    }));

    app.save(siteSettings);
}, (app) => {
    try {
        const siteSettings = app.findCollectionByNameOrId("site_settings");
        ["about_title", "about_image", "about_content", "discover_image"].forEach((name) => {
            const f = siteSettings.fields.getByName(name);
            if (f) siteSettings.fields.removeById(f.id);
        });
        app.save(siteSettings);
    } catch (e) { /* ignore */ }
})
