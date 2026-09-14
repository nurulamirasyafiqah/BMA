# BidMyAsset — PHP + PocketBase (SQLite) edition

This project used to run on PHP + MySQL/phpMyAdmin. It has been converted to
run on **PHP + PocketBase**. PocketBase is a single-file backend that stores
everything in a **SQLite** database and exposes a REST API — so there is no
MySQL server or phpMyAdmin to install anymore.

## 1. Install & run PocketBase

1. Download the PocketBase binary for your OS from
   https://pocketbase.io/docs/ (or `github.com/pocketbase/pocketbase/releases`).
2. Create a folder for it, e.g. `pocketbase/`, and put the binary inside.
3. Copy the `pb_migrations/` folder from this project into that same
   `pocketbase/` folder (next to the binary). PocketBase automatically runs
   any `.js` files it finds there on startup.
4. Start the server:
   ```
   ./pocketbase serve
   ```
   (on Windows: `pocketbase.exe serve`)
5. On first run this creates `pb_data/data.db` (the SQLite file) and applies
   the migrations, which set up:
   - the `admins`, `site_settings`, `services`, `news`, and
     `contact_submissions` collections
   - a default admin login: **admin@bidmyasset.com / admin123**
   - starter site settings, services, and news rows for both India and
     Vietnam (a leftover of how the migrations were originally written —
     this Vietnam zip only ever shows/edits the Vietnam rows via
     `SITE_COUNTRY`; the India rows just sit unused in this database
     and are harmless to ignore)

   PocketBase's own admin dashboard is available at
   `http://127.0.0.1:8090/_/` if you ever want to inspect the data directly.

**Change the default admin password immediately** — either from PocketBase's
dashboard (Collections → admins) or from this project's `/admin` login once
you're in.

**Also setting up the India site?** That's the matching India zip —
repeat this whole section inside that separate project folder (its own
PocketBase binary, its own `pb_migrations/`, its own port if running both
on the same machine). See §4 for the full explanation of why there are two
separate zips instead of one, and the Docker section there for the easiest
way to run both without doing this by hand.

## 2. Configure the PHP site

By default the PHP code talks to PocketBase at `http://127.0.0.1:8090`. If
your PocketBase server runs elsewhere, set the `PB_URL` environment variable
(e.g. in your web server config or `.env`/`php.ini`) before serving the PHP
site, for example:
```
PB_URL=http://127.0.0.1:8090
```

This zip's `config.php` already defaults `SITE_COUNTRY` to `vietnam`, and
`docker-compose.yml` sets it explicitly too — so you don't need to set
anything for normal use. You'd only ever override it (e.g. to
`SITE_COUNTRY=india`) for local testing purposes; doing that on a real
Vietnam deployment would be unusual since this zip's database only really
has Vietnam's *real* content in it (see §4 for why there are two zips).

Then serve the PHP folder as usual, e.g.:
```
PB_URL=http://127.0.0.1:8090 SITE_COUNTRY=vietnam php -S localhost:8000
```
and open `http://localhost:8000/index.php`.

## 3. What changed

- `config.php` no longer opens a MySQL connection. It now defines `PB_URL`
  and a small `PocketBase` helper class that talks to the PocketBase REST
  API over cURL (`PocketBase::list()`, `::view()`, `::create()`,
  `::update()`, `::delete()`, `::authWithPassword()`).
- `includes/functions.php` (`get_settings`, `get_services`, `get_news`) now
  query PocketBase's `site_settings` / `services` / `news` collections
  instead of running SQL.
- `contact_submit.php` creates a record in the `contact_submissions`
  collection via the PocketBase API.
- The admin panel (`/admin`) logs in against PocketBase's `admins` auth
  collection (email + password) instead of a local `admins` MySQL table, and
  all CRUD screens (Settings / Services / News / Submissions) call the
  PocketBase API using the logged-in admin's auth token, which is kept in
  the PHP session.
- `database.sql` (the old MySQL schema) has been removed and replaced by
  `pb_migrations/1700000001_create_collections.js` (schema) and
  `pb_migrations/1700000002_seed_data.js` (starter data). Later migrations
  (`1700000003`–`1700000009`) add image fields, seed sample images, add
  admin roles plus the Discover More / map-embed content fields, add
  `created`/`updated` timestamp fields to every collection, let superusers
  see other admins' emails in Manage Admins, add category descriptions
  plus the `machinery_listings` collection for the machinery detail pages,
  and seed sample listings for each category (see §9–§11 below).
  `1700000010` allows public self-registration of admin accounts,
  restricted to the `admin` role only (see §5).
