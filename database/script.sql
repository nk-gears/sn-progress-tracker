GRANT ALL PRIVILEGES ON `meditation_tracker`.* TO 'mediuser'@'%';

-- Or if connecting from localhost only:
GRANT ALL PRIVILEGES ON `meditation_tracker`.* TO 'mediuser'@'localhost';

-- Apply the changes
FLUSH PRIVILEGES;

-- Verify the user exists and has permissions
SHOW GRANTS FOR 'mediuser'@'%';

-- Exit MySQL
EXIT;