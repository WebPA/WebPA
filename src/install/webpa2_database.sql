-- WebPA 2.0.0.10
--
-- Database definition

--
-- Create database (default name is `webpa`, change as required)
--

CREATE DATABASE webpa DEFAULT CHARACTER SET latin1;

--
-- Create user account and grant privileges (default name is `webpa_user@localhost`, change as required, and change the password to something more secure)
--

GRANT SELECT, INSERT, UPDATE, DELETE ON webpa.* TO webpa_user@localhost IDENTIFIED BY 'password';

--
-- Set database as default schema (use same name as above)
--

USE webpa
