-- Support multi-media chat
ALTER TABLE chat_messages ADD COLUMN type VARCHAR(10) DEFAULT 'text' AFTER message;
ALTER TABLE chat_messages MODIFY COLUMN message LONGTEXT;

-- Support freezing/deleting users
ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER role;

-- Support LONGTEXT for avatars and tokens
ALTER TABLE users MODIFY COLUMN avatar LONGTEXT DEFAULT NULL;
ALTER TABLE finance_requests MODIFY COLUMN proof_img LONGTEXT DEFAULT NULL;
