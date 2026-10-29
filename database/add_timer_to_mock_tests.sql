-- Add duration and started_at to mock_tests table
ALTER TABLE mock_tests ADD COLUMN duration_mins INT DEFAULT 0 AFTER single_attempt;
ALTER TABLE mock_tests ADD COLUMN started_at DATETIME NULL AFTER duration_mins;
