-- PC28 Rules Update for MySQL 5.6+ Compatibility

-- 1. Seeding / Updating Odds
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

-- Numbers Seeding
INSERT IGNORE INTO odds_config (play_type, odds_low, odds_high) VALUES
('0', 12.0, 12.0), ('1', 12.0, 12.0), ('2', 12.0, 12.0), ('3', 12.0, 12.0), ('4', 12.0, 12.0), ('5', 12.0, 12.0),
('6', 12.0, 12.0), ('7', 12.0, 12.0), ('8', 12.0, 12.0), ('9', 12.0, 12.0), ('10', 12.0, 12.0), ('11', 12.0, 12.0),
('12', 12.0, 12.0), ('13', 12.0, 12.0), ('14', 12.0, 12.0), ('15', 12.0, 12.0), ('16', 12.0, 12.0), ('17', 12.0, 12.0),
('18', 12.0, 12.0), ('19', 12.0, 12.0), ('20', 12.0, 12.0), ('21', 12.0, 12.0), ('22', 12.0, 12.0), ('23', 12.0, 12.0),
('24', 12.0, 12.0), ('25', 12.0, 12.0), ('26', 12.0, 12.0), ('27', 12.0, 12.0);

-- 2. System Settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('announcement', '需要上分得先打开加拿大投注页面联系客服 然后发支付宝口令红包 需要你自行去充值页面点充值金额 新人首充20送21 后面是充值10送1 充值20送4 充值30送10 充值40送12 充值50送20 充值100送60')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
