# Crawford Professionals Institute (CPI) — Web Platform

Public website, corporate training catalogue/request pipeline, and three portals
(Learner, Lecturer, Admin) plus a private, unlisted Academic System for
certificate/diploma/degree programmes — for **Crawford Professionals Institute
Limited**, Kampala, Uganda. Production domain: `crawfordinstitute.online`.

Built as a small, dependency-free PHP MVC application — no Composer, no
build step. Everything the app needs at runtime is either PHP's standard
library or vendored as flat files under `vendor/`. This was a deliberate
choice for easy, low-friction deployment on shared cPanel hosting.

## Stack

- PHP 8.1+ (developed/tested on 8.4), PDO/MySQL
- MySQL/MariaDB 10.4+ (InnoDB, utf8mb4)
- Zero Composer dependencies — a hand-rolled `App\` autoloader
  (`app/bootstrap.php`), vendored FPDF (certificates) and a vendored
  pure-PHP QR code generator under `vendor/`
- Flutterwave (Standard v3, Mobile Money + cards) for online payments, plus
  manual bank-transfer with proof upload
- Hand-rolled SMTP client (falls back to PHP `mail()` if unconfigured) and
  Africa's Talking SMS (optional)
- No JS framework/build step — plain CSS + vanilla JS

## Directory layout

```
app/                  MVC application code (App\ namespace, PSR-4-ish)
  Core/                Router, Request, Model, Auth, Session, Database, View...
  Controllers/         Public, Auth, Learner, Lecturer, Admin, Corporate, Academic
  Models/              One class per table (active-record-ish, PDO prepared stmts)
  Support/             Helpers: Str, Upload, CertificatePdf, QrCode, Mailer, Sms...
config/                (reserved; unused at present — settings live in .env + DB)
database/
  schema.sql           Full schema (37 tables), import this first
  seed.sql              Roles/permissions, pillars, default super_admin, settings
  seed_courses.sql      Real course catalogue (184 courses, client-supplied) — import after seed.sql
  tools/import_courses.php  Regenerates seed_courses.sql from an updated course list (see docs/content/README.md)
docs/content/           Client-supplied source content (About/Why-Choose copy, welcome
                         message, course spreadsheets) and where each piece ended up
docs/
  SPEC.md              Original functional specification
public/                Web root — point the domain's Document Root here
  index.php            Front controller
  assets/              CSS, images (logo, favicons)
resources/views/       PHP view templates, one subtree per portal + layouts/partials
routes/
  web.php              Public site + auth routes
  portals.php          Learner/Lecturer/Admin/Corporate/Academic portal routes
storage/                Runtime output — gitignored except folder structure
  uploads/              Certificates, payment proofs, materials, submissions...
  cache/, logs/
vendor/                 Vendored third-party libs (FPDF, QR generator) — flat files
.env.example            Copy to .env and fill in for each environment
.htaccess               Root-level fallback rewrite (see Deployment below)
```

## Local setup

```bash
cp .env.example .env        # then edit DB_*, APP_URL, etc.
mysql -u root -p -e "CREATE DATABASE cpi_dev CHARACTER SET utf8mb4"
mysql -u root -p cpi_dev < database/schema.sql
mysql -u root -p cpi_dev < database/seed.sql
mysql -u root -p cpi_dev < database/seed_courses.sql
cd public && php -S 127.0.0.1:8888
```

Default seeded super admin: `admin@crawfordinstitute.online` /
`ChangeMe123!` — **change this password immediately** after first login on
any environment that isn't purely local/throwaway.

## Roles

`super_admin`, `admissions`, `finance`, `registrar`, `content_manager`,
`lecturer`, `corporate_contact`, `learner`. See `database/seed.sql` for the
role → permission mapping and `app/Core/Auth.php::homeFor()` for where each
role lands after login.

## Deployment

See **CLAUDE.md** for the full cPanel deployment runbook.
