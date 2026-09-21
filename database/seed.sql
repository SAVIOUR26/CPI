-- Reference data + a first super_admin account.
-- Default admin password is set below as bcrypt hash of "ChangeMe123!" — CHANGE IT immediately after first login.

SET NAMES utf8mb4;

INSERT INTO roles (slug, name) VALUES
  ('super_admin','Super Administrator'),
  ('admissions','Admissions Officer'),
  ('finance','Finance Officer'),
  ('registrar','Registrar'),
  ('content_manager','Content Manager'),
  ('lecturer','Lecturer'),
  ('corporate_contact','Corporate Contact'),
  ('learner','Learner')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO permissions (slug, name) VALUES
  ('courses.manage','Manage courses & intakes'),
  ('enrolments.manage','Manage enrolments'),
  ('payments.manage','Confirm & manage payments'),
  ('corporate.requests.manage','Handle corporate training requests'),
  ('corporate.orgs.manage','Manage corporate organizations'),
  ('admissions.manage','Review academic applications'),
  ('academic.manage','Manage academic programmes, timetable, fee ledger'),
  ('certificates.issue','Issue & revoke certificates'),
  ('users.manage','Manage users & roles'),
  ('reports.view','View reports & analytics'),
  ('content.manage','Manage public site content'),
  ('teaching.manage','Teach: materials, assignments, quizzes, grading, attendance')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- super_admin gets everything
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON 1=1 WHERE r.slug = 'super_admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON p.slug IN ('admissions.manage','reports.view')
WHERE r.slug = 'admissions'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON p.slug IN ('payments.manage','reports.view')
WHERE r.slug = 'finance'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON p.slug IN ('academic.manage','reports.view','certificates.issue')
WHERE r.slug = 'registrar'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON p.slug IN ('content.manage','courses.manage')
WHERE r.slug = 'content_manager'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
  ON p.slug IN ('teaching.manage','certificates.issue')
WHERE r.slug = 'lecturer'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO pillars (slug, name, description, sort_order) VALUES
  ('professional-training','Professional Training','Short courses that build in-demand professional skills.', 1),
  ('capacity-building','Capacity Building','Programmes for NGOs, government and donor-funded projects to strengthen institutional capacity.', 2),
  ('corporate-training','Corporate Training','Catalogue and customized training delivered directly to organizations.', 3)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Real course categories + the full course catalogue (184 courses, client-supplied)
-- live in database/seed_courses.sql — import that file right after this one.

-- First super admin — email/password should be rotated immediately after go-live.
INSERT INTO users (full_name, email, phone, password_hash, status, email_verified_at)
VALUES ('CPI Administrator', 'admin@crawfordinstitute.online', NULL,
        '$2y$12$rzOPiEFbOGzzSE9WGhHJPe90cjDKjfneaqXxNnfoNbW7WqJu6YiNi', 'active', NOW())
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

INSERT INTO role_user (role_id, user_id)
SELECT r.id, u.id FROM roles r, users u
WHERE r.slug = 'super_admin' AND u.email = 'admin@crawfordinstitute.online'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO settings (`key`, `value`) VALUES
  ('site_name', 'Crawford Professionals Institute (CPI)'),
  ('site_tagline', 'Empowering Skills, Transforming Lives.'),
  ('support_email', 'info@crawfordinstitute.online'),
  ('support_phone', '+256 700 000 000')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
