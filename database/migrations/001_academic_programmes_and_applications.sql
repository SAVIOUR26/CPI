-- 001: Academic programmes content + full online application form
-- (client documents supplied Sep 2026 — see docs/content/academic/).
-- Applied automatically by App\Core\Migrator; safe to re-run by hand.

ALTER TABLE academic_programmes MODIFY award_level ENUM('certificate','diploma','degree','postgraduate') NOT NULL;
ALTER TABLE academic_programmes ADD COLUMN awarding_body VARCHAR(190) NULL AFTER award_level;
ALTER TABLE academic_programmes ADD COLUMN sort_order INT NOT NULL DEFAULT 0;

ALTER TABLE academic_applications ADD COLUMN application_no VARCHAR(40) NULL AFTER id;
ALTER TABLE academic_applications ADD UNIQUE KEY uq_application_no (application_no);
ALTER TABLE academic_applications ADD COLUMN form_data MEDIUMTEXT NULL;
ALTER TABLE academic_applications ADD COLUMN documents MEDIUMTEXT NULL;
