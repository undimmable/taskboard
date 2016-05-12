USE mysql;
CREATE DATABASE db_text_idx;
GRANT ALL ON db_text_idx.* TO 'user_text_idx'@'%';
USE db_text_idx;

CREATE TABLE text_idx (
  FTS_DOC_ID  BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
  entity_type VARCHAR(10)                    NOT NULL,
  entity_id   BIGINT                         NOT NULL,
  text_val    TEXT                           NOT NULL,
  FULLTEXT idx (text_val)
)
  ENGINE = InnoDB;