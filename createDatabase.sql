DROP DATABASE IF EXISTS gruprDatabase;
CREATE database gruprDatabase;
USE gruprDatabase;

CREATE TABLE user_info
(
user_id int AUTO_INCREMENT,
email varchar(50),
password varchar(255),
salt varchar(255),
first_name varchar(25),
last_name varchar(25),
profile_level float,
session_id varchar(30),
last_login timestamp,
is_teacher tinyint,
is_ta tinyint,
encrypted_password varchar(255),
PRIMARY KEY(user_id)
);

CREATE TABLE teachers
(
user_id int,
class_id int
);

CREATE TABLE students
(
user_id int,
class_id int,
is_active Boolean
);

CREATE TABLE tas
(
user_id int,
class_id int
);


CREATE TABLE locations
(
location_id int auto_increment,
location varchar(30),
PRIMARY KEY(location_id)
);

CREATE TABLE classes
(
class_id int NOT NULL auto_increment,
class_subject varchar(4),
class_number varchar(4),
PRIMARY KEY(class_id)
);

CREATE TABLE groups
(
group_id int NOT NULL auto_increment,
group_name varchar(55),
class_id int,
time_of_meeting datetime,
owner_id int,
description varchar(200),
location_id int,
ta_attending tinyint,
teacher_attending tinyint,
location_details varchar(350),
PRIMARY KEY(group_id),
FOREIGN KEY(owner_id)
	REFERENCES user_info(user_id),
FOREIGN KEY(class_id)
	REFERENCES classes(class_id),
FOREIGN KEY(location_id)
	REFERENCES locations(location_id)
);


CREATE TABLE members
(
member_id int NOT NULL auto_increment,
user_id int,
group_id int,
time_joined timestamp,
PRIMARY KEY(member_id),
FOREIGN KEY(user_id)
	REFERENCES user_info(user_id),
FOREIGN KEY(group_id)
	REFERENCES groups(group_id)

);

CREATE TABLE messages
(
message_id int auto_increment,
content blob,
send_time timestamp,
member_id int,
group_id int,
PRIMARY KEY (message_id),
FOREIGN KEY (member_id)
	REFERENCES members (member_id),
FOREIGN KEY (group_id)
	REFERENCES groups (group_id)
);

CREATE TABLE discussion
(
discussion_id int,
group_id int,
PRIMARY KEY(discussion_id),
FOREIGN KEY(group_id)
	REFERENCES groups(group_id)
);

INSERT INTO user_info (email, password)
VALUES ('donttalktomeormyson@yeveragain.com', '12345');

SELECT * FROM user_info;

INSERT INTO classes (class_subject, class_number)
VALUES ('CSE', '1341');

SELECT * FROM classes;








