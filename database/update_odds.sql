-- Delete old config and insert new specific odds
DELETE FROM odds_config;

-- Core play types
INSERT INTO odds_config (play_type, odds_low, odds_high) VALUES
('big', 2.00, 1.95),
('small', 2.00, 1.95),
('single', 2.00, 1.95),
('double', 2.00, 1.95),
('triple', 66.00, 66.00),
('straight', 10.00, 10.00),
('pair', 3.00, 3.00),
('banker', 3.00, 3.00),
('player', 3.00, 3.00),
('tie', 10.00, 10.00);

-- Numbers with specific odds
INSERT INTO odds_config (play_type, odds_low, odds_high) VALUES
('0', 888.00, 888.00),
('1', 488.00, 488.00),
('2', 288.00, 288.00),
('3', 45.00, 45.00),
('4', 35.00, 35.00),
('27', 888.00, 888.00);

-- Other numbers default to 12
-- (This part will be handled by logic or explicit inserts for 5-26)
