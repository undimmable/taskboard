USE mysql;
CREATE DATABASE db_task;
GRANT ALL ON db_task.* TO 'user_task'@'%';
USE db_task;
CREATE TABLE task (
  id           BIGINT PRIMARY KEY AUTO_INCREMENT,
  created_at   TIMESTAMP DEFAULT now() NOT NULL,
  customer_id  BIGINT                  NOT NULL,
  performer_id BIGINT             DEFAULT NULL,
  amount       NUMERIC(10, 2)          NOT NULL,
  description  MEDIUMTEXT              NOT NULL,
  deleted      BOOL DEFAULT FALSE      NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;