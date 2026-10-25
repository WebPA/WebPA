-- WebPA 2.0.0.10
-- Database definition
--
-- Create database and user (idempotent for Docker usage)

CREATE DATABASE IF NOT EXISTS webpa DEFAULT CHARACTER SET latin1;

CREATE USER IF NOT EXISTS 'webpa_user'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE ON webpa.* TO 'webpa_user'@'localhost';
FLUSH PRIVILEGES;

-- Set database as default schema
USE webpa;
