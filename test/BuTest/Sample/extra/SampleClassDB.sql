drop database base;
create database if not exists base;
use base;

create table if not exists sampleclass (
        sampleclass_id int auto_increment,
        name varchar(255),
	optional varchar(255),
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
