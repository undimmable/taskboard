USE mysql;
CREATE DATABASE db_customer;
GRANT ALL ON db_customer.* TO 'user_customer'@'%';
USE db_customer;
CREATE TABLE customer (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  created_at TIMESTAMP DEFAULT now() NOT NULL,
  updated_at TIMESTAMP
)
  ENGINE = InnoDB;