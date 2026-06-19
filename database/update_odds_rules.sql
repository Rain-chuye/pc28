-- Update Odds for 2.8 and 2.0 rooms
DELETE FROM odds_config;

INSERT INTO odds_config (play_type, odds_low, odds_high) VALUES
('big', 2.00, 2.80),
('small', 2.00, 2.80),
('single', 2.00, 2.80),
('double', 2.00, 2.80),
('small_single', 4.60, 6.00),
('big_double', 4.60, 6.00),
('small_double', 4.20, 6.00),
('big_single', 4.20, 6.00),
('extreme_big', 12.00, 12.00),
('extreme_small', 12.00, 12.00),
('pair', 3.00, 3.00),
('straight', 12.00, 12.00),
('triple', 60.00, 60.00),
('banker', 2.00, 2.00),
('player', 2.00, 2.00),
('tie', 9.00, 9.00);

-- Special handling for numbers (特码) defaults to 12.0 as per common practice if not specified, but user said "所有特码" can be modified.
-- For now, let's seed 0-27
INSERT IGNORE INTO odds_config (play_type, odds_low, odds_high)
SELECT CAST(n AS CHAR), 12.0, 12.0 FROM (
    SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION
    SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION
    SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION
    SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27
) numbers;

-- Update System Settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('announcement', '需要上分得先打开加拿大投注页面联系客服 然后发支付宝口令红包 需要你自行去充值页面点充值金额 新人首充20送21 后面是充值10送1 充值20送4 充值30送10 充值40送12 充值50送20 充值100送60')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Ensure users table has necessary columns
ALTER TABLE users ADD COLUMN IF NOT EXISTS first_recharge_done TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS theme_color VARCHAR(20) DEFAULT '#2563eb';
ALTER TABLE users ADD COLUMN IF NOT EXISTS settings_json TEXT;
