/// <reference path="../pb_data/types.d.ts" />

/**
 * Creates every collection needed by the BidMyAsset website:
 *  - admins              (auth collection, used to log in to /admin)
 *  - site_settings       (per-country hero/contact/social text)
 *  - services            (the 6 service cards per country)
 *  - news                (news list per country)
 *  - contact_submissions (messages sent from the public contact form)
 */
migrate((app) => {
    // ------------------------------------------------------------------
    // admins (auth collection)
    // ------------------------------------------------------------------
    const admins = new Collection({
        type: "auth",
        name: "admins",
        listRule: null,
        viewRule: "@request.auth.id = id",
        createRule: null,
        updateRule: "@request.auth.id = id",
        deleteRule: null,
        fields: [
            {
                type: "text",
                name: "name",
                max: 100,
            },
        ],
        passwordAuth: {
            enabled: true,
            identityFields: ["email"],
        },
    });
    app.save(admins);

    // ------------------------------------------------------------------
    // site_settings (public read, admin-only write)
    // ------------------------------------------------------------------
    const siteSettings = new Collection({
        type: "base",
        name: "site_settings",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            { type: "text", name: "site_title", required: true, max: 150 },
            { type: "text", name: "hero_title", required: true, max: 255 },
            { type: "text", name: "hero_subtitle", required: true, max: 255 },
            { type: "text", name: "hero_text" },
            { type: "text", name: "services_intro" },
            { type: "text", name: "office_name", required: true, max: 150 },
            { type: "text", name: "address_line1", max: 200 },
            { type: "text", name: "address_line2", max: 200 },
            { type: "text", name: "address_line3", max: 200 },
            { type: "email", name: "email" },
            { type: "text", name: "phone", max: 50 },
            { type: "url", name: "live_auction_url" },
            { type: "url", name: "facebook_url" },
            { type: "url", name: "linkedin_url" },
            { type: "url", name: "tiktok_url" },
            { type: "url", name: "youtube_url" },
            { type: "text", name: "founder_name", max: 100 },
            { type: "text", name: "founder_quote" },
        ],
        indexes: [
            "CREATE UNIQUE INDEX idx_site_settings_country ON site_settings (country)",
        ],
    });
    app.save(siteSettings);

    // ------------------------------------------------------------------
    // services (public read, admin-only write)
    // ------------------------------------------------------------------
    const services = new Collection({
        type: "base",
        name: "services",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            { type: "text", name: "title", required: true, max: 150 },
            { type: "text", name: "description", required: true },
            { type: "number", name: "sort_order" },
        ],
        indexes: [
            "CREATE INDEX idx_services_country ON services (country)",
        ],
    });
    app.save(services);

    // ------------------------------------------------------------------
    // news (public read, admin-only write)
    // ------------------------------------------------------------------
    const news = new Collection({
        type: "base",
        name: "news",
        listRule: "",
        viewRule: "",
        createRule: "@request.auth.collectionName = 'admins'",
        updateRule: "@request.auth.collectionName = 'admins'",
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            { type: "text", name: "title", required: true, max: 255 },
            { type: "date", name: "news_date", required: true },
            { type: "number", name: "sort_order" },
        ],
        indexes: [
            "CREATE INDEX idx_news_country ON news (country)",
        ],
    });
    app.save(news);

    // ------------------------------------------------------------------
    // contact_submissions (public create only, admin-only read/delete)
    // ------------------------------------------------------------------
    const submissions = new Collection({
        type: "base",
        name: "contact_submissions",
        listRule: "@request.auth.collectionName = 'admins'",
        viewRule: "@request.auth.collectionName = 'admins'",
        createRule: "",
        updateRule: null,
        deleteRule: "@request.auth.collectionName = 'admins'",
        fields: [
            { type: "text", name: "country", required: true, max: 20 },
            { type: "text", name: "name", required: true, max: 150 },
            { type: "text", name: "company", max: 150 },
            { type: "text", name: "phone", max: 50 },
            { type: "email", name: "email" },
            { type: "text", name: "message" },
        ],
        indexes: [
            "CREATE INDEX idx_contact_country ON contact_submissions (country)",
        ],
    });
    app.save(submissions);
}, (app) => {
    // down: remove everything created above (in reverse order, in case of FK-like ordering)
    ["contact_submissions", "news", "services", "site_settings", "admins"].forEach((name) => {
        try {
            const c = app.findCollectionByNameOrId(name);
            app.delete(c);
        } catch (e) {
            // already gone, ignore
        }
    });
})
