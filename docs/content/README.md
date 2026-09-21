# Client-supplied content

Raw source materials the client (Saviour Najuna / CPI) supplied on
2026-09-20, kept here for provenance and future re-use, plus a record of
where each one ended up in the app.

## Source files (`source/`)

| File | Used for |
|---|---|
| `About_Crawford_Professionals_Institute.pdf` | `resources/views/public/about.php` — "About CPI" section |
| `Why_Choose_Crawford_Professionals_Institute.pdf` | `resources/views/public/about.php` — "Why Choose CPI" section, competitive advantages, mission & vision |
| `CPI_WELCOME_MESSAGE.docm` | `resources/views/public/home.php` — "Achieve Your Goals with CPI" homepage section |
| `Short_Professional_Courses.xlsx` | 47 priced short courses (title, category, duration, fee, award) — imported as `programme_type='short_course'` |
| `More_Professional_Training_Courses.xlsx` | 145 additional course titles by category (no pricing) — imported as `programme_type='corporate'` for the public Corporate Training catalogue |
| `courses_parsed.json` | The two spreadsheets merged and de-duplicated (8 titles appeared in both — the priced version won) into one clean list — the actual input to the importer below |

## Course catalogue import

`database/seed_courses.sql` is generated from `courses_parsed.json` by
`database/tools/import_courses.php` and contains:
- 16 course categories (a merged taxonomy covering both spreadsheets)
- 184 unique courses (184 = 192 raw rows − 8 exact-title duplicates across
  the two files)
- pillar links (professional-training / corporate-training, plus
  capacity-building for NGO/government-relevant categories: MEAL, public
  health, governance, project management, procurement, legal & policy)
- 3 flagship demo intakes so self-enrolment isn't empty on first deploy —
  replace/extend these with real dates via Admin → Courses

Import order for a fresh database:
```bash
mysql -u <user> -p <db> < database/schema.sql
mysql -u <user> -p <db> < database/seed.sql
mysql -u <user> -p <db> < database/seed_courses.sql
```

To regenerate `seed_courses.sql` after the client sends an updated course
list, produce a new JSON in the same shape as `courses_parsed.json` (each
item: `title`, `category_raw`, `duration`, `fee_ugx`, `venue`, `award`,
`programme_type`) and re-run:
```bash
php database/tools/import_courses.php path/to/new_courses.json database/seed_courses.sql
```
It's idempotent (`ON DUPLICATE KEY UPDATE`), so re-running it against a
live database updates existing rows by slug rather than duplicating them.

## Note on `.docm`

The welcome message file is a macro-enabled Word document (`.docm`). No
macros were present or executed — it was read as plain zipped XML (same
format as `.docx`) purely for its text content.
