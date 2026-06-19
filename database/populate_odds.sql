INSERT INTO odds_config (play_type, odds_low, odds_high) VALUES
('big', 2.0, 1.95), ('small', 2.0, 1.95), ('single', 2.0, 1.95), ('double', 2.0, 1.95),
('big_single', 3.8, 3.6), ('big_double', 3.8, 3.6), ('small_single', 3.8, 3.6), ('small_double', 3.8, 3.6),
('triple', 66.0, 60.0), ('straight', 10.0, 9.0), ('pair', 3.0, 2.8),
('banker', 2.0, 1.95), ('player', 2.0, 1.95), ('tie', 9.0, 8.5)
ON DUPLICATE KEY UPDATE odds_low=VALUES(odds_low), odds_high=VALUES(odds_high);
