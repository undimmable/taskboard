USE mysql;
CREATE DATABASE db_account;
GRANT ALL ON db_account.* TO 'user_account'@'%';
USE db_account;

CREATE TABLE account (
  user_id    BIGINT PRIMARY KEY,
  balance    NUMERIC(10, 2) NOT NULL DEFAULT 0,
  last_tx_id BIGINT         NOT NULL DEFAULT -1
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;