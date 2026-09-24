# Crawford Professionals Institute (CPI) — Platform Specification

Legal entity: **CRAWFORD PROFESSIONALS INSTITUTE (CPI) LIMITED** (Uganda, URSB-registered).
Domain: **crawfordinstitute.online**
Tagline: *Empowering Skills, Transforming Lives.*

## 1. Product shape

One codebase, one database, one login system. A course belongs to one or more
**pillars** (Professional Training, Capacity Building, Corporate Training) and
one **programme type** (`short_course`, `corporate`, `academic`). The
programme type turns extra features on:

| Feature | short_course | corporate | academic |
|---|---|---|---|
| Public catalogue listing | yes | yes (catalogue) / no (custom) | no (unlisted) |
| Self-enrolment | yes | no (seat-based, via org) | no (apply → admit) |
| Cohorts / intakes | yes | yes | yes (semesters) |
| Certificates | yes | yes | yes (+ transcripts) |
| Timetable | no | no | yes |
| Fee ledger / instalments | no | invoice-based | yes |

## 2. Sitemap

```
/                                   Home
/about                              About CPI
/professional-training              Pillar: Professional Training
/capacity-building                  Pillar: Capacity Building
/corporate-training                 Pillar: Corporate Training (catalogue + request form)
/courses                            Full course catalogue (filterable)
/courses/{slug}                     Course detail + intake dates + apply
/corporate/request                  "Train our staff" request form
/verify                             Public certificate verification
/verify/{code}                      Direct verification result
/contact
/login /register /forgot-password /reset-password
/academic                           Unlisted gateway: programmes + apply (noindex)
/academic/apply/{programme}

--- authenticated ---
/dashboard                          Redirects by role

/learner/...                        Learner portal
/lecturer/...                       Lecturer portal
/corporate/portal/...               Corporate client portal (org admin)
/admin/...                          Admin portal (role-scoped)
/academic/portal/...                Academic student portal (reuses learner UI, academic scope)
```

## 3. Roles

`super_admin`, `admissions`, `finance`, `registrar`, `content_manager`,
`lecturer`, `corporate_contact`, `learner`. A user can hold more than one role
(e.g. a lecturer who is also content_manager). Permissions are role→permission
mappings in the DB (`roles`, `permissions`, `role_permissions`,
`role_user`), checked via `Auth::can('permission.key')`.

Key permissions: `courses.manage`, `intakes.manage`, `enrolments.manage`,
`payments.manage`, `corporate.requests.manage`, `corporate.orgs.manage`,
`admissions.manage`, `academic.manage`, `certificates.issue`, `users.manage`,
`reports.view`, `content.manage`.

## 4. Core flows

**Self-enrolment (short course):** browse catalogue → course page shows next
intake → Enroll → login/register → payment (Mobile Money to CPI's numbers,
then a screenshot uploaded in the Student Portal) → Finance approves it →
once the fee is fully paid, enrolment activates → learner portal access to
that course.

**Corporate request:** organization fills request form (org, contact,
topic/course, headcount, location, mode, dates, budget) → admin reviews,
creates a quote (PDF) and a corporate order → client accepts → invoice →
payment (Mobile Money/card/bank transfer) or per-seat online payment →
admin bulk-enrols staff (CSV or manual) into a dedicated cohort → corporate
contact gets a portal login to track progress and download a completion
report.

**Academic admissions:** applicant finds `/academic` only via direct link →
selects programme → application form + document upload → admissions review
→ admit/reject → on admit, applicant pays (full or instalment per fee
ledger) → account created → academic learning system access.

**Teaching (all systems):** lecturer logs in → sees assigned
courses/cohorts → uploads notes/videos (links for video) → creates
assignments/quizzes/exams → grades → marks attendance → views/exports
reports → messages learners.

**Certification:** on course/programme completion (all required
assessments passed + attendance threshold met), admin/lecturer triggers
certificate issuance → PDF generated with a unique code + QR → publicly
verifiable at `/verify/{code}`.

## 5. Payments

- Mobile Money (Airtel / MTN), manual: students send the fee to CPI's
  numbers (registered to the Principal Accountant), upload a screenshot of
  the confirmation with the amount, number and transaction ID, and Finance
  approves or declines it in Admin → Payments. Approving can record less
  than the student entered; declining emails the reason. On the client's
  instruction (Sep 2026) there is no online gateway: the earlier
  Flutterwave integration was removed and can be restored from git history
  if they want card payments later.
- Academic programmes: fees are agreed at admission; Admissions enters the
  fee when admitting (or bills it later) and the student pays it the same way.
- Corporate: invoice-based, part-payment allowed, tracked in `invoices` +
  `invoice_payments`.
- Academic: `fee_ledger` per student per academic term, with instalment
  rules (a hold flag blocks exam/certificate release until cleared, unless
  overridden by Finance).

## 6. Notifications

Email (PHPMailer/SMTP) for: registration, enrolment confirmation, payment
receipt, admissions decision, results published, certificate issued. SMS
(Africa's Talking) for the same events where a phone number exists, kept
short. A `notifications_log` table records every send attempt.

## 7. Security

Prepared statements everywhere (PDO), CSRF tokens on every state-changing
form, password hashing via `password_hash` (bcrypt/argon2id), role-based
route guards, login throttling, session regeneration on login/privilege
change, uploads stored outside the web root and served through a
controller that checks ownership/role, audit log on sensitive actions
(grade changes, certificate issuance, payment confirmation, user role
changes).

## 8. Build phases

1. **Foundation + public site + payments + learner portal + certificates.**
2. **Lecturer portal + corporate portal + finance/admissions workflows.**
3. **Private academic system** (admissions, timetable, transcripts, fee
   ledger) — reuses the phase-1/2 engine with `programme_type = academic`.

## 9. Design

Palette from the CPI crest: crimson `#7A1010` (primary / actions), near-black
`#141414` (text, header), gold `#C9A227` (accents, certificates/awards only),
white/off-white surfaces. Headline serif (e.g. "Merriweather" / "Playfair
Display") + a clean sans body (e.g. "Inter"). Crest used as a favicon/seal on
certificates; a simpler horizontal crane+wordmark lockup for the navbar.
