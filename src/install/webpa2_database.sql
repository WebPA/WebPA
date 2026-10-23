-- WebPA 2.0.0.10
--
-- Database definition
--
-- Create database (default name is `webpa`, change as required)

CREATE DATABASE IF NOT EXISTS `webpa` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user account and grant privileges (MySQL 8.0+ compatible)
CREATE USER IF NOT EXISTS 'webpa'@'%' IDENTIFIED BY 'webpa_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON `webpa`.* TO 'webpa'@'%';
FLUSH PRIVILEGES;

-- Set database as default schema (use same name as above)

USE `webpa`;
