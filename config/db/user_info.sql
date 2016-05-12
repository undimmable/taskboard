USE mysql;
CREATE DATABASE db_user_info;
GRANT ALL ON db_user_info.* TO 'user_user_info'@'%';
USE db_user_info;
CREATE TABLE user_info (
  user_id    BIGINT PRIMARY KEY UNIQUE,
  first_name VARCHAR(255),
  last_name  VARCHAR(255),
  avatar_url VARCHAR(255)
)
  ENGINE = InnoDB;