USE mysql;
CREATE DATABASE db_account;
GRANT ALL ON db_account.* TO 'user_account'@'%';
USE db_account;

CREATE TABLE account (
  user_id        BIGINT         NOT NULL PRIMARY KEY,
  balance        NUMERIC(10, 2) NOT NULL DEFAULT 0,
  locked_balance NUMERIC(10, 2) NOT NULL DEFAULT 0,
  last_tx_id     BIGINT         NOT NULL DEFAULT -1
)
  ENGINE = InnoDB
  DEFAULT CHARSET UTF8;

INSERT INTO db_account.account (user_id, balance, locked_balance, last_tx_id) VALUES (1, 0, 0, -1);
