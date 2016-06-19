USE mysql;
CREATE DATABASE db_login;
GRANT ALL ON db_login.* TO 'user_login'@'%';
USE db_login;
CREATE TABLE login (
  id             BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id        BIGINT             NOT NULL DEFAULT -1,
  ip             VARBINARY(16)           NOT NULL,
  user_client    VARCHAR(255)             NOT NULL,
  last_login     TIMESTAMP DEFAULT now() NOT NULL,
  failed_attepts TINYINT            DEFAULT NULL,
  INDEX(user_id, id),
  UNIQUE(ip,user_client,user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;
