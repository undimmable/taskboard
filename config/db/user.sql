USE mysql;
CREATE DATABASE db_user;
GRANT ALL ON db_user.* TO 'user_user'@'%';
USE db_user;
CREATE TABLE user (
  id                 BIGINT PRIMARY KEY UNIQUE               AUTO_INCREMENT,
  created_at         TIMESTAMP DEFAULT now() NOT NULL,
  email              VARCHAR(255)            NOT NULL UNIQUE,
  hashed_password    VARCHAR(255)            NOT NULL,
  role               TINYINT                 NOT NULL,
  confirmation_token VARCHAR(255)            NOT NULL,
  confirmed          BOOLEAN                 NOT NULL        DEFAULT FALSE
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;