USE mysql;
CREATE DATABASE db_event;
GRANT ALL ON db_event.* TO 'user_event'@'%';
USE db_event;

CREATE TABLE event (
  id         BIGINT        NOT NULL PRIMARY KEY AUTO_INCREMENT,
  target_id  BIGINT,
  target_role TINYINT NOT NULL DEFAULT 4,
  message    VARCHAR(2048) NOT NULL,
  created_at TIMESTAMP(3)  NOT NULL             DEFAULT CURRENT_TIMESTAMP(3),
  type       CHAR(1)       NOT NULL             DEFAULT 'c',
  INDEX(id, target_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;