- India and Vietnam are no longer one shared site switched by
  `?country=india` / `?country=vietnam` in the URL — each is now its own
  independent website with its own PocketBase database, decided by the
  `SITE_COUNTRY` environment variable instead of a URL parameter (see §4).
- The old India/Vietnam language switcher in the website header is gone
  (it no longer makes sense now that each site only ever shows one
  country) — the header now just shows a flag. Admin login and
  self-registration live at `/admin/login.php` and
  `/admin/public-register.php`, reachable directly by URL but not linked
  from the public site — see §5 for what "Register" means and the
  security trade-off it comes with.
- `/admin` now has a responsive sidebar with a hamburger menu on small
  screens, and the public site's own navigation menu (which previously just
  disappeared with no way to open it on phones) now does the same.

## 4. This is the VIETNAM website — a fully independent system

This zip is **one of two separate zips** for this project — this one runs
the Vietnam website; a matching India zip runs the India website. They
are **completely independent systems**, each with its own PocketBase
database, its own domain, and its own admin logins:

- An admin account created here (on the Vietnam site) does not exist on
  the India site, and vice versa — they're different databases entirely,
  not just different accounts in a shared one.
- A Vietnam admin can only ever log into and manage the Vietnam website.
  An India admin can only ever log into and manage the India website.
  There is no shared login between them and no way to switch between
  sites from within one admin panel.

Both zips run the **exact same code** — the only difference is the
`SITE_COUNTRY` setting (already set to `vietnam` in this zip's
`config.php` and `docker-compose.yml`). This means a bug fix or new
feature only has to be built once and copied into both zips to roll it
out everywhere.

### One superuser who manages both websites

If you want one person to be able to manage *both* the Vietnam and India
sites, the way to do that with two fully separate databases is simple:
**register (or have a superuser create) an account with the same email
and password on both sites.** That person then logs into
`bidmyasset-vietnam.com/admin/login.php` to manage Vietnam, and separately
into `bidmyasset-india.com/admin/login.php` to manage India — same
person, same credentials, but two separate logins into two separate
systems (there's no single login that controls both at once, since the
two databases have no way to talk to each other by design).

The default seeded superuser (`admin@bidmyasset.com` / `admin123`) already
exists with the **same credentials** in both this zip and the India
zip's starter data — that's your one shared superuser out of the box.
**Change this password on both sites** before going live (they're
separate accounts in separate databases, so changing it here does *not*
change it on the India site — you have to do it in both places).

### Running this site with Docker (recommended)

1. Install Docker Desktop (Mac/Windows) or Docker Engine + Compose plugin (Linux).
2. From this project folder, run:
   ```
   docker compose up --build
   ```
3. Open:
   - `http://localhost:8000/` — the Vietnam website
   - `http://localhost:8000/admin/login.php` — the Vietnam admin panel
     (admin@bidmyasset.com / admin123 — change this immediately)
   - `http://localhost:8000/admin/public-register.php` — where new admins
     can sign themselves up (see §5 for the security trade-off this comes with)
   - `http://localhost:8090/_/` — PocketBase's own dashboard for this database
4. Stop with `docker compose down` (the database stays, in the `pb_data`
   volume) or `docker compose down -v` to wipe it.

**Running the India zip on the same machine too?** Put it in a
*different folder* and Docker Compose will automatically keep its
containers, volumes, and network separate from this one — but you'll need
to change this zip's ports (e.g. `8002:80` and `8091:8090` instead of
`8000:80` and `8090:8090`) so they don't clash with the India stack's
ports if you start that one first.

### Deploying to a real server (production)

