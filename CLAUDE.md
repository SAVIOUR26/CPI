# CLAUDE.md — Handover notes for deploying CPI to cPanel

This file is for whoever (or whichever Claude Code session) takes this
repository from GitHub to the live `crawfordinstitute.online` cPanel
hosting account and does final touches. Read this fully before touching
the server.

## What this app is

A zero-Composer PHP MVC app (see README.md for the stack and layout). It
was built and smoke-tested locally against MariaDB with PHP's built-in
dev server (`php -S`). It has **not** yet been deployed to real cPanel
hosting or tested against Flutterwave/SMTP/SMS in live mode — that's the
remaining work for this handover.

## Deployment steps (cPanel, shared hosting)

1. **Get the code onto the server.** Either:
   - cPanel's "Git Version Control" feature, pointed at this GitHub repo
     (preferred — makes future updates a `git pull` away), or
   - Download a zip of the repo and extract it via File Manager / SSH if
     Git Version Control isn't available on this hosting plan.

2. **Point the domain's Document Root at `public/`.** In cPanel →
   Domains (or "Addon Domains"/"Subdomains" depending on how
   `crawfordinstitute.online` is set up), set the Document Root to the
   repo's `public/` folder, not the repo root. This is the supported,
   tested way to serve the app — everything outside `public/` (app code,
   `.env`, `vendor/`, `database/`) stays unreachable from the web this way.
   - If the hosting plan does **not** allow changing the Document Root
     (some shared/reseller setups don't), fall back to the root-level
     `.htaccess` already in this repo, which rewrites requests into
     `public/` and blocks direct access to sensitive folders. Test this
     fallback carefully before relying on it — it has not been verified
     against a real Apache/cPanel config, only written defensively.

3. **Create the MySQL database and user** via cPanel → MySQL Databases.
   Note the generated DB name/user (cPanel usually prefixes them with the
   account username, e.g. `cpiuser_cpi_live`).

4. **Import the schema, then the seed data, then the course catalogue**, in
   that order:
   ```bash
   mysql -u <cpanel_db_user> -p <cpanel_db_name> < database/schema.sql
   mysql -u <cpanel_db_user> -p <cpanel_db_name> < database/seed.sql
   mysql -u <cpanel_db_user> -p <cpanel_db_name> < database/seed_courses.sql
   ```
   (cPanel → phpMyAdmin's Import tab works too if shell access isn't
   available.) `seed_courses.sql` is the real, client-supplied course
   catalogue (184 courses) — see `docs/content/README.md` for where it came
   from and how to regenerate it when the client sends an updated list.

5. **Create `.env`** in the repo root (copy `.env.example`) and fill in:
   - `APP_URL=https://crawfordinstitute.online`, `APP_DEBUG=false`
   - `DB_*` — the database created in step 3
   - `MAIL_*` — either real SMTP creds, or leave `MAIL_HOST` empty to use
     PHP's `mail()` (cPanel's shared mail usually works out of the box,
     but check the domain's SPF/DKIM records so mail doesn't land in
     spam — CPI's admissions/payment/certificate emails matter)
   - `AT_*` — Africa's Talking SMS creds, if/when SMS is wanted; safe to
     leave blank (SMS sending just no-ops/logs)
   - `FLW_*` — **live** Flutterwave keys (not test keys) from the
     Flutterwave dashboard. `FLW_SECRET_HASH` must match the webhook
     secret hash configured in Flutterwave's dashboard for the webhook
     URL in the next step.

6. **Register the Flutterwave webhook URL**: in the Flutterwave
   dashboard, set the webhook to
   `https://crawfordinstitute.online/webhooks/flutterwave` (see
   `routes/web.php` for the current route if this changes) with the same
   secret hash as `FLW_SECRET_HASH` in `.env`.

7. **File permissions.** `storage/` (and its subfolders:
   `uploads/`, `uploads/certificates/`, `uploads/payment-proofs/`,
   `cache/`, `logs/`) must be writable by the PHP process (typically the
   cPanel account's user — usually fine by default on shared hosting,
   but verify with a test upload before declaring done).

8. **Change the seeded super admin password immediately.** Default
   credentials from `database/seed.sql` are
   `admin@crawfordinstitute.online` / `ChangeMe123!` — log in once,
   change the password via the admin profile page, and consider changing
   the email too if it shouldn't be the literal string "admin@...".

9. **Verify PHP version** on the hosting account is 8.1+ (cPanel → MultiPHP
   Manager). The app uses `str_starts_with()`, constructor property
   promotion in a few places, and match expressions — anything older than
   8.1 in some spots will break.

10. **Smoke test on the live domain** before calling it done — at minimum:
    register → enrol in a free/test-priced course → pay via bank transfer
    → admin confirms payment → learner reaches the course room; and
    separately, a real Flutterwave test transaction if Flutterwave
    provides a sandbox/live-test mode, to confirm the webhook fires and
    `payments.status` updates. See the full checklist this repo was
    smoke-tested against, below.

## What has already been tested (locally, against MariaDB + `php -S`)

All of the following were exercised end-to-end with real HTTP requests
against a local dev server and verified against the database — not just
read through:

- Register → self-enrol in a short course → pay by manual bank transfer
  (proof upload) → admin confirms payment → learner reaches the course
  room.
- Lecturer: create a quiz, add a question with options (including
  `&`-containing text, to catch encoding bugs), grade an assignment
  submission.
- Learner: take a quiz (auto-graded on submit), submit an assignment.
- Certificate issuance (admin → roster → issue) → PDF generated with a
  QR code → learner downloads it → public `/verify/{code}` shows it as
  valid, with the correct name/programme.
- Corporate: public "request custom training" form → admin converts the
  request into a dedicated organization + cohort (intake) → admin sets a
  portal contact and bulk-enrols staff (pasted-text roster) → the
  corporate contact logs in and sees their cohorts/invoices in
  `/corporate/portal`.
- Private academic system: admin creates a programme (reachable only via
  the unlisted `/academic` link) → public applicant applies → admin
  admits them into an intake → an account + `pending_payment` enrollment
  is created and the applicant is emailed.
- Real content import: `schema.sql` → `seed.sql` → `seed_courses.sql`
  imported into a **freshly created** database with zero errors; the
  public `/courses` catalogue lists all 184 real courses, `/corporate-
  training` lists the 137 corporate-catalogue ones, a course detail page
  renders the correct real price/award, and `/about` and the homepage
  render the client's real About/Why-Choose/welcome copy.

**Not yet tested locally** (do before/while doing final touches):
Flutterwave online payment end-to-end (card/Mobile Money) including the
signed webhook — this needs live or sandbox Flutterwave keys, which
weren't available in the dev sandbox; and real SMTP/SMS delivery (the dev
environment had no `sendmail` binary, so mail silently no-ops rather than
sending — confirm this is not still the case once deployed).

## Known quirks worth knowing before changing code

- **No Composer.** Do not run `composer install`/`require` — there is no
  `composer.json`, and packagist.org is unreachable from some sandboxed
  build environments. Third-party code (FPDF under `vendor/fpdf/`, a
  QR-code generator under `vendor/qrcode/`) is vendored as flat files. If
  you need another small library, prefer vendoring it the same way
  (fetch the raw source file(s) and drop them under `vendor/<name>/`)
  rather than introducing a Composer dependency, to keep the zero-build
  deploy story intact.

- **View files execute in the global PHP namespace**, regardless of the
  namespace of the controller/layout that included them. Any view that
  calls a namespaced class unqualified (e.g. `Auth::check()`,
  `View::partial()`) needs its own `use App\Core\Auth;` (etc.) at the top,
  or must fully-qualify the call (`\App\Core\Auth::check()`). Most views
  in this codebase use the fully-qualified form to sidestep this
  entirely; a few (e.g. `resources/views/layouts/dashboard.php`) use a
  local `use` import instead. If you add a new view that references a
  namespaced class, remember this.

- **`Model::count()`/`Model::insert()`/`Model::update()` always act on
  `static::table()`** — i.e. the calling model's own table. Two real bugs
  were found and fixed during development from exactly this mistake:
  code in `IntakeController::addQuestion()` and `Quiz::attemptsUsed()`
  called `Quiz::count('quiz_id = ?', ...)`, which queries the `quizzes`
  table (no `quiz_id` column there — that FK lives on `quiz_questions`/
  `quiz_attempts`), not the intended child table. Similarly
  `CourseRoomController::startQuiz()` called `Quiz::insert([...])` with
  columns belonging to `quiz_attempts`, not `quizzes`. **If you use a
  model's `insert()`/`update()`/`count()` helper, the columns must belong
  to that model's own table** — for a child/related table, use
  `Model::statement()`/`Model::query()` with a raw SQL string instead (as
  the rest of the codebase does for `quiz_attempts`, `quiz_answers`,
  `quiz_options`, `assignment_submissions`, etc.). Worth a quick
  `grep -rn "::count(\|::insert(\|::update(" app/` scan after any schema
  change, to catch new instances of this pattern early.

- **Dev-server-only static passthrough** in `public/index.php`
  (`PHP_SAPI === 'cli-server'` check) only matters for local
  `php -S` testing — Apache/cPanel serves static files from `public/`
  directly via normal `.htaccess`/mod_rewrite, so this code path is
  inert in production and doesn't need touching.

## Repo hygiene

`.env` is gitignored — never commit real credentials. `storage/uploads/`,
`storage/cache/`, `storage/logs/` are gitignored except for `.gitkeep`
placeholders that preserve the folder structure; the live server will
accumulate real uploads/certs/logs there, which is expected and should
stay untracked.
