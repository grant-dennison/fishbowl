CREATE DATABASE intothehat;

# Privileges for `test`@`127.0.0.1`
GRANT USAGE ON *.* TO 'test'@'127.0.0.1' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON `intothehat`.* TO 'test'@'127.0.0.1';

# Privileges for `test`@`localhost`
GRANT USAGE ON *.* TO 'test'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON `intothehat`.* TO 'test'@'localhost';

