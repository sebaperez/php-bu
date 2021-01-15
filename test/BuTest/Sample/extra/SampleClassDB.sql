create database if not exists base;
use base;

create table if not exists sampleclass (
        sampleclass_id int auto_increment,
        name varchar(255),
	optional varchar(255),
	value_validation int,
	date datetime,
	time datetime,
	max_time datetime,
        start_date datetime,
        end_date datetime,
        primary key(sampleclass_id)
);

create table if not exists sampleclassmultiplepk (
        id1 int,
        id2 int,
        name varchar(255),
        start_date datetime,
        end_date datetime,
        primary key(id1, id2)
);

create table if not exists account (
	account_id int auto_increment,
	name varchar(255),
	start_date datetime,
	end_date datetime,
	primary key(account_id)
);

create table if not exists user (
	user_id int auto_increment,
	account_id int,
	email varchar(255),
	name varchar(255),
	lastname varchar(255),
	password varchar(255),
	permission int,
	start_date datetime,
	end_date datetime,
	primary key(user_id)
);

create table if not exists session (
	session_id int auto_increment,
	user_id int,
	hash varchar(255),
	start_date datetime,
	end_date datetime,
	primary key(session_id)
);

create table if not exists sessionchild (
  sessionchild_id int auto_increment,
  session_id int,
  start_date datetime,
  end_date datetime,
  primary key (sessionchild_id)
);

create table if not exists telegram_session (
	session_id int,
	telegram_user_id int,
	start_date datetime,
	end_date datetime,
	primary key (session_id, telegram_user_id)
);
