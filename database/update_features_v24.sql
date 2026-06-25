-- Fix missing columns reported by user
ALTER TABLE users ADD COLUMN last_daily_bonus_at DATE DEFAULT NULL;
ALTER TABLE users MODIFY COLUMN status VARCHAR(20) DEFAULT 'active';

-- Support for clearing private chat
ALTER TABLE chat_messages ADD INDEX idx_chat_receiver (receiver_id);
