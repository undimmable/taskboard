USE mysql;
CREATE DATABASE db_tx;
GRANT ALL ON db_tx.* TO 'user_tx'@'%';
USE db_tx;

CREATE TABLE tx (
  id           BIGINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
  id_from BIGINT                            NOT NULL,
  id_to   BIGINT                            NOT NULL,
  amount       NUMERIC(10, 2)                    NOT NULL,
  type         CHAR(1)                           NOT NULL DEFAULT 'p',
  processed    BOOL                              NOT NULL DEFAULT FALSE,
  INDEX (id_from, id),
  INDEX (id_to, id),
  INDEX (id_from, id_to, id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;