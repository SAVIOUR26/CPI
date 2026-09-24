# CLAUDE.md — Handover notes for deploying CPI to cPanel

This file is for whoever (or whichever Claude Code session) takes this
repository from GitHub to the live `crawfordinstitute.online` cPanel
hosting account and does final touches. Read this fully before touching
the server.

## What this app is

A zero-Composer PHP MVC app (see README.md for the stack and layout). It
was built and smoke-tested locally against MariaDB with PHP's built-in
dev server (`php -S`). It has **not** yet been deployed to real cPanel
hosting or tested against SMTP/SMS in live mode — that's the remaining
work for this handover.

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
   - **Later schema changes apply themselves.** Anything added after the
     first import lives in `database/migrations/NNN_*.sql` and is run by
     `App\Core\Migrator` on the first request after a deploy (the live
     site is deployed over FTP by `.github/workflows/deploy.yml`, so there
     is no shell step to run them). Progress is recorded in
     `settings.schema_version` and cached in `storage/cache/schema_version`.
     A failure is logged to the PHP error log and retried after 10 minutes;
     if the DB user lacks `ALTER` rights, run the pending files by hand in
     phpMyAdmin (they are safe to re-run) and the app picks up from there.

5. **Create `.env`** in the repo root (copy `.env.example`) and fill in:
   - `APP_URL=https://crawfordinstitute.online`, `APP_DEBUG=false`
   - `DB_*` — the database created in step 3
   - `MAIL_*` — either real SMTP creds, or leave `MAIL_HOST` empty to use
     PHP's `mail()` (cPanel's shared mail usually works out of the box,
     but check the domain's SPF/DKIM records so mail doesn't land in
     spam — CPI's admissions/payment/certificate emails matter)
   - `AT_*` — Africa's Talking SMS creds, if/when SMS is wanted; safe to
     leave blank (SMS sending just no-ops/logs)
   - There are no payment-gateway keys: any `FLW_*` lines left in an old
     `.env` are unused and can be deleted.

6. **Payments are manual Mobile Money** (the client's instruction, Sep
   2026). Students send the fee to the numbers in
   `App\Support\Institute::MOBILE_MONEY` (registered to CPI's Principal
   Accountant), upload a screenshot under **Fees & Payments**, and Finance
   approves it in **Admin → Payments**; the class opens once the fee is
   fully paid. Give whoever approves payments the Finance role in Users &
   Roles, and make sure `support_email` (settings table) reaches them —
   it gets an email for every upload. There is no online gateway: the
   Flutterwave integration was removed and can be restored from git
   history if card payments are wanted later.

7. **File permissions.** `storage/` (and its subfolders:
   `uploads/`, `uploads/certificates/`, `uploads/payment-proofs/`,
   `cache/`, `logs/`) must be writable by the PHP process (typically the
   cPanel account's user — usually fine by default on shared hosting,
   but verify with a test upload before declaring done).

8. **Change the seeded super admin password immediately.** Default
   credentials from `database/seed.sql` are
   `admin@crawfordinstitute.online` / `ChangeMe123!` — log in once and
   change the password (and the email, if it shouldn't be the literal
   "admin@..." address) under **My account** (`/admin/account`).

9. **Verify PHP version** on the hosting account is 8.1+ (cPanel → MultiPHP
   Manager). The app uses `str_starts_with()`, constructor property
   promotion in a few places, and match expressions — anything older than
   8.1 in some spots will break.

10. **Smoke test on the live domain** before calling it done — at minimum:
    register → enrol in a test-priced course → send a small real Mobile
    Money payment → upload the screenshot → approve it in Admin →
    Payments → the learner reaches the course room. See the full checklist
    this repo was smoke-tested against, below.

## What has already been tested (locally, against MariaDB + `php -S`)

All of the following were exercised end-to-end with real HTTP requests
against a local dev server and verified against the database — not just
read through:

- Register → self-enrol in a short course → pay by Mobile Money
  (screenshot upload) → admin approves the payment → learner reaches the
  course room.
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
- Academic system (public at `/academic`, linked from the Programmes menu,
  homepage and footer; built from the client's documents in
  `docs/content/academic/`): the 19 seeded programmes list by level →
  programme page → 8-step online application with document uploads
  (browser + server validation, including file type/size and a content
  check that a ".pdf" really is a PDF) → printable receipt with an
  application number (`CPI-APP-YYYY-NNNNN`) → admin reviews the full form
  and documents, marks it under review, then admits (account +
  optional `pending_payment` enrolment + offer email) or declines (email).
  Admin can create/edit programmes (awarding body, duration, entry
  requirements, visibility) and drafts stay hidden from the public.
- Portals and accounts: "Student Portal" / "Lecturer Portal" links (top
  bar, phone menu, footer, `/academic`) go to `/student-portal` and
  `/lecturer-portal`, which open the login page on that portal's tab and
  land people in the right portal (or explain why not). An admin creates a
  lecturer in Users & Roles and is shown the temporary password once
  (`App\Support\NewAccounts`, also used when admitting an applicant,
  setting a corporate contact and bulk-enrolling staff), so onboarding
  works even when email doesn't. The lecturer signs in with it and changes
  it under **My account**, which every portal has (`/admin/account`,
  `/lecturer/account`, `/corporate/portal/account`, `/learner/profile`);
  changing the sign-in email there needs the current password.
- The client's portal brief (`docs/content/portals/`, mapped item by item
  in `docs/content/README.md`), run in a browser as admin, lecturer and
  student: admin posts announcements to everyone, students, lecturers or
  one class (Admin → Announcements) and adds key dates and weekly class
  times (Admin → Calendar & Timetable); each lands only with the right
  people (a class awaiting payment, another class's dates and past dates
  stay hidden). The lecturer posts class announcements, adds YouTube and
  Vimeo lectures that play inside the student's class page (`javascript:`
  links and videos without a link are refused), marks attendance, records
  a grade, and grades a submission with feedback (regrading keeps one
  mark). The student sees it all under Announcements, Academic Calendar,
  the class page and **My Results** (quiz auto-graded; average = mean of
  the percentages), and the lecturer's class list shows each student's
  average, attendance and standing. **My Admission** shows an admitted
  student's applications, their own uploaded documents and a printable
  admission letter (one A4 page); another applicant's letter or documents
  are 404. Applying while signed in links the application to the account
  only when the form's email is the account's own.
- Mobile Money payments, in a browser: a student enrols, sees CPI's
  numbers and the registered name on the payment page, and uploads a
  screenshot with the amount, number and transaction ID (wrong file types,
  fake images and amounts above the balance are refused). Finance approves
  part of the fee (the amount can be corrected), the student sees the
  balance, pays the rest and is approved again — the class opens only
  then. A declined payment shows its reason to the student. Admitting an
  academic applicant with an agreed fee bills it (or Admissions bills it
  afterwards) and the student pays it the same way. Students can't open
  each other's invoices or the admin screenshots.
- Real content import: `schema.sql` → `seed.sql` → `seed_courses.sql`
  imported into a **freshly created** database with zero errors; the
  public `/courses` catalogue lists all 184 real courses, `/corporate-
  training` lists the 137 corporate-catalogue ones, a course detail page
  renders the correct real price/award, and `/about` and the homepage
  render the client's real About/Why-Choose/welcome copy.

**Not yet tested locally** (do before/while doing final touches): real
SMTP/SMS delivery (the dev environment had no `sendmail` binary, so mail
silently no-ops rather than sending — confirm this is not still the case
once deployed). Payment emails (upload, approved, declined) depend on it.

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

- **Upload limits come from PHP.** The application form reads
  `upload_max_filesize`, `post_max_size` and `max_file_uploads` and tells
  applicants the real limits (capped at 5MB per file). Default PHP
  settings (2MB / 8MB / 20 files) are tight for scanned certificates —
  raise them in cPanel → MultiPHP INI Editor if applicants struggle.

- **Old form input lasts one request.** `Session::flashInput()` re-fills a
  form after a failed submit and is cleared on the next request (it used
  to linger for the whole session). Passwords and the CSRF token are never
  stored in it.

- **Contact details live in one place.** Location, training coverage,
  phone numbers, WhatsApp, email, the four training modes and the Mobile
  Money payment numbers are constants in `App\Support\Institute`; the top bar, phone menu, footer, Contact and
  About pages, the WhatsApp button, the corporate request form's mode list
  and the homepage's search-engine data (JSON-LD) all read from it.

- **Database times use `APP_TIMEZONE`.** `Database::connection()` sets the
  MySQL session time zone to PHP's offset, so `NOW()`/`CURRENT_TIMESTAMP`
  match PHP's `date()` regardless of the hosting server's own clock.

- **Marks come from one place.** `App\Support\Results` builds a student's
  marks for a class (assignment grades, grades the lecturer records, the
  best submitted attempt at each quiz/exam) and attendance; the class page,
  My Results and the lecturer's class list all use it. Assignment grades
  are stored in `grades` as `component = 'assignment:{id}'`, one row per
  student (regrading replaces it).

- **Lecture videos are links, not uploads.** Shared hosting can't take
  video files, so lecturers paste YouTube (Unlisted), Vimeo or Google
  Drive links; `App\Support\Video::embedUrl()` turns those into player
  URLs and anything else opens in a new tab. Only `http(s)` links are
  accepted or rendered.

- **Application documents are served by `App\Support\ApplicationDocuments`**
  for both the admin review screen and the student's My Admission page. My
  Admission only lists applications whose `user_id` is the signed-in user
  (set when admitted, or when someone applies while signed in with their
  own email) — never matched by email, because registration doesn't
  verify email addresses.

## Repo hygiene

`.env` is gitignored — never commit real credentials. `storage/uploads/`,
`storage/cache/`, `storage/logs/` are gitignored except for `.gitkeep`
placeholders that preserve the folder structure; the live server will
accumulate real uploads/certs/logs there, which is expected and should
stay untracked.
