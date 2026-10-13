-- Run once on existing u0216646_sat_testdrive database in phpMyAdmin.
ALTER TABLE registrations ADD COLUMN axles TINYINT UNSIGNED NULL AFTER model;
-- Existing registrations, if any, need their axles set before making this field NOT NULL.
-- For a new, empty table use: ALTER TABLE registrations MODIFY axles TINYINT UNSIGNED NOT NULL;
