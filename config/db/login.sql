USE mysql;
CREATE DATABASE db_login;
GRANT ALL ON db_login.* TO 'user_login'@'%';
USE db_login;
CREATE TABLE login (
  user_id     BIGINT PRIMARY KEY,
  ip          VARBINARY(16),
  user_client VARCHAR(31),
  created_at  TIMESTAMP
)
  ENGINE = InnoDB;