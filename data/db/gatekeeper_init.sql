create database gatekeeper;
CREATE USER 'gk-user'@'localhost' IDENTIFIED BY 'some-password-here';
grant all on gatekeeper.* to 'gk-user'@'localhost';
flush privileges;