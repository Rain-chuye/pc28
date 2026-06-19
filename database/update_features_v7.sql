-- PC28 Feature Update v7 (MySQL 5.6+)

-- 1. User Nickname
ALTER TABLE users ADD COLUMN nickname VARCHAR(50) DEFAULT NULL AFTER username;

-- 2. Finance Refusal Reason
ALTER TABLE finance_requests ADD COLUMN refusal_reason VARCHAR(255) DEFAULT NULL AFTER status;

-- 3. Bot Rules Table
CREATE TABLE IF NOT EXISTS bot_rules (
    id INT(11) NOT NULL AUTO_INCREMENT,
    keyword VARCHAR(100) DEFAULT NULL, -- NULL means general auto-reply
    response TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Chat/Lottery Settings
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES
('chat_mute_all', '0'),
('custom_draw_interval', '300'), -- Seconds
('bot_auto_reply_enabled', '1');

-- Ensure utf8mb4 for chat
ALTER TABLE group_messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE chat_messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
