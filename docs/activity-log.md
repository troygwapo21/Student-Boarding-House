# Activity Log

## 2026-08-28 — Full codebase audit vs. principles

Did a read-only scan of the entire codebase. Ran `php -l` across app, config,
routes, cron, lib (0 syntax failures). Delegated a security audit and an
architecture/style audit. Verified critical items directly.

### Security findings (by severity)

- CRITICAL `fix_accounts.php` (repo root) — web-reachable standalone script;
  resets users id 1/2 to `password123`, rewrites their emails, prints user
  emails/roles. Anyone reaching `/fix_accounts.php` can hijack the admin.
  `.htaccess` does not block it. Candidate: delete.
- HIGH `config/mail.php:24` — real Gmail App Password committed in repo
  (file itself warns not to commit it). Candidate: move to env/untracked,
  rotate the password. Do not echo the value anywhere.
- HIGH `app/Controller.php:248` `uploadFile()` — validates extension only,
  not MIME/content; gallery + system_settings uploads allow `svg` (stored
  XSS via embedded scripts) at AdminController.php:1739/3618 and
  ManagerController.php:1741. Candidate: strip svg, tighten validation.
- LOW — `Router::middleware()` is dead code; all access control relies on
  constructor `requireRole()` per controller (defense-in-depth gap).
- LOW `StudentController.php:1732` `maintenanceCreate` — stores client-
  supplied `room_id` without verifying it belongs to the student.
- LOW `Controller.php:141` `back()` uses unvalidated `HTTP_REFERER`;
  `Controller.php:159` `validateUrl()` accepts `javascript:`/`data:` schemes.
- LOW `cron/reminders.php` — unauthenticated endpoint (no user input, has DB
  lock; acceptable but worth a token check).
- LOW `storage/tmp_phpmailer_test.php` — leftover debug file (safe to delete).

### Things that are good (baseline)

- Every guarded controller enforces `requireAuth()` + `requireRole()` in
  constructor (admin=super_admin, manager=manager|super_admin, student=student).
- All SQL uses PDO prepared statements (no SQLi found).
- All state-changing POSTs validate CSRF; no `$_POST` passed straight to
  insert/update (no mass assignment).
- Student actions filter records by `student_id`/`user_id`; archive recovery
  redacts password/token/secret fields.

### Architecture / style findings

- ~30 near-identical admin/manager view pairs (differ only by branding string)
  — double maintenance.
- God classes: `app/Controller.php` ~3233 lines (notifications, refunds,
  archiving, billing, walk-in/tenant logic); `AdminController.php` ~4015 lines.
  Suggested: extract into `NotificationService`, `RefundService`,
  `ArchiveService`, `TenantService`.
- Helper overlap: `flash()` helper reads/unset vs Controller writes (same name,
  divergent); `redirect()` duplicated; CSRF split across helpers (snake) and
  Controller (camel).
- No dead routes, no orphan views; all 267 routed methods exist.
- `lib/phpmailer/POP3.php` unused.
- CDN version drift: Bootstrap 5.3.2 (admin/manager) vs 5.3.0
  (public/student); FA, jQuery likewise. Chart.js unpinned vs pinned.
- Style: 4-space, same-line braces, consistent. Some non-ASCII glyphs saved as
  mojibake in a few files (Controller.php, AdminController.php,
  MonthlyBillingService.php, layouts).

### Applied (user-approved: critical + high security only, 2026-08-28)

- Deleted `fix_accounts.php` (root) and `storage/tmp_phpmailer_test.php`.
- `config/mail.php` — removed the hardcoded App Password; MAIL_PASSWORD now
  resolves from env var `MAIL_PASSWORD` or `config/mail.local.php`.
- Added `config/mail.local.php` (local-only file returning the password) and
  `config/.htaccess` (deny direct web access to config/).
- `app/Controller.php` `uploadFile()` — now validates real MIME via `finfo`
  against a per-extension allow-map and explicitly rejects `image/svg+xml`;
  unknown extensions are rejected.
- Removed `svg` from gallery + system_settings upload allow-lists
  (AdminController gallery, AdminController system_settings with ico kept,
  ManagerController gallery).
- Verified: `php -l` clean on all touched files; MAIL_PASSWORD resolves via
  local file; MIME test accepts real PNG, rejects SVG and HTML.

### Status / next

Critical + high security fixes done. Still open (not approved): low-severity
security items (dead Router::middleware, maintenanceCreate room ownership,
validateUrl/referer, cron auth), and the architecture/de-duplication work
(god classes, admin/manager twin views).