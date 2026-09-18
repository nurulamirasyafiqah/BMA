/// <reference path="../pb_data/types.d.ts" />

/**
 * Makes each "Used Machinery Listed" tile (Injection Molding, CNC, Press
 * Machine, etc.) clickable and lead to its own detail page, which shows:
 *   1. An admin-editable description of that machine category.
 *   2. A list of the actual used machines currently for sale in that
 *      category, each with its own photo, brand, model, origin, year, key
 *      specs, and indicative price.
 *
 * This adds:
 *  - `description` on `machinery_items` (the category itself) — the intro
 *    paragraph shown at the top of the category detail page.
 *  - a new `machinery_listings` collection — one row per individual used
 *    machine, linked to its category via the `category` relation field.
 */
migrate((app) => {
    // ---------------- machinery_items: add category description ----------------
    const categories = app.findCollectionByNameOrId("machinery_items");
    categories.fields.add(new Field({ type: "text", name: "description" }));
    app.save(categories);

    // ---------------- machinery_listings (new) ----------------
    const listings = new Collection({
        type: "base",
        name: "machinery_listings",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            {
                type: "relation", name: "category", required: true,
                collectionId: categories.id, maxSelect: 1,
                cascadeDelete: true,
            },
            {
                type: "file", name: "image", maxSelect: 1, maxSize: 5242880,
                mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
            },
            { type: "text", name: "brand", max: 150 },
            { type: "text", name: "model", max: 150 },
            { type: "text", name: "origin", max: 150 },
            { type: "text", name: "year", max: 20 },
            { type: "text", name: "key_specs", max: 500 },
            { type: "text", name: "indicative_price", max: 150 },
            { type: "number", name: "sort_order" },
            { type: "autodate", name: "created", onCreate: true, onUpdate: false },
            { type: "autodate", name: "updated", onCreate: true, onUpdate: true },
        ],
        indexes: [
            "CREATE INDEX idx_machinery_listings_country ON machinery_listings (country)",
            "CREATE INDEX idx_machinery_listings_category ON machinery_listings (category)",
        ],
    });
    app.save(listings);
}, (app) => {
    try {
        const categories = app.findCollectionByNameOrId("machinery_items");
        const f = categories.fields.getByName("description");
        if (f) categories.fields.removeById(f.id);
        app.save(categories);
    } catch (e) { /* ignore */ }

    try {
        const listings = app.findCollectionByNameOrId("machinery_listings");
        app.delete(listings);
    } catch (e) { /* ignore */ }
})
