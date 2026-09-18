/// <reference path="../pb_data/types.d.ts" />

/**
 * The very first migration never gave any collection a "created" / "updated"
 * timestamp field. That went unnoticed at first because most admin screens
 * sort by their own "sort_order" field — but a few screens (Contact
 * Submissions, Manage Admins, the Dashboard's recent-submissions widget)
 * sort by "-created" / "+created" to show the newest entries first.
 *
 * Without the field existing, PocketBase rejects that sort with a 400 error.
 * The app's PocketBase::list() helper treats any failed request as "no
 * results" rather than surfacing the error — so those screens silently
 * looked empty ("No submissions yet.", an empty Manage Admins table) even
 * though the underlying records were saved correctly the whole time.
 *
 * This migration adds proper autodate "created" and "updated" fields to
 * every collection, which fixes those screens without any PHP changes.
 */
migrate((app) => {
    const collections = [
        "admins", "site_settings", "services", "news",
        "contact_submissions", "machinery_items", "trusted_logos",
    ];

    collections.forEach((name) => {
        const collection = app.findCollectionByNameOrId(name);

        if (!collection.fields.getByName("created")) {
            collection.fields.add(new Field({
                type: "autodate",
                name: "created",
                onCreate: true,
                onUpdate: false,
            }));
        }
        if (!collection.fields.getByName("updated")) {
            collection.fields.add(new Field({
                type: "autodate",
                name: "updated",
                onCreate: true,
                onUpdate: true,
            }));
        }

        app.save(collection);
    });
}, (app) => {
    const collections = [
        "admins", "site_settings", "services", "news",
        "contact_submissions", "machinery_items", "trusted_logos",
    ];

    collections.forEach((name) => {
        try {
            const collection = app.findCollectionByNameOrId(name);
            ["created", "updated"].forEach((fieldName) => {
                const f = collection.fields.getByName(fieldName);
                if (f) collection.fields.removeById(f.id);
            });
            app.save(collection);
        } catch (e) { /* ignore */ }
    });
})
