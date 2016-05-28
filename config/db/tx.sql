USE mysql;
CREATE DATABASE db_tx;
GRANT ALL ON db_tx.* TO 'user_tx'@'%';
USE db_tx;

CREATE TABLE tx (
  id           BIGINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  from_user_id BIGINT                            NOT NULL,
  to_user_id   BIGINT                            NOT NULL,
  amount       NUMERIC(10, 2)                    NOT NULL,
  type         CHAR(1)                           NOT NULL DEFAULT 'p',
  processed    BOOL                              NOT NULL DEFAULT FALSE,
  INDEX (from_user_id, id),
  INDEX (to_user_id, id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;