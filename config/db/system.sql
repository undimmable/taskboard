USE mysql;
CREATE DATABASE db_system;
GRANT ALL ON db_system.* TO 'user_system'@'%';
USE db_system;
CREATE TABLE system (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  created_at TIMESTAMP DEFAULT now() NOT NULL,
  updated_at TIMESTAMP
)
  ENGINE = InnoDB;