/// <reference path="../pb_data/types.d.ts" />

/**
 * Adds:
 *  - a "role" field to `admins` (superuser | admin), so the app can offer
 *    two levels of login: a superuser who can create/manage other admin
 *    accounts, and regular admins who manage site content but not accounts.
 *    The rules on `admins` are updated so a logged-in superuser can list,
 *    create, view, and delete other admin records via the API (previously
 *    createRule/listRule/deleteRule were "null", meaning nobody at all
 *    could do this through the API).
 *  - `discover_title` / `discover_description` on `site_settings`, so the
 *    "Discover More" page has real editable content instead of being a
 *    permanently-empty placeholder.
 *  - `map_embed_url` on `site_settings`, so the admin can paste a Google
 *    Maps (or other provider) embed link and have it appear automatically
 *    on the public Contact Us section, without editing any code.
 */
migrate((app) => {
    // ---------------- admins: add role + update access rules ----------------
    const admins = app.findCollectionByNameOrId("admins");

    admins.fields.add(new Field({
        type: "select",
        name: "role",
        required: true,
        maxSelect: 1,
        values: ["superuser", "admin"],
    }));

    admins.listRule   = "@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser'";
    admins.viewRule   = "@request.auth.id = id || (@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser')";
    admins.createRule = "@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser'";
    admins.updateRule = "@request.auth.id = id || (@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser')";
    admins.deleteRule = "@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser' && @request.auth.id != id";

    app.save(admins);

    // Make the existing seeded admin a superuser so there's always at
    // least one account able to manage other admin logins.
    try {
        const existing = app.findAuthRecordByEmail("admins", "admin@bidmyasset.com");
        existing.set("role", "superuser");
        app.save(existing);
    } catch (e) { /* seed admin not found, ignore */ }

    // ---------------- site_settings: discover page + map embed ----------------
    const siteSettings = app.findCollectionByNameOrId("site_settings");

    siteSettings.fields.add(new Field({ type: "text", name: "discover_title", max: 255 }));
    siteSettings.fields.add(new Field({ type: "text", name: "discover_description" }));
    siteSettings.fields.add(new Field({ type: "url", name: "map_embed_url" }));

    app.save(siteSettings);

    // Seed the BidMyAsset HQ map link on both existing country records so
    // the Contact section shows a real map immediately (admins can still
    // change/replace it per country from Site Settings).
    const hqMapUrl = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4034.489042148637!2d100.43411781076114!3d5.352646394603674!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304ac732a279eaa3%3A0x538abefb23d6c227!2sBidMyAsset%20Resources%20(M)%20Sdn.%20Bhd.!5e1!3m2!1sen!2smy!4v1788921519835!5m2!1sen!2smy";
    try {
        app.findAllRecords(siteSettings).forEach((rec) => {
            rec.set("map_embed_url", hqMapUrl);
            app.save(rec);
        });
    } catch (e) { /* ignore */ }
}, (app) => {
    try {
        const admins = app.findCollectionByNameOrId("admins");
        const f = admins.fields.getByName("role");
        if (f) admins.fields.removeById(f.id);
        admins.listRule   = null;
        admins.viewRule   = "@request.auth.id = id";
        admins.createRule = null;
        admins.updateRule = "@request.auth.id = id";
        admins.deleteRule = null;
        app.save(admins);
    } catch (e) { /* ignore */ }

    try {
        const siteSettings = app.findCollectionByNameOrId("site_settings");
        ["discover_title", "discover_description", "map_embed_url"].forEach((name) => {
            const f = siteSettings.fields.getByName(name);
            if (f) siteSettings.fields.removeById(f.id);
        });
        app.save(siteSettings);
    } catch (e) { /* ignore */ }
})
