create database gatekeeper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gk-user'@'localhost' IDENTIFIED BY 'some-password-here';
grant all on gatekeeper.* to 'gk-user'@'localhost';
flush privileges;