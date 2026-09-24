-- 004: Student/lecturer portal content from the client's portal brief
-- (docs/content/portals/): announcements and the academic calendar.
-- Applied automatically by App\Core\Migrator; safe to re-run by hand.

CREATE TABLE IF NOT EXISTS announcements (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id   BIGINT UNSIGNED NULL,          -- NULL = institute-wide
  audience    ENUM('everyone','students','lecturers') NOT NULL DEFAULT 'students',  -- institute-wide only
  title       VARCHAR(190) NOT NULL,
  body        TEXT NOT NULL,
  pinned      TINYINT(1) NOT NULL DEFAULT 0,
  created_by  BIGINT UNSIGNED NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (intake_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS calendar_events (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_id   BIGINT UNSIGNED NULL,          -- NULL = everyone
  category    ENUM('term','exam','holiday','deadline','event') NOT NULL DEFAULT 'event',
  title       VARCHAR(190) NOT NULL,
  starts_on   DATE NOT NULL,
  ends_on     DATE NULL,
  notes       VARCHAR(500) NULL,
  created_by  BIGINT UNSIGNED NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (starts_on)
) ENGINE=InnoDB;
