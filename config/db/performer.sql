USE mysql;
CREATE DATABASE db_performer;
GRANT ALL ON db_performer.* TO 'user_performer'@'%';
USE db_performer;
CREATE TABLE performer (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  created_at TIMESTAMP DEFAULT now() NOT NULL,
  updated_at TIMESTAMP
)
  ENGINE = InnoDB;