USE mysql;
CREATE DATABASE db_login;
GRANT ALL ON db_login.* TO 'user_login'@'%';
USE db_login;
CREATE TABLE login (
  id             BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id        BIGINT             DEFAULT NULL,
  ip             VARBINARY(16)           NOT NULL,
  user_client    VARCHAR(31)             NOT NULL,
  created_at     TIMESTAMP DEFAULT now() NOT NULL,
  failed_attepts TINYINT            DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;