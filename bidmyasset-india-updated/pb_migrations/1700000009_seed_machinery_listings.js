/// <reference path="../pb_data/types.d.ts" />

/**
 * Seeds sample "used machine" listings under every existing machinery
 * category (Injection Molding, CNC, Press Machine, Packaging Machine,
 * Industrial Printer, Other Machine), for both India and Vietnam — so the
 * new "Machinery Listings" admin screen and the public category detail
 * pages aren't empty out of the box. These are placeholder/dummy entries;
 * an admin can edit, replace, or delete them at any time from
 * /admin/machinery-listings.php, and upload real photos to replace the
 * placeholder image each one falls back to.
 */
migrate((app) => {
    const listingsCol = app.findCollectionByNameOrId("machinery_listings");

    const dummyByCategoryTitle = {
        "Injection Molding": [
            { brand: "Haitian", model: "MA1200", origin: "China", year: "2016", key_specs: "1200T Clamping Force", indicative_price: "Contact us" },
            { brand: "Toshiba", model: "EC100SX", origin: "Japan", year: "2013", key_specs: "100T All-Electric", indicative_price: "Negotiable" },
        ],
        "CNC": [
            { brand: "Okuma", model: "MX-45VA", origin: "Japan", year: "2015", key_specs: "5-Axis Machining Center", indicative_price: "Contact us" },
            { brand: "Dekel Maho", model: "DMC 635V", origin: "Japan", year: "2011", key_specs: "CNC Machining Center", indicative_price: "Negotiable" },
        ],
        "Press Machine": [
            { brand: "Amada", model: "HFE 100-3", origin: "Japan", year: "2014", key_specs: "100T Press Brake", indicative_price: "Contact us" },
            { brand: "Komatsu", model: "OBS60", origin: "Japan", year: "2012", key_specs: "60T Punch Press", indicative_price: "Negotiable" },
        ],
        "Packaging Machine": [
            { brand: "Bosch", model: "Sigpack", origin: "Germany", year: "2017", key_specs: "Flow Wrapping Machine", indicative_price: "Contact us" },
            { brand: "Multivac", model: "R145", origin: "Germany", year: "2015", key_specs: "Thermoforming Packaging Line", indicative_price: "Negotiable" },
        ],
        "Industrial Printer": [
            { brand: "HP Indigo", model: "5600", origin: "USA", year: "2016", key_specs: "Digital Offset Press", indicative_price: "Contact us" },
            { brand: "Heidelberg", model: "Speedmaster XL 106", origin: "Germany", year: "2013", key_specs: "Offset Printing Press", indicative_price: "Negotiable" },
        ],
        "Other Machine": [
            { brand: "Various", model: "—", origin: "—", year: "—", key_specs: "Miscellaneous industrial equipment", indicative_price: "Contact us" },
        ],
    };

    app.findAllRecords(app.findCollectionByNameOrId("machinery_items")).forEach((category) => {
        const dummies = dummyByCategoryTitle[category.get("title")];
        if (!dummies) return;

        dummies.forEach((d, i) => {
            const rec = new Record(listingsCol);
            rec.set("country", category.get("country"));
            rec.set("category", category.id);
            rec.set("brand", d.brand);
            rec.set("model", d.model);
            rec.set("origin", d.origin);
            rec.set("year", d.year);
            rec.set("key_specs", d.key_specs);
            rec.set("indicative_price", d.indicative_price);
            rec.set("sort_order", i + 1);
            // no image yet — the public page falls back to its placeholder
            // image until an admin uploads a real photo from
            // /admin/machinery-listings.php
            app.save(rec);
        });
    });
}, (app) => {
    try {
        const listingsCol = app.findCollectionByNameOrId("machinery_listings");
        app.findAllRecords(listingsCol).forEach((r) => app.delete(r));
    } catch (e) { /* ignore */ }
})
