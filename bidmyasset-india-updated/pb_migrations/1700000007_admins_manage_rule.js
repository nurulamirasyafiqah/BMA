/// <reference path="../pb_data/types.d.ts" />

/**
 * PocketBase auth collections hide certain fields — most notably `email`
 * (governed by each record's own `emailVisibility` flag) — from anyone
 * except the record's own owner, UNLESS the collection has a `manageRule`
 * granting broader access.
 *
 * Without this, a superuser opening "Manage Admins" could see every OTHER
 * admin's name and role, but not their email (it would simply be missing
 * from the API response — which is what caused the "Undefined array key
 * email" warning on admins.php). Only the logged-in user's own record ever
 * showed an email, because self-view is always allowed regardless of
 * emailVisibility.
 *
 * Adding a manageRule that matches logged-in superusers gives them full
 * visibility (and manage rights) over every admin record, which is exactly
 * the access "Manage Admins" is meant to have.
 */
migrate((app) => {
    const admins = app.findCollectionByNameOrId("admins");
    admins.manageRule = "@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser'";
    app.save(admins);
}, (app) => {
    try {
        const admins = app.findCollectionByNameOrId("admins");
        admins.manageRule = null;
        app.save(admins);
    } catch (e) { /* ignore */ }
})
