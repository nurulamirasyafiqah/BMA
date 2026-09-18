/// <reference path="../pb_data/types.d.ts" />

/**
 * Footer "Privacy Policy" and "Terms and Conditions" used to be dead links
 * (href="#"). They now open an in-page popup showing admin-editable content
 * plus a "Last Updated" line — same rich-text editor pattern as the About Us
 * page (see Site Settings → "Privacy Policy & Terms").
 */
migrate((app) => {
    const siteSettings = app.findCollectionByNameOrId("site_settings");

    siteSettings.fields.add(new Field({ type: "editor", name: "privacy_policy_content", convertUrls: false }));
    siteSettings.fields.add(new Field({ type: "text", name: "privacy_policy_updated", max: 255 }));
    siteSettings.fields.add(new Field({ type: "editor", name: "terms_content", convertUrls: false }));
    siteSettings.fields.add(new Field({ type: "text", name: "terms_updated", max: 255 }));

    app.save(siteSettings);
}, (app) => {
    try {
        const siteSettings = app.findCollectionByNameOrId("site_settings");
        ["privacy_policy_content", "privacy_policy_updated", "terms_content", "terms_updated"].forEach((name) => {
            const f = siteSettings.fields.getByName(name);
            if (f) siteSettings.fields.removeById(f.id);
        });
        app.save(siteSettings);
    } catch (e) { /* ignore */ }
})
