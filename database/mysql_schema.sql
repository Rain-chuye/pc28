-- PC28 Canada MySQL 5.6 Compatible Schema (Enhanced)

CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    status INT(1) DEFAULT 1,
    role VARCHAR(20) DEFAULT 'user',
    inviter_id INT(11) DEFAULT NULL,
    daily_turnover DECIMAL(15, 2) DEFAULT 0.00,
    total_turnover DECIMAL(15, 2) DEFAULT 0.00,
    total_deposit DECIMAL(15, 2) DEFAULT 0.00,
    total_bonus DECIMAL(15, 2) DEFAULT 0.00,
    is_robot TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (username),
    KEY (inviter_id)
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
    odds_low DECIMAL(10, 3) NOT NULL,
    odds_high DECIMAL(10, 3) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (play_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    issue_no VARCHAR(50) NOT NULL,
    play_type VARCHAR(50) NOT NULL,
    odds_type ENUM('low', 'high') DEFAULT 'low',
    bet_amount DECIMAL(15, 2) NOT NULL,
    odds DECIMAL(10, 3) NOT NULL,
    win_amount DECIMAL(15, 2) DEFAULT 0.00,
    status INT(1) DEFAULT 0, -- 0: pending, 1: win, 2: lose, 3: returned (for 13/14 high odds)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (user_id),
    KEY (issue_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (sender_id),
    KEY (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS group_messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    message TEXT,
    type ENUM('text', 'red_packet') DEFAULT 'text',
    packet_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS red_packets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    total_amount DECIMAL(15, 2) NOT NULL,
    total_count INT(11) NOT NULL,
    remaining_amount DECIMAL(15, 2) NOT NULL,
    remaining_count INT(11) NOT NULL,
    min_turnover_req DECIMAL(15, 2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS red_packet_claims (
    id INT(11) NOT NULL AUTO_INCREMENT,
    packet_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    claimed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (packet_id),
    KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS finance_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    type ENUM('deposit', 'withdraw') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    proof_img VARCHAR(255) DEFAULT NULL,
    admin_note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS rebates (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL, -- The one who gets the reward (inviter)
    sub_id INT(11) NOT NULL,  -- The one who triggered it (sub-agent)
    type ENUM('invitation', 'turnover') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed Data (Updated for high/low odds)
INSERT IGNORE INTO odds_config (play_type, odds_low, odds_high) VALUES
('big', 2.000, 1.950),
('small', 2.000, 1.950),
('single', 2.000, 1.950),
('double', 2.000, 1.950),
('big_single', 3.800, 3.600),
('big_double', 3.800, 3.600),
('small_single', 3.800, 3.600),
('small_double', 3.800, 3.600),
('extreme_big', 10.000, 9.500),
('extreme_small', 10.000, 9.500);

-- Demo and Robot Users
INSERT IGNORE INTO users (username, password, balance, role, is_robot) VALUES
('admin', 'admin123', 0.00, 'admin', 0),
('demo_user', '123456', 1000.00, 'user', 0),
('bot_jack', 'bot', 5000.00, 'user', 1),
('bot_lisa', 'bot', 5000.00, 'user', 1),
('bot_mike', 'bot', 5000.00, 'user', 1);

CREATE TABLE IF NOT EXISTS balance_logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    type ENUM('deposit', 'withdraw', 'bet', 'win', 'bonus', 'rebate', 'red_packet') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    balance_before DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (user_id),
    KEY (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
