-- 003: The client's training modes on corporate training requests (Sep 2026):
-- adds on-site corporate ("onsite") and customized in-house ("in_house") to
-- online, physical at a training location ("in_person") and hybrid.
-- Applied automatically by App\Core\Migrator; safe to re-run by hand.

ALTER TABLE corporate_requests MODIFY mode ENUM('online','in_person','hybrid','onsite','in_house') NOT NULL DEFAULT 'in_person';
