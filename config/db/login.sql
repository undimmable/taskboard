USE mysql;
CREATE DATABASE db_login;
GRANT ALL ON db_login.* TO 'user_login'@'%';
USE db_login;
CREATE TABLE login (
  id                 BIGINT PRIMARY KEY               AUTO_INCREMENT,
  user_id            BIGINT                  NOT NULL UNIQUE,
  created_at         TIMESTAMP DEFAULT now() NOT NULL,
  user_email         VARCHAR(255)            NOT NULL UNIQUE,
  password           VARCHAR(255)            NOT NULL,
  role               TINYINT                 NOT NULL,
  confirmation_token VARCHAR(255)            NOT NULL,
  confirmed          BOOLEAN                 NOT NULL DEFAULT FALSE
)
ENGINE = InnoDB;