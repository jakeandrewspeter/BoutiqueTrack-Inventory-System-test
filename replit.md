# BoutiqueTrack

BoutiqueTrack is a single-boutique PHP/MySQL system for managing product variations, cashier sales, stock, and business reports. Its deployment target is local XAMPP.

## Run & Operate

- `pnpm --filter @workspace/api-server run dev` — run the API server (port 5000)
- `bash boutiquetrack/scripts/dev.sh` — run the BoutiqueTrack preview with local MariaDB and PHP
- `php -l <file>` — check a PHP file for syntax errors
- See `boutiquetrack/README-XAMPP.md` for Windows/XAMPP setup
- `pnpm run typecheck` — full typecheck across all packages
- `pnpm run build` — typecheck + build all packages
- `pnpm --filter @workspace/api-spec run codegen` — regenerate API hooks and Zod schemas from the OpenAPI spec
- `pnpm --filter @workspace/db run push` — push DB schema changes (dev only)
- The PHP app uses the DB settings in `boutiquetrack/config/database.php`.

## Stack

- pnpm workspaces, Node.js 24, TypeScript 5.9
- BoutiqueTrack: PHP 8.1+, PDO, MySQL/MariaDB, server-rendered pages
- XAMPP installation stores data in the boutique's local MySQL/MariaDB service
- Replit preview uses a separate local MariaDB database with sample products
- Existing shared workspace starter services remain under `artifacts/api-server` and `lib/`

## Where things live

- `boutiquetrack/public/` — browser entry point and static CSS/JavaScript
- `boutiquetrack/app/` — authentication, actions, page data, and server-rendered views
- `boutiquetrack/database/install.sql` — source-of-truth MySQL schema and default categories
- `boutiquetrack/database/demo-data.sql` — optional demo catalogue
- `boutiquetrack/config/database.php` — local XAMPP connection settings
- `artifacts/boutique-track/` — Replit preview host; its `/php/` path forwards to the PHP/MariaDB preview

## Architecture decisions

- One boutique per database for the graduation-project scope; no multi-tenant accounts or branches.
- Stock levels change through checkout or reasoned inventory adjustments, not by editing a product form.
- The first administrator is created during setup; there is no shared default login.
- The PHP app is the source of truth and XAMPP deployment target; the web artifact is only a Replit preview host.

## Product

- Product and size/color variation management with SKU, cost, selling price, and reorder levels
- Admin and cashier roles, point-of-sale checkout, stock movement history, and printable receipts
- Dashboard, daily/weekly/monthly sales summaries, product performance, and CSV export

## User preferences

- Build to the concept note's PHP/MySQL and XAMPP target.

## Gotchas

- Create the `boutique_track` database before first-run setup in XAMPP.
- Replit preview data and local XAMPP data are separate databases.

## Pointers

- See the `pnpm-workspace` skill for workspace structure, TypeScript setup, and package details
