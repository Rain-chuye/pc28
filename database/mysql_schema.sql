-- PC28 Canada MySQL 5.6 Compatible Schema

CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    status INT(1) DEFAULT 1,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS lottery_results (
    id INT(11) NOT NULL AUTO_INCREMENT,
    issue_no VARCHAR(50) NOT NULL,
    numbers VARCHAR(20) NOT NULL,
    total_sum INT(3) NOT NULL,
    open_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (issue_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS odds_config (
    id INT(11) NOT NULL AUTO_INCREMENT,
    play_type VARCHAR(50) NOT NULL,
    odds DECIMAL(10, 3) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (play_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    issue_no VARCHAR(50) NOT NULL,
    play_type VARCHAR(50) NOT NULL,
    bet_amount DECIMAL(15, 2) NOT NULL,
    odds DECIMAL(10, 3) NOT NULL,
    win_amount DECIMAL(15, 2) DEFAULT 0.00,
    status INT(1) DEFAULT 0, -- 0: pending, 1: win, 2: lose
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (user_id),
    KEY (issue_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed Data
INSERT IGNORE INTO odds_config (play_type, odds) VALUES
('big', 2.000),
('small', 2.000),
('single', 2.000),
('double', 2.000),
('big_single', 3.800),
('big_double', 3.800),
('small_single', 3.800),
('small_double', 3.800),
('extreme_big', 10.000),
('extreme_small', 10.000);
