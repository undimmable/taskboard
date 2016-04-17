USE mysql;
CREATE DATABASE db_account;
GRANT ALL ON db_account.* TO 'user_account'@'%';
USE db_account;
CREATE TABLE account (
  id            BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id       BIGINT                  NOT NULL,
  created_at    TIMESTAMP DEFAULT now() NOT NULL,
  updated_at    TIMESTAMP,
  amount        INTEGER,
  locked_amount INTEGER
)
  ENGINE = InnoDB;