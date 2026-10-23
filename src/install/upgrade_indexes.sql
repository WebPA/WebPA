-- WebPA 3.8.0-RC4 — Performance Index Upgrade
-- Run this to add missing indexes for better query performance

-- 1. Assessment lookups by module (frequently JOINed in assessment queries)
ALTER TABLE pa2_assessment ADD INDEX idx_assessment_module (module_id);

-- 2. Assessment lookups by collection (frequently JOINed)
ALTER TABLE pa2_assessment ADD INDEX idx_assessment_collection (collection_id);

-- 3. Collection lookups by module (frequently JOINed)
ALTER TABLE pa2_collection ADD INDEX idx_collection_module (module_id);

-- 4. User email lookup (login/auth queries)
ALTER TABLE pa2_user ADD INDEX idx_user_email (email);

-- 5. User disabled status (WHERE disabled = 0 queries)
ALTER TABLE pa2_user ADD INDEX idx_user_disabled (disabled);

-- 6. User last_module_id (session restoration queries)
ALTER TABLE pa2_user ADD INDEX idx_user_last_module (last_module_id);

-- 7. User_module by module (frequently queried for user module assignments)
ALTER TABLE pa2_user_module ADD INDEX idx_user_module_module (module_id);

-- 8. User tracking by datetime (date range queries for reports)
ALTER TABLE pa2_user_tracking ADD INDEX idx_tracking_datetime (datetime);

-- 9. User tracking by module (module-specific tracking reports)
ALTER TABLE pa2_user_tracking ADD INDEX idx_tracking_module (module_id);

-- 10. User group member by group (frequently JOINed)
ALTER TABLE pa2_user_group_member ADD INDEX idx_group_member_group (group_id);

-- 11. Form lookups by form_id (already has PK, but ensure optimal)
-- pa2_form.form_id is already the PRIMARY KEY
