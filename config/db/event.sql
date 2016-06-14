USE mysql;
CREATE DATABASE db_event;
GRANT ALL ON db_event.* TO 'user_event'@'%';
USE db_event;

CREATE TABLE event (
  id         BIGINT        NOT NULL PRIMARY KEY,
  target_id  BIGINT        NOT NULL,
  message    VARCHAR(2048) NOT NULL,
  created_at TIMESTAMP     NOT NULL DEFAULT NOW()
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;