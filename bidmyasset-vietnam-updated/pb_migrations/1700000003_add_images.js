/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds admin-manageable images to the site:
 *  - site_settings gets 7 optional single-image "slots" (logo, hero,
 *    process diagram, auction photo, founder avatar, 2 factory banner
 *    photos). Each slot stays exactly where it already is on the page —
 *    the admin can only add / replace / remove the picture in that slot,
 *    never move it.
 *  - machinery_items (new): the "Used Machinery Listed" grid — a real
 *    add/edit/delete list per country, each row with its own image.
 *  - trusted_logos (new): the "Trusted By" logo grid — same idea.
 */
migrate((app) => {
    // ---------------- site_settings: add file fields ----------------
    const siteSettings = app.findCollectionByNameOrId("site_settings");

    const imageFieldNames = [
        "logo_image", "hero_image", "process_image", "auction_image",
        "founder_avatar", "factory_image_1", "factory_image_2",
    ];

    imageFieldNames.forEach((name) => {
        siteSettings.fields.add(new Field({
            type: "file",
            name: name,
            maxSelect: 1,
            maxSize: 5242880, // 5MB
            mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
        }));
    });

    app.save(siteSettings);

    // ---------------- machinery_items (public read, admin-only write) ----------------
    const machinery = new Collection({
        type: "base",
        name: "machinery_items",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            { type: "text", name: "title", required: true, max: 150 },
            {
                type: "file", name: "image", maxSelect: 1, maxSize: 5242880,
                mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
            },
            { type: "number", name: "sort_order" },
        ],
        indexes: [
            "CREATE INDEX idx_machinery_country ON machinery_items (country)",
        ],
    });
    app.save(machinery);

    // ---------------- trusted_logos (public read, admin-only write) ----------------
    const trustedLogos = new Collection({
        type: "base",
        name: "trusted_logos",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            { type: "text", name: "name", required: true, max: 150 },
            {
                type: "file", name: "logo", maxSelect: 1, maxSize: 5242880,
                mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
            },
            { type: "number", name: "sort_order" },
        ],
        indexes: [
            "CREATE INDEX idx_trusted_country ON trusted_logos (country)",
        ],
    });
    app.save(trustedLogos);
}, (app) => {
    // down
    ["machinery_items", "trusted_logos"].forEach((name) => {
        try {
            app.delete(app.findCollectionByNameOrId(name));
        } catch (e) { /* ignore */ }
    });

    try {
        const siteSettings = app.findCollectionByNameOrId("site_settings");
        [
            "logo_image", "hero_image", "process_image", "auction_image",
            "founder_avatar", "factory_image_1", "factory_image_2",
        ].forEach((name) => {
            const f = siteSettings.fields.getByName(name);
            if (f) siteSettings.fields.removeById(f.id);
        });
        app.save(siteSettings);
    } catch (e) { /* ignore */ }
})
