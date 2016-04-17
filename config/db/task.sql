USE mysql;
CREATE DATABASE db_task;
GRANT ALL ON db_task.* TO 'user_task'@'%';
USE db_task;
CREATE TABLE task (
  id           BIGINT PRIMARY KEY AUTO_INCREMENT,
  created_at   TIMESTAMP DEFAULT now() NOT NULL,
  updated_at   TIMESTAMP,
  customer_id  BIGINT                  NOT NULL,
  performer_id BIGINT             DEFAULT NULL
)
  ENGINE = InnoDB;