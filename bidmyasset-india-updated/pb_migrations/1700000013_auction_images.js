/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds an "Auction Images" gallery so the single photo beside "Leading
 * Machinery Auction Platform" on the homepage can become a clickable
 * image carousel (prev/next arrows + dots), with an optional short
 * caption overlay on each photo (e.g. brand / model / date), the way an
 * individual machine's photo gallery is shown on the reference site.
 *
 * This is a real add/edit/delete list (like `trusted_logos`), not a fixed
 * number of slots, so an admin can add as many photos as they like from
 * /admin/auction-images.php. The existing single `auction_image` field on
 * `site_settings` is left in place and now only used as a fallback if this
 * gallery is empty.
 *
 * Seeds 3 empty (no photo yet) rows per country so the carousel has
 * something to show right away — an admin just needs to upload photos
 * into the existing rows rather than creating them from scratch.
 */
migrate((app) => {
    const auctionImages = new Collection({
        type: "base",
        name: "auction_images",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            {
                type: "file", name: "image", maxSelect: 1, maxSize: 5242880,
                mimeTypes: ["image/jpeg", "image/png", "image/webp", "image/gif", "image/svg+xml"],
            },
            { type: "text", name: "caption", max: 300 },
            { type: "number", name: "sort_order" },
            { type: "autodate", name: "created", onCreate: true, onUpdate: false },
            { type: "autodate", name: "updated", onCreate: true, onUpdate: true },
        ],
        indexes: [
            "CREATE INDEX idx_auction_images_country ON auction_images (country)",
        ],
    });
    app.save(auctionImages);

    ["india", "vietnam"].forEach((country) => {
        for (let i = 1; i <= 3; i++) {
            const rec = new Record(auctionImages);
            rec.set("country", country);
            rec.set("caption", "");
            rec.set("sort_order", i);
            app.save(rec);
        }
    });
}, (app) => {
    try {
        app.delete(app.findCollectionByNameOrId("auction_images"));
    } catch (e) { /* ignore */ }
})
