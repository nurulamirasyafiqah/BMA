/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds `bottom_title` to `machinery_items`, so the block shown at the
 * bottom of a category detail page (below the machine listings) can have
 * its own heading — e.g. "What is a plastic injection molding machine?" —
 * above the `bottom_description` text, instead of the description text
 * standing alone with no heading.
 */
migrate((app) => {
    const categories = app.findCollectionByNameOrId("machinery_items");
    categories.fields.add(new Field({ type: "text", name: "bottom_title", max: 200 }));
    app.save(categories);
}, (app) => {
    try {
        const categories = app.findCollectionByNameOrId("machinery_items");
        const f = categories.fields.getByName("bottom_title");
        if (f) categories.fields.removeById(f.id);
        app.save(categories);
    } catch (e) { /* ignore */ }
})
