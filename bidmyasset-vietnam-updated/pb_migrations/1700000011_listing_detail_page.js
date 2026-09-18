/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds `detail_description` to `machinery_listings`, so each individual
 * used machine (not just its category) can have its own "Read More" page
 * with free-form, admin-editable content — similar to the "Discover More"
 * page, but for one specific machine instead of a whole category, and
 * with the machine's own photo shown alongside it (the listing's existing
 * `image` field is reused for this, so there's nothing new to upload
 * separately unless you want a different photo here than on the card).
 */
migrate((app) => {
    const listings = app.findCollectionByNameOrId("machinery_listings");
    listings.fields.add(new Field({ type: "text", name: "detail_description" }));
    app.save(listings);
}, (app) => {
    try {
        const listings = app.findCollectionByNameOrId("machinery_listings");
        const f = listings.fields.getByName("detail_description");
        if (f) listings.fields.removeById(f.id);
        app.save(listings);
    } catch (e) { /* ignore */ }
})
