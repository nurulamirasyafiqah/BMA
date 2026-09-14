/// <reference path="../pb_data/types.d.ts" />

/**
 * Allows anyone visiting the public website to self-register an admin
 * account via the new "Register" link/page — no invite or approval from
 * an existing superuser needed.
 *
 * IMPORTANT SECURITY NOTE: this is an intentional, explicit choice — the
 * site owner asked for open self-registration so admins can sign
 * themselves up directly from the website. Be aware that this means
 * *anyone* who finds the registration page can create a login with access
 * to manage site content (settings, services, machinery, news, etc.).
 *
 * As a guardrail, public self-registration can only ever create an
 * "admin" role account — never "superuser" — enforced here at the
 * database level (not just in the PHP form), so even a direct API call
 * can't self-promote to superuser. Only an existing superuser can create
 * another superuser (via /admin/register.php while logged in).
 *
 * If you want to lock this down later (e.g. require an invite code, or
 * disable public sign-up entirely and go back to superuser-only invites),
 * this is the migration to edit or roll back.
 */
migrate((app) => {
    const admins = app.findCollectionByNameOrId("admins");
    admins.createRule = "@request.body.role = 'admin' || (@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser')";
    app.save(admins);
}, (app) => {
    try {
        const admins = app.findCollectionByNameOrId("admins");
        admins.createRule = "@request.auth.collectionName = 'admins' && @request.auth.role = 'superuser'";
        app.save(admins);
    } catch (e) { /* ignore */ }
})
