-- Fix avatar storage length
ALTER TABLE users MODIFY COLUMN avatar LONGTEXT DEFAULT NULL;

-- Ensure room separation
-- Current room types: 'high', 'low', 'red_packet'
-- The previous migration added room_type to group_messages and bets.
