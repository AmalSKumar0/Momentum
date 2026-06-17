-- Migration script for Momentum RPG features

-- 1. Modify users table
ALTER TABLE users CHANGE COLUMN xp hp INT(5) NOT NULL DEFAULT 100;
ALTER TABLE users ADD COLUMN xp INT(11) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN level INT(11) NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN daily_streak INT(11) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN last_active DATE DEFAULT NULL;

-- Cap existing users' hp to 100 (since some had higher values in old xp column)
UPDATE users SET hp = 100 WHERE hp > 100;

-- 2. Modify habits table
ALTER TABLE habits ADD COLUMN streak INT(11) NOT NULL DEFAULT 0;
ALTER TABLE habits ADD COLUMN last_completed DATE DEFAULT NULL;

-- 3. Modify shop table
ALTER TABLE shop ADD COLUMN type ENUM('potion', 'scroll', 'gear') DEFAULT 'potion';
ALTER TABLE shop ADD COLUMN description VARCHAR(255) DEFAULT '';

-- 4. Modify customshop table
ALTER TABLE customshop ADD COLUMN type ENUM('potion', 'scroll', 'gear', 'custom') DEFAULT 'custom';
ALTER TABLE customshop ADD COLUMN description VARCHAR(255) DEFAULT '';

-- 5. Seed standard shop items
DELETE FROM shop;
INSERT INTO shop (title, difficulty, xp_reward, gold_cost, type, description) VALUES 
('Minor Health Potion', 'easy', 25, 50, 'potion', 'Restores 25 Health Points (HP).'),
('Major Health Potion', 'medium', 50, 90, 'potion', 'Restores 50 Health Points (HP).'),
('Elixir of Life', 'hard', 100, 150, 'potion', 'Fully restores Health Points (HP) to 100.'),
('Scroll of Wisdom', 'medium', 100, 80, 'scroll', 'Grants 100 Experience Points (XP) immediately.');
