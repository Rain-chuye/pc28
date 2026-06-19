-- Ensure system settings exist
CREATE TABLE IF NOT EXISTS system_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    PRIMARY KEY (id),
    UNIQUE KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES
('announcement', '欢迎使用 PC28 商业版，祝您游戏愉快！'),
('agent_link_prefix', 'http://154.12.51.237/register.html?ref=');

-- Ensure special betting types exist in odds config
INSERT IGNORE INTO odds_config (play_type, odds_low, odds_high) VALUES
('big_single', 3.80, 3.60),
('big_double', 3.80, 3.60),
('small_single', 3.80, 3.60),
('small_double', 3.80, 3.60),
('triple', 66.00, 60.00),
('straight', 10.00, 9.00),
('pair', 3.00, 2.80);

-- Update users table for agent referral tracking
ALTER TABLE users ADD INDEX idx_inviter (inviter_id);