1. Copy this project folder onto your server (anywhere Docker is installed).
2. Run `docker compose up --build -d` (`-d` = run in the background).
3. Point a reverse proxy (Caddy or Nginx with Let's Encrypt) at this
   container's port 8000 for HTTPS, using your Vietnam domain
   (e.g. `bidmyasset.vn`).
4. Don't expose PocketBase's port 8090 to the public internet unless you
   specifically need remote access to its dashboard.
5. Change the default admin password.
6. Back up the `pb_data` Docker volume regularly — that's this site's
   entire database, independent from the India site's.

The India zip gets deployed the same way, either on this same server
(different folder, different ports) or on a completely separate server —
whichever suits your hosting setup. Since the two are fully independent,
there's no requirement that they even run on the same machine.

### Updating the PocketBase version

`Dockerfile.pocketbase` pins a version via `ARG PB_VERSION=0.40.3`. Check
https://github.com/pocketbase/pocketbase/releases for newer versions and
change that line, then `docker compose up --build` again. Do the same in
the India zip to keep both sites on the same PocketBase version.

## 5. Public admin self-registration — please read the security note

Anyone who finds `/admin/public-register.php` can create their own admin
login and immediately start managing site content — no invite or approval
from an existing admin needed. This page isn't linked anywhere on the
public website (by design, to keep the website and admin panel visually
and structurally separate) — but the URL itself has no login requirement,
so anyone who's given the link, guesses it, or finds it some other way can
still use it. **This was an explicit choice** made when this feature was built,
not an oversight — but it's worth being deliberate about:

- Self-registered accounts can only ever get the `admin` role, never
  `superuser` — this is enforced at the PocketBase database level (see
  `pb_migrations/1700000010_public_admin_registration.js`), so it can't be
  bypassed even by someone calling the API directly instead of using the
  form.
- An `admin`-role account can still edit Site Settings, Services,
  Machinery, News, and view Contact Submissions — so open registration
  does mean anyone can obtain that level of access.
- If you'd rather not allow open sign-up at all: the simplest fix is to
  roll back `1700000010_public_admin_registration.js`'s `createRule`
  change (the migration file itself documents exactly what to revert),
  which makes the page fail even if someone finds the URL. You could also
  keep registration open but add your own extra check in
  `admin/public-register.php` — e.g. a shared invite code the form must
  match before creating the account — if you want a middle ground between
  "fully open" and "superuser invites only".

## 6. Admin-managed images (and why they now actually show up)

Every image on the public site can be managed from `/admin`, without
touching code:

- **Site Settings** page — fixed image "slots" (site logo, hero image,
  process diagram, auction photo, founder portrait, and the 2 banner-strip
  photos). Each slot always stays in its original position on the page —
  admins can only **add** a picture (if empty), **replace** it with a new
  upload, or **remove** it (falls back to a neutral "no image" placeholder,
  not a stock photo).
- **Used Machinery** page — full add / edit / delete list for the
  "Used Machinery Listed" grid, each row with its own photo.
- **Trusted By Logos** page — full add / edit / delete list for the
  "Trusted By" logo grid. Rows without an uploaded logo just show the
  company name as text (matching the original design).

**Important — how images are actually served:** images are uploaded to
PocketBase and stored inside `pb_data/`, but the public `<img>` tags do
**not** link straight to `PB_URL`. In the Docker setup, `PB_URL` is set to
`http://pocketbase:8090` — a hostname that only exists *inside* the Docker
network. A browser can never resolve that, so linking to it directly makes
every image on the site show as broken, no matter how many times you
re-upload it. Instead, every file URL goes through `pb-file.php`, a small
proxy that runs on the web server (which *can* reach `PB_URL`) and streams
the image back to the browser from the site's own domain. This is what
makes "upload a new photo → it shows up on the site" actually work in every
deployment (local, Docker, or behind a reverse proxy) without any extra
configuration. Because PocketBase gives every re-uploaded file a brand-new
random filename, the proxy can (and does) cache responses aggressively
without ever serving a stale image.

## 7. Admin accounts: Superuser vs Admin

The `/admin` login system (this is separate from PocketBase's own `/_/`
dashboard login) has two roles:

- **Superuser** — can do everything an Admin can, plus create, view, and
  delete other admin logins from the **Manage Admins** page. The seeded
  `admin@bidmyasset.com` account is a superuser.
- **Admin** — can manage all site content (Settings, Services, Machinery,
  Logos, News, and view Contact Submissions) but cannot see or manage other
  admin accounts.

To add a teammate: log in as a superuser → **Manage Admins** (in the
sidebar) → **+ Add New Admin** → choose their role. Only a superuser sees
that link at all; a plain Admin who visits `/admin/admins.php` directly
gets an "Access denied" page.

**Change the default password** for `admin@bidmyasset.com` as soon as you
deploy this for real — either via `/admin` (log in, then use PocketBase's
dashboard at `/_/` → Collections → `admins` → edit the record, since there's
currently no self-service "change my password" screen in `/admin` itself)
or by creating a fresh superuser account and deleting the seeded one.

## 8. "Discover More" page & the map on Contact Us

Both are now editable from **Site Settings** in `/admin`, per country:

- **"Discover More" Page** section — a heading and a description. Leave it
  blank and the public page shows a friendly placeholder message instead of
  looking broken.
- **Map / Location** section — paste a Google Maps *embed* URL (Google Maps
  → Share → Embed a map → copy the URL inside `src="..."`, not the whole
  `<iframe>` tag) and the Contact Us section on the homepage embeds it
  automatically. Leave it blank and a simple text placeholder is shown
  instead. It's seeded by default with BidMyAsset's HQ location.

## 9. Machinery category pages ("Injection Molding", "CNC", etc.)

Each tile in the "Used Machinery Listed" grid on the homepage is now a link
to its own detail page, which shows an admin-editable description followed
by the actual used machines currently listed in that category (each with
its own photo, brand, model, origin, year, key specs, and indicative
price) — no "Read More" button, everything shows directly on the page.

Two admin screens manage this, both per country:

- **Used Machinery Listed** (`/admin/machinery.php`) — the category tiles
  themselves. Each one now has a **Category Description** field, shown at
  the top of that category's detail page.
- **Machinery Listings** (`/admin/machinery-listings.php`, new) — the
  individual machines within a category. Filter by category using the tabs
  at the top, then add/edit/delete listings with their own photo and specs.
  There's also a direct "Listings" shortcut next to each category in the
  Used Machinery Listed table.

Deleting a category deletes its listings too (so you don't end up with
orphaned machines that no longer show anywhere) — a confirmation prompt
warns about this before it happens.

Migration `1700000009_seed_machinery_listings.js` seeds 1–2 sample
listings per category, per country, so these screens aren't empty out of
the box. These are placeholder/dummy entries meant purely as a starting
point — edit, replace, or delete them from `/admin/machinery-listings.php`
whenever you have real listings to put in their place.

## 10. If "Contact Submissions" or "Manage Admins" ever look empty again

Both of those screens (and the Dashboard's "recent submissions" list) sort
records by newest-first, which requires every collection to have a
`created` timestamp field. The very first version of this project didn't
add that field to any collection — so those specific screens would
silently show as empty ("No submissions yet.", an empty admin list) even
though the data was saved correctly the whole time, because PocketBase
rejects a sort on a field that doesn't exist and the app's PocketBase
helper treats a failed request the same as "no results" rather than
showing an error.

Migration `1700000006_add_timestamps.js` fixes this by adding `created`/
`updated` fields to every collection. If you're upgrading an existing
deployment, just make sure this migration file is present alongside the
others and restart PocketBase — it applies automatically without touching
your existing data. Records saved *before* this migration ran won't have a
retroactive timestamp (they'll show "—" instead of a date), but they'll
display correctly, and everything saved from now on gets a real one.

## 11. If an admin's email shows blank in "Manage Admins"

PocketBase hides the `email` field of an auth record from anyone except
that record's own owner, unless the collection has a `manageRule` granting
broader access — a logged-in superuser could otherwise see every other
admin's name and role in the "Manage Admins" list, but not their email.

Migration `1700000007_admins_manage_rule.js` adds that `manageRule` so any
logged-in superuser can fully see (and manage) every admin record. As with
the other migrations, dropping this file into `pb_migrations/` and
restarting PocketBase applies it automatically without touching existing
data.

## 12. Notes

- PocketBase collection access rules: `site_settings`, `services`, and
  `news` are readable by anyone (so the public site works without login),
  but can only be created/edited/deleted by an authenticated `admins`
  record. `contact_submissions` can be created by anyone (the public
  contact form) but can only be listed/viewed/deleted by an admin.
- Everything the PHP code needs from PocketBase is plain HTTP + JSON, so no
  Composer packages or PocketBase SDK are required.
