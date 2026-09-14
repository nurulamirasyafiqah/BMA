/// <reference path="../pb_data/types.d.ts" />

/**
 * Seeds the same starter data that used to live in database.sql:
 *  - one admin login (username/email: admin@bidmyasset.com | password: admin123)
 *  - site_settings for india + vietnam
 *  - the 6 services x 2 countries
 *  - the 3 news items x 2 countries
 *
 * CHANGE THE ADMIN PASSWORD IMMEDIATELY AFTER FIRST LOGIN.
 */
migrate((app) => {
    // ---------------- admin login ----------------
    const adminsCol = app.findCollectionByNameOrId("admins");
    const admin = new Record(adminsCol);
    admin.set("email", "admin@bidmyasset.com");
    admin.set("password", "admin123");
    admin.set("name", "Admin");
    admin.set("verified", true);
    app.save(admin);

    // ---------------- site_settings ----------------
    const settingsCol = app.findCollectionByNameOrId("site_settings");

    const siteSettingsData = [
        {
            country: "india",
            site_title: "BidMyAsset India",
            hero_title: "Buy and Sell wisely with high quality Second hand machinery",
            hero_subtitle: "Invest In Your Ideal Asset",
            hero_text: "Boost your business's competitiveness. BidMyAsset India helps you source cheap machinery and used industrial equipment from trusted sellers. Whether you're upgrading your production line or liquidating a shutdown factory, we'll help you find or sell used machinery that works for your business and your budget.",
            services_intro: "BidMyAsset is a platform where businesses buy and sell used machinery, second hand machinery, and used machinery equipment across India. We make it straightforward, whether you're hunting for cheap machinery to keep costs down or need to move used industrial equipment quickly.",
            office_name: "Bid My Asset (India) Pvt. Ltd.",
            address_line1: "Plot 5/15, Business Bay Tower",
            address_line2: "Sector 44, Gurugram",
            address_line3: "Haryana, 122003, India",
            email: "hello.in@bidmyasset.com",
            phone: "+91 98-765-43210",
            live_auction_url: "https://bidmyasset.online/",
            facebook_url: "https://facebook.com/bidmyasset",
            linkedin_url: "https://linkedin.com/company/bidmyasset",
            tiktok_url: "https://tiktok.com/@bidmyasset",
            youtube_url: "https://youtube.com/@bidmyasset",
            founder_name: "Jeevan Muniandy",
            founder_quote: "We are committed to creating a transparent and secure marketplace for second-hand asset trading, because we believe that every asset has value and every relationship matters.",
        },
        {
            country: "vietnam",
            site_title: "BidMyAsset Vietnam",
            hero_title: "Buy and Sell wisely with high quality Second hand machinery",
            hero_subtitle: "Invest In Your Ideal Asset",
            hero_text: "Boost your business's competitiveness. BidMyAsset Vietnam helps you source cheap machinery and used industrial equipment from trusted sellers. Whether you're upgrading your production line or liquidating a shutdown factory, we'll help you find or sell used machinery that works for your business and your budget.",
            services_intro: "BidMyAsset is a platform where businesses buy and sell used machinery, second hand machinery, and used machinery equipment across Vietnam. We make it straightforward, whether you're hunting for cheap machinery to keep costs down or need to move used industrial equipment quickly.",
            office_name: "Bid My Asset (Vietnam) Co., Ltd.",
            address_line1: "Level 8, Saigon Trade Center",
            address_line2: "37 Ton Duc Thang Street, Ben Nghe Ward",
            address_line3: "District 1, Ho Chi Minh City, 700000",
            email: "hello.vn@bidmyasset.com",
            phone: "+84 28-3822-9988",
            live_auction_url: "https://bidmyasset.online/",
            facebook_url: "https://facebook.com/bidmyasset",
            linkedin_url: "https://linkedin.com/company/bidmyasset",
            tiktok_url: "https://tiktok.com/@bidmyasset",
            youtube_url: "https://youtube.com/@bidmyasset",
            founder_name: "Jeevan Muniandy",
            founder_quote: "We are committed to creating a transparent and secure marketplace for second-hand asset trading, because we believe that every asset has value and every relationship matters.",
        },
    ];

    siteSettingsData.forEach((data) => {
        const rec = new Record(settingsCol);
        Object.keys(data).forEach((k) => rec.set(k, data[k]));
        app.save(rec);
    });

    // ---------------- services ----------------
    const servicesCol = app.findCollectionByNameOrId("services");
    const serviceTemplates = [
        {
            title: "Buy Used Industrial & Used Machinery Equipment",
            description: "Browse a wide range of used industrial equipment and used machinery equipment across multiple industries and brands. Our auctions give you direct access to quality stock at honest prices, including cheap machinery from factories looking to offload surplus assets.",
            sort_order: 1,
        },
        {
            title: "Sell Used Industrial & Used Machinery Equipment",
            description: "Got unused machinery or unused industrial equipment sitting idle? List it on BidMyAsset and reach buyers fast. From individual machines to full shutdown factory clearances, we assist you sell machine assets at competitive prices.",
            sort_order: 2,
        },
        {
            title: "Dismantling Services",
            description: "Need machinery taken apart safely and efficiently? Our dismantling team handles used industrial equipment and used machinery of all sizes, from single units to full shutdown factory clearances.",
            sort_order: 3,
        },
        {
            title: "Insurance",
            description: "Buying or selling used machinery comes with real risks of damage, theft, and unexpected downtime. Our insurance plans are built specifically for used industrial equipment and second hand machinery.",
            sort_order: 4,
        },
        {
            title: "Transportation",
            description: "Moving used machinery or used industrial equipment isn't straightforward. Our transportation team manages everything from secure packaging to on-site delivery.",
            sort_order: 5,
        },
        {
            title: "24/7 Support",
            description: "Have a question about buying used machinery or need help listing used industrial equipment for sale? Our team is available 24/7 to walk you through the process.",
            sort_order: 6,
        },
    ];

    ["india", "vietnam"].forEach((country) => {
        serviceTemplates.forEach((tpl) => {
            const rec = new Record(servicesCol);
            rec.set("country", country);
            rec.set("title", tpl.title);
            rec.set("description", tpl.description);
            rec.set("sort_order", tpl.sort_order);
            app.save(rec);
        });
    });

    // ---------------- news ----------------
    const newsCol = app.findCollectionByNameOrId("news");
    const newsTemplates = [
        { title: "Machinery Auction vs. Private Sale: How to Choose the Best Way to Sell Your Used Machinery", news_date: "2026-09-04", sort_order: 1 },
        { title: "Where to Sell Used CNC Machines", news_date: "2026-06-16", sort_order: 2 },
        { title: "How to Sell Your Used Machinery and Get the Best Value", news_date: "2026-06-16", sort_order: 3 },
    ];

    ["india", "vietnam"].forEach((country) => {
        newsTemplates.forEach((tpl) => {
            const rec = new Record(newsCol);
            rec.set("country", country);
            rec.set("title", country === "india" && tpl.title.indexOf("CNC") !== -1
                ? "Where to Sell Used CNC Machines in India"
                : (country === "vietnam" && tpl.title.indexOf("CNC") !== -1
                    ? "Where to Sell Used CNC Machines in Vietnam"
                    : tpl.title));
            rec.set("news_date", tpl.news_date);
            rec.set("sort_order", tpl.sort_order);
            app.save(rec);
        });
    });
}, (app) => {
    // down: wipe seeded records (best-effort)
    ["site_settings", "services", "news"].forEach((name) => {
        try {
            const col = app.findCollectionByNameOrId(name);
            app.findAllRecords(col).forEach((r) => app.delete(r));
        } catch (e) { /* ignore */ }
    });
    try {
        const admin = app.findAuthRecordByEmail("admins", "admin@bidmyasset.com");
        app.delete(admin);
    } catch (e) { /* ignore */ }
})
