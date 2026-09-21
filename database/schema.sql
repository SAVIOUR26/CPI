-- Crawford Professionals Institute (CPI) — full schema
-- MySQL 5.7+/MariaDB 10.3+, InnoDB, utf8mb4
-- Load order matters: this file is written in dependency order, top to bottom.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────────────────
-- 1. ACCESS CONTROL
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name         VARCHAR(150) NOT NULL,
  email             VARCHAR(190) NOT NULL UNIQUE,
  phone             VARCHAR(30) NULL,
  password_hash     VARCHAR(255) NOT NULL,
  status            ENUM('active','suspended','pending') NOT NULL DEFAULT 'active',
  email_verified_at DATETIME NULL,
  last_login_at     DATETIME NULL,
  remember_token    VARCHAR(100) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(190) NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS roles (
  id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
  id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id       BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_user (
  role_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, user_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 2. ORGANIZATIONS (corporate clients)
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS organizations (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(190) NOT NULL,
  sector          VARCHAR(120) NULL,
  address         VARCHAR(255) NULL,
  contact_user_id BIGINT UNSIGNED NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (contact_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS organization_members (
  organization_id BIGINT UNSIGNED NOT NULL,
  user_id         BIGINT UNSIGNED NOT NULL,
  title           VARCHAR(100) NULL,
  PRIMARY KEY (organization_id, user_id),
  FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 3. CATALOGUE: pillars, categories, courses, intakes/cohorts
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS pillars (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(60) NOT NULL UNIQUE,   -- professional-training | capacity-building | corporate-training
  name        VARCHAR(150) NOT NULL,
  description TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_categories (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(80) NOT NULL UNIQUE,   -- health-sector | business-management | technology-ai ...
  name       VARCHAR(150) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id      BIGINT UNSIGNED NULL,
  title            VARCHAR(190) NOT NULL,
  slug             VARCHAR(200) NOT NULL UNIQUE,
  summary          VARCHAR(500) NULL,
  description      MEDIUMTEXT NULL,
  programme_type   ENUM('short_course','corporate','academic') NOT NULL DEFAULT 'short_course',
  level            ENUM('foundation','intermediate','advanced') NOT NULL DEFAULT 'foundation',
  duration_note    VARCHAR(100) NULL,       -- e.g. "6 weeks, weekends"
  price_amount     DECIMAL(12,2) NULL,
  price_currency   VARCHAR(3) NOT NULL DEFAULT 'UGX',
  is_public        TINYINT(1) NOT NULL DEFAULT 1,   -- 0 = unlisted (used for academic + custom corporate)
  status           ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  cover_image_path VARCHAR(255) NULL,
  created_by       BIGINT UNSIGNED NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES course_categories(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (programme_type), INDEX (status), INDEX (is_public)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_pillars (
  course_id BIGINT UNSIGNED NOT NULL,
  pillar_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (course_id, pillar_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (pillar_id) REFERENCES pillars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- an intake/cohort is one running of a course (also used as the "class" for corporate & academic)
CREATE TABLE IF NOT EXISTS intakes (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id         BIGINT UNSIGNED NOT NULL,
  organization_id   BIGINT UNSIGNED NULL,      -- set for a dedicated corporate cohort
  code              VARCHAR(60) NOT NULL,      -- e.g. "MAY-2026-WEEKEND"
  mode              ENUM('online','in_person','hybrid') NOT NULL DEFAULT 'online',
  venue             VARCHAR(190) NULL,
  start_date        DATE NOT NULL,
  end_date          DATE NULL,
  capacity          INT UNSIGNED NULL,
  seats_taken        INT UNSIGNED NOT NULL DEFAULT 0,
  primary_lecturer_id BIGINT UNSIGNED NULL,
  status            ENUM('scheduled','open','closed','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
  FOREIGN KEY (primary_lecturer_id) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY (course_id, code)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS intake_lecturers (
  intake_id  BIGINT UNSIGNED NOT NULL,
  user_id    BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (intake_id, user_id),
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS live_sessions (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id   BIGINT UNSIGNED NOT NULL,
  title       VARCHAR(190) NOT NULL,
  starts_at   DATETIME NOT NULL,
  duration_minutes INT UNSIGNED NOT NULL DEFAULT 60,
  meeting_url VARCHAR(255) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 4. CORPORATE REQUESTS (customized training pipeline)
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS corporate_requests (
  id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_name  VARCHAR(190) NOT NULL,
  contact_name       VARCHAR(150) NOT NULL,
  contact_email      VARCHAR(190) NOT NULL,
  contact_phone      VARCHAR(30) NULL,
  topic              VARCHAR(190) NOT NULL,
  course_id          BIGINT UNSIGNED NULL,
  headcount          INT UNSIGNED NULL,
  location           VARCHAR(190) NULL,
  mode               ENUM('online','in_person','hybrid') NOT NULL DEFAULT 'in_person',
  preferred_dates    VARCHAR(190) NULL,
  budget_note        VARCHAR(190) NULL,
  message            TEXT NULL,
  status             ENUM('new','reviewing','quoted','accepted','declined','converted') NOT NULL DEFAULT 'new',
  quote_pdf_path     VARCHAR(255) NULL,
  organization_id    BIGINT UNSIGNED NULL,
  resulting_intake_id BIGINT UNSIGNED NULL,
  handled_by         BIGINT UNSIGNED NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
  FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
  FOREIGN KEY (resulting_intake_id) REFERENCES intakes(id) ON DELETE SET NULL,
  FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 5. ENROLMENT & PAYMENTS
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS enrollments (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      BIGINT UNSIGNED NOT NULL,
  intake_id    BIGINT UNSIGNED NOT NULL,
  source       ENUM('self','corporate','academic_admission','admin') NOT NULL DEFAULT 'self',
  status       ENUM('pending_payment','active','completed','withdrawn','failed') NOT NULL DEFAULT 'pending_payment',
  progress_pct TINYINT UNSIGNED NOT NULL DEFAULT 0,
  enrolled_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  UNIQUE KEY (user_id, intake_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS invoices (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_number  VARCHAR(40) NOT NULL UNIQUE,
  billable_type   ENUM('enrollment','corporate_request','academic_fee') NOT NULL,
  billable_id     BIGINT UNSIGNED NOT NULL,
  organization_id BIGINT UNSIGNED NULL,
  user_id         BIGINT UNSIGNED NULL,
  amount_total    DECIMAL(12,2) NOT NULL,
  amount_paid     DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency        VARCHAR(3) NOT NULL DEFAULT 'UGX',
  status          ENUM('unpaid','partially_paid','paid','void') NOT NULL DEFAULT 'unpaid',
  due_date        DATE NULL,
  pdf_path        VARCHAR(255) NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (billable_type, billable_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id     BIGINT UNSIGNED NULL,
  user_id        BIGINT UNSIGNED NULL,
  method         ENUM('flutterwave','bank_transfer','cash','other') NOT NULL,
  provider_ref   VARCHAR(190) NULL,           -- Flutterwave tx_ref / transaction id
  amount         DECIMAL(12,2) NOT NULL,
  currency       VARCHAR(3) NOT NULL DEFAULT 'UGX',
  status         ENUM('initiated','successful','failed','pending_review') NOT NULL DEFAULT 'initiated',
  proof_path     VARCHAR(255) NULL,           -- bank transfer proof upload
  confirmed_by   BIGINT UNSIGNED NULL,
  raw_payload    MEDIUMTEXT NULL,             -- webhook/gateway response, for audit
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  confirmed_at   DATETIME NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (provider_ref)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 6. TEACHING: materials, assignments, quizzes, exams, attendance, grades
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS materials (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id   BIGINT UNSIGNED NOT NULL,
  type        ENUM('note','video','link') NOT NULL DEFAULT 'note',
  title       VARCHAR(190) NOT NULL,
  file_path   VARCHAR(255) NULL,     -- for uploaded notes/PDFs
  video_url   VARCHAR(255) NULL,     -- YouTube/Vimeo/Bunny embed link
  body        MEDIUMTEXT NULL,
  uploaded_by BIGINT UNSIGNED NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignments (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id    BIGINT UNSIGNED NOT NULL,
  title        VARCHAR(190) NOT NULL,
  instructions MEDIUMTEXT NULL,
  max_score    DECIMAL(6,2) NOT NULL DEFAULT 100,
  due_at       DATETIME NULL,
  created_by   BIGINT UNSIGNED NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignment_submissions (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assignment_id  BIGINT UNSIGNED NOT NULL,
  user_id        BIGINT UNSIGNED NOT NULL,
  file_path      VARCHAR(255) NULL,
  notes          TEXT NULL,
  submitted_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  score          DECIMAL(6,2) NULL,
  feedback       TEXT NULL,
  graded_by      BIGINT UNSIGNED NULL,
  graded_at      DATETIME NULL,
  UNIQUE KEY (assignment_id, user_id),
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quizzes (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id    BIGINT UNSIGNED NOT NULL,
  title        VARCHAR(190) NOT NULL,
  time_limit_minutes INT UNSIGNED NULL,
  max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 1,
  is_exam      TINYINT(1) NOT NULL DEFAULT 0,   -- 0 = quiz, 1 = formal exam
  available_from DATETIME NULL,
  available_to   DATETIME NULL,
  created_by   BIGINT UNSIGNED NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_questions (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quiz_id    BIGINT UNSIGNED NOT NULL,
  question   TEXT NOT NULL,
  type       ENUM('single','multiple','short_text') NOT NULL DEFAULT 'single',
  points     DECIMAL(6,2) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_options (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id BIGINT UNSIGNED NOT NULL,
  option_text VARCHAR(500) NOT NULL,
  is_correct  TINYINT(1) NOT NULL DEFAULT 0,
  sort_order  INT NOT NULL DEFAULT 0,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_attempts (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quiz_id     BIGINT UNSIGNED NOT NULL,
  user_id     BIGINT UNSIGNED NOT NULL,
  attempt_no  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  started_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  submitted_at DATETIME NULL,
  score       DECIMAL(6,2) NULL,
  max_score   DECIMAL(6,2) NULL,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_answers (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id  BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  option_id   BIGINT UNSIGNED NULL,
  answer_text TEXT NULL,
  is_correct  TINYINT(1) NULL,
  points_awarded DECIMAL(6,2) NULL,
  FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
  FOREIGN KEY (option_id) REFERENCES quiz_options(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id  BIGINT UNSIGNED NOT NULL,
  user_id    BIGINT UNSIGNED NOT NULL,
  session_date DATE NOT NULL,
  status     ENUM('present','absent','excused','late') NOT NULL DEFAULT 'present',
  marked_by  BIGINT UNSIGNED NULL,
  UNIQUE KEY (intake_id, user_id, session_date),
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grades (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id    BIGINT UNSIGNED NOT NULL,
  user_id      BIGINT UNSIGNED NOT NULL,
  component    VARCHAR(100) NOT NULL,     -- 'assignment:12', 'quiz:5', 'final'
  score        DECIMAL(6,2) NOT NULL,
  max_score    DECIMAL(6,2) NOT NULL,
  recorded_by  BIGINT UNSIGNED NULL,
  recorded_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS discussions (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id  BIGINT UNSIGNED NOT NULL,
  user_id    BIGINT UNSIGNED NOT NULL,
  parent_id  BIGINT UNSIGNED NULL,
  body       TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES discussions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 7. CERTIFICATES
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS certificates (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(40) NOT NULL UNIQUE,     -- public verification code, e.g. CPI-2026-000123
  user_id      BIGINT UNSIGNED NOT NULL,
  intake_id    BIGINT UNSIGNED NOT NULL,
  title        VARCHAR(190) NOT NULL,           -- "Certificate in Monitoring & Evaluation"
  issued_at    DATE NOT NULL,
  pdf_path     VARCHAR(255) NULL,
  issued_by    BIGINT UNSIGNED NULL,
  revoked      TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 8. PRIVATE ACADEMIC SYSTEM
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS academic_programmes (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id   BIGINT UNSIGNED NOT NULL,       -- courses row with programme_type = 'academic', is_public = 0
  award_level ENUM('certificate','diploma','degree') NOT NULL,
  duration_note VARCHAR(100) NULL,
  entry_requirements MEDIUMTEXT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS academic_applications (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  programme_id   BIGINT UNSIGNED NOT NULL,
  applicant_name VARCHAR(150) NOT NULL,
  email          VARCHAR(190) NOT NULL,
  phone          VARCHAR(30) NULL,
  documents_path VARCHAR(255) NULL,           -- zipped/uploaded supporting docs
  status         ENUM('submitted','under_review','admitted','rejected') NOT NULL DEFAULT 'submitted',
  decision_note  TEXT NULL,
  reviewed_by    BIGINT UNSIGNED NULL,
  user_id        BIGINT UNSIGNED NULL,        -- set once account is created on admit
  intake_id      BIGINT UNSIGNED NULL,        -- assigned cohort/term once admitted
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decided_at     DATETIME NULL,
  FOREIGN KEY (programme_id) REFERENCES academic_programmes(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS timetable_entries (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id   BIGINT UNSIGNED NOT NULL,
  day_of_week TINYINT UNSIGNED NOT NULL,      -- 1=Mon..7=Sun
  start_time  TIME NOT NULL,
  end_time    TIME NOT NULL,
  venue       VARCHAR(190) NULL,
  lecturer_id BIGINT UNSIGNED NULL,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fee_ledger (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      BIGINT UNSIGNED NOT NULL,
  intake_id    BIGINT UNSIGNED NOT NULL,
  description  VARCHAR(190) NOT NULL,        -- "Tuition — Semester 1"
  amount_due   DECIMAL(12,2) NOT NULL,
  amount_paid  DECIMAL(12,2) NOT NULL DEFAULT 0,
  due_date     DATE NULL,
  hold_release TINYINT(1) NOT NULL DEFAULT 0, -- 1 = Finance cleared despite balance
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transcripts (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED NOT NULL,
  intake_id   BIGINT UNSIGNED NOT NULL,
  final_grade VARCHAR(10) NULL,
  remarks     VARCHAR(190) NULL,
  pdf_path    VARCHAR(255) NULL,
  generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────
-- 9. SYSTEM: notifications, audit log, settings
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS notifications_log (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    BIGINT UNSIGNED NULL,
  channel    ENUM('email','sms') NOT NULL,
  event      VARCHAR(100) NOT NULL,
  recipient  VARCHAR(190) NOT NULL,
  subject    VARCHAR(190) NULL,
  status     ENUM('sent','failed') NOT NULL,
  error      VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED NULL,
  action      VARCHAR(100) NOT NULL,     -- e.g. "certificate.issue", "payment.confirm"
  subject_type VARCHAR(60) NULL,
  subject_id  BIGINT UNSIGNED NULL,
  meta        MEDIUMTEXT NULL,
  ip_address  VARCHAR(45) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (subject_type, subject_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
  `key`   VARCHAR(100) PRIMARY KEY,
  `value` MEDIUMTEXT NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
