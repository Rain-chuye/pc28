-- Fix critical status column type mismatch for existing users
ALTER TABLE users MODIFY COLUMN status VARCHAR(20) DEFAULT 'active';

-- Update any existing integer data to strings
UPDATE users SET status = 'active' WHERE status = '1' OR status = 1;
UPDATE users SET status = 'frozen' WHERE status = '0' OR status = 0;
