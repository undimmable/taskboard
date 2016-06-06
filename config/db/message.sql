USE mysql;
CREATE DATABASE db_message;
GRANT ALL ON db_message.* TO 'user_message'@'%';
USE db_message;

CREATE TABLE message (
  id      BIGINT PRIMARY KEY AUTO_INCREMENT,
  message VARCHAR(31) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;
