-- noinspection SqlNoDataSourceInspectionForFile

CREATE TABLE pepsicontest_reg_contest_tmp
(
tmp_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
contest_id INT(11) NOT NULL,
user_id INT(11) NOT NULL,
contest_name VARCHAR(255) NOT NULL,
first_name VARCHAR(255) NOT NULL,
last_name VARCHAR(255) NOT NULL,
email VARCHAR(255) NOT NULL,
gender VARCHAR(1) NOT NULL,
postalcode VARCHAR(6) NOT NULL,
province VARCHAR(2) NOT NULL,
city VARCHAR(100) NOT NULL,
language VARCHAR(2) NOT NULL,
regdate VARCHAR(20) NOT NULL,
user_ip VARCHAR(20) NOT NULL,
user_agent VARCHAR(255) NOT NULL,
enterdate date NOT NULL DEFAULT '0000-00-00',
nomember TINYINT(1) DEFAULT 0
);

 ALTER TABLE pepsicontest_reg_contest_tmp CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;