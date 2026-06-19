-- Update for PC28 Version 5
ALTER TABLE users ADD COLUMN qq_number VARCHAR(20) DEFAULT NULL AFTER role;
ALTER TABLE lottery_results ADD COLUMN next_draw_at DATETIME DEFAULT NULL AFTER open_time;

-- Seed additional odds for Triple, Straight, Pair if missing
INSERT IGNORE INTO odds_config (play_type, odds_low, odds_high) VALUES
('triple', 66.000, 60.000),
('straight', 10.000, 9.000),
('pair', 3.000, 2.800),
('banker', 2.000, 1.950),
('player', 2.000, 1.950),
('tie', 9.000, 8.500);

-- Update for Chat replies
ALTER TABLE chat_messages ADD COLUMN reply_to INT(11) DEFAULT NULL;
