USE mysql;
CREATE DATABASE db_login;
GRANT ALL ON db_login.* TO 'user_login'@'%';
USE db_login;
CREATE TABLE login (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id    BIGINT                  NOT NULL,
  created_at TIMESTAMP DEFAULT now() NOT NULL,
  token      VARCHAR(255)
)
  ENGINE = InnoDB;