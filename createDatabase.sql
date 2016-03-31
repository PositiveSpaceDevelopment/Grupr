DROP DATABASE IF EXISTS gruprDatabase;
CREATE database gruprDatabase;
USE gruprDatabase;

CREATE table user_info
(
user_id int NOT NULL AUTO_INCREMENT,
email varchar(50),
password varchar(255),
salt varchar(255),
first_name varchar(25),
last_name varchar(25),
icon smallint,
profile_level float,
session_id varchar(30),
last_login timestamp,
is_teacher tinyint,
is_ta tinyint,
encrypted_password varchar(255),
PRIMARY KEY(user_id)
);

CREATE table message
(
message_id int NOT NULL auto_increment,
content tinyblob,
send_time timestamp,
member_id int,
discussion_id int,
PRIMARY KEY(message_id)
);

CREATE table locations
(
location_id int auto_increment,
building varchar(30),
room varchar(30),
PRIMARY KEY(location_id)
);

CREATE table Classes
(
class_subject varchar(4),
class_number varchar(4),
semester varchar(10),
class_id int NOT NULL auto_increment,
user_id int,
teacher_id int,
ta_id int,
PRIMARY KEY(class_id),
FOREIGN KEY(user_id)
	REFERENCES user_info(user_id),
FOREIGN KEY(teacher_id)
	REFERENCES user_info(user_id),
FOREIGN KEY(ta_id)
	REFERENCES user_info(user_id)
);

CREATE table groups
(
group_id int NOT NULL auto_increment,
group_name varchar(55),
class_id int,
creation_time timestamp,
time_of_meeting datetime,
owner_id int,
description varchar(200),
location_id int,
ta_attending tinyint,
teacher_attending tinyint,
PRIMARY KEY(group_id),
FOREIGN KEY(owner_id)
	REFERENCES user_info(user_id),
FOREIGN KEY(class_id)
	REFERENCES Classes(class_id),
FOREIGN KEY(location_id)
	REFERENCES locations(location_id)
);



CREATE table members
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

CREATE table discussion
(
discussion_id int,
group_id int,
PRIMARY KEY(discussion_id),
FOREIGN KEY(group_id)
	REFERENCES groups(group_id)
);

INSERT INTO user_info (email)
VALUES ('hogfan@yahoo.com');

SELECT * FROM user_info;










