ALTER TABLE group_messages ADD COLUMN room_type VARCHAR(10) DEFAULT 'high' AFTER user_id;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL;
ALTER TABLE bets ADD COLUMN room_type VARCHAR(10) DEFAULT 'high' AFTER odds_type;

-- Ensure indices for chat performance
CREATE INDEX idx_group_msgs_room ON group_messages(room_type);
