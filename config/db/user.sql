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
  confirmed          BOOLEAN                 NOT NULL        DEFAULT FALSE,
  INDEX (id, email)
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;

INSERT INTO db_user.user (email, hashed_password, role, confirmation_token, confirmed)
VALUES ('taskboards@taskboards.top', '$2y$10$qImYB/mzxcQhXWI1r.y/te2sPtjwEVvuCH/iDVmZ17TL3717ClAyu', 1, -1, TRUE);
