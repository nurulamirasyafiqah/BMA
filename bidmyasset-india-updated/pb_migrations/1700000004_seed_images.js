/// <reference path="../pb_data/types.d.ts" />

migrate((app) => {
    // ---------------- machinery_items ----------------
    const machineryCol = app.findCollectionByNameOrId("machinery_items");
    const machineryTemplates = [
        { title: "Injection Molding", sort_order: 1 },
        { title: "CNC", sort_order: 2 },
        { title: "Press Machine", sort_order: 3 },
        { title: "Packaging Machine", sort_order: 4 },
        { title: "Industrial Printer", sort_order: 5 },
        { title: "Other Machine", sort_order: 6 },
    ];

    ["india", "vietnam"].forEach((country) => {
        machineryTemplates.forEach((tpl) => {
            const rec = new Record(machineryCol);
            rec.set("country", country);
            rec.set("title", tpl.title);
            rec.set("sort_order", tpl.sort_order);
            // no image yet — the public page falls back to its placeholder
            // image until an admin uploads one from /admin/machinery.php
            app.save(rec);
        });
    });

    // ---------------- trusted_logos ----------------
    const trustedCol = app.findCollectionByNameOrId("trusted_logos");
    const trustedTemplates = [
        { name: "HICOM-HONDA", sort_order: 1 },
        { name: "SUK SAN", sort_order: 2 },
        { name: "BRIDGESTONE", sort_order: 3 },
        { name: "GREEN SPOT", sort_order: 4 },
        { name: "LPN PLATE MILL", sort_order: 5 },
        { name: "NHK PRECISION", sort_order: 6 },
    ];

    ["india", "vietnam"].forEach((country) => {
        trustedTemplates.forEach((tpl) => {
            const rec = new Record(trustedCol);
            rec.set("country", country);
            rec.set("name", tpl.name);
            rec.set("sort_order", tpl.sort_order);
            // no logo file yet — the public page shows the text name until
            // an admin uploads a logo from /admin/trusted-logos.php
            app.save(rec);
        });
    });
}, (app) => {
    ["machinery_items", "trusted_logos"].forEach((name) => {
        try {
            const col = app.findCollectionByNameOrId(name);
            app.findAllRecords(col).forEach((r) => app.delete(r));
        } catch (e) { /* ignore */ }
    });
})
