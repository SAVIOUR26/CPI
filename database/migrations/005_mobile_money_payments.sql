-- 005: Manual Mobile Money payments (the client's instruction, Sep 2026).
-- Students send Mobile Money to CPI's numbers, upload the screenshot in the
-- Student Portal and Finance approves it in Admin → Payments. Online card /
-- Flutterwave payments were removed; 'flutterwave' and 'bank_transfer' stay
-- in the list so older payment rows keep their method.
-- Applied automatically by App\Core\Migrator; safe to re-run by hand.

ALTER TABLE payments MODIFY method ENUM('flutterwave','bank_transfer','mobile_money','cash','other') NOT NULL;
ALTER TABLE payments ADD COLUMN payer_phone VARCHAR(40) NULL AFTER provider_ref;
ALTER TABLE payments ADD COLUMN review_note VARCHAR(255) NULL AFTER confirmed_by;
