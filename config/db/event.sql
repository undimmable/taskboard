USE mysql;
CREATE DATABASE db_event;
GRANT ALL ON db_event.* TO 'user_event'@'%';
USE db_event;

CREATE TABLE event (
  id         BIGINT        NOT NULL PRIMARY KEY AUTO_INCREMENT,
  target_id  BIGINT        NOT NULL,
  message    VARCHAR(2048) NOT NULL,
  created_at TIMESTAMP(3)  NOT NULL             DEFAULT CURRENT_TIMESTAMP(3),
  type       CHAR(1)       NOT NULL             DEFAULT 'c'
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;