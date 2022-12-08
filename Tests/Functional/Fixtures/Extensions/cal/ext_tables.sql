# Source: https://gitlab.com/janhelke/cal/-/blob/0487d82241d5a214c720b582fa1d786f6419f10b/ext_tables.sql
# License: GPL-2.0-or-later

#
# Table structure for table 'tx_cal_event'
#
CREATE TABLE tx_cal_event (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,

	start_date int(11) DEFAULT '0' NOT NULL,
	end_date int(11) DEFAULT '0' NOT NULL,
	start_time int(11) unsigned DEFAULT '0' NOT NULL,
	end_time int(11) unsigned DEFAULT '0' NOT NULL,
	allday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	timezone varchar(5) DEFAULT 'UTC' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	calendar_id int(11) unsigned DEFAULT '0' NOT NULL,
	category_id int(11) unsigned DEFAULT '0' NOT NULL,
	organizer varchar(128) DEFAULT '' NOT NULL,
	organizer_id int(11) unsigned DEFAULT '0' NOT NULL,
	organizer_pid int(11) DEFAULT '0' NOT NULL,
	organizer_link varchar(255) DEFAULT '' NOT NULL,
	location varchar(128) DEFAULT '' NOT NULL,
	location_id int(11) unsigned DEFAULT '0' NOT NULL,
	location_pid int(11) DEFAULT '0' NOT NULL,
	location_link varchar(255) DEFAULT '' NOT NULL,
	teaser text,
	description text,
	freq varchar(128) DEFAULT 'none',
	until int(11) DEFAULT '0' NOT NULL,
	cnt tinyint(4) unsigned DEFAULT '0',
	byday varchar(128) DEFAULT '',
	bymonthday varchar(128) DEFAULT '',
	bymonth varchar(128) DEFAULT '',
	intrval tinyint(4) unsigned DEFAULT '1',
	rdate text,
	rdate_type varchar(10) DEFAULT 'none' NOT NULL,
	deviation varchar(255) DEFAULT '',
	monitor_cnt int(11) unsigned DEFAULT '0',
	exception_cnt int(11) unsigned DEFAULT '0',
	fe_cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	fe_crgroup_id int(11) unsigned DEFAULT '0' NOT NULL,
	shared_user_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	type tinyint(4) DEFAULT '0' NOT NULL,
	page int(11) DEFAULT '0' NOT NULL,
	ext_url VARCHAR(255) DEFAULT '' NOT NULL,
	isTemp tinyint(1) DEFAULT '0' NOT NULL,
	icsUid text,
	image VARCHAR(255) DEFAULT '' NOT NULL,
	attachment text,
	ref_event_id int(11) unsigned DEFAULT '0',
	send_invitation tinyint(1) DEFAULT '0' NOT NULL,
	attendee VARCHAR(255) DEFAULT '' NOT NULL,
	status varchar(12) DEFAULT '' NOT NULL,
	priority tinyint(1) DEFAULT '0' NOT NULL,
	completed tinyint(3) DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_event_shared_user_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_calendar (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	owner int(11) unsigned DEFAULT '0' NOT NULL,
	activate_fnb tinyint(4) unsigned DEFAULT '0' NOT NULL,
	fnb_user_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	type tinyint(4) DEFAULT '0' NOT NULL,
	ext_url text,
	ext_url_notes text,
	ics_file VARCHAR(255) DEFAULT '' NOT NULL,
	refresh int(11) unsigned DEFAULT '0' NOT NULL,
	md5 varchar(32) DEFAULT '' NOT NULL,
	headerstyle varchar(30) DEFAULT '' NOT NULL,
	bodystyle varchar(30) DEFAULT '' NOT NULL,
	schedulerId int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

# fnb = free & busy
CREATE TABLE tx_cal_calendar_fnb_user_group_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Extend table structure of table 'sys_category'
#
CREATE TABLE sys_category (
	icon varchar(128) DEFAULT '' NOT NULL,
	single_pid int(11) unsigned DEFAULT '0' NOT NULL,
	shortcut int(11) DEFAULT '0' NOT NULL,

	headerstyle varchar(30) DEFAULT '' NOT NULL,
	bodystyle varchar(30) DEFAULT '' NOT NULL,
	calendar_id int(11) unsigned DEFAULT '0' NOT NULL,
	shared_user_allowed tinyint(4) unsigned DEFAULT '0' NOT NULL,
	notification_emails text
);

CREATE TABLE tx_cal_category (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	parent_category int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	headerstyle varchar(30) DEFAULT '' NOT NULL,
	bodystyle varchar(30) DEFAULT '' NOT NULL,
	calendar_id int(11) unsigned DEFAULT '0' NOT NULL,
	single_pid int(11) DEFAULT '0' NOT NULL,
	shared_user_allowed tinyint(4) unsigned DEFAULT '0' NOT NULL,
	notification_emails text,
	icon varchar(128) DEFAULT '' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_event_category_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_calendar_user_group_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_fe_user_event_monitor_mm (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	offset int(11) DEFAULT '0' NOT NULL,
	schedulerId int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_unknown_users (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	email varchar(128) DEFAULT '' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_exception_event_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_exception_event_group_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


CREATE TABLE tx_cal_exception_event_group (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	exception_event_cnt int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_exception_event (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	start_date int(11) DEFAULT '0' NOT NULL,
	end_date int(11) DEFAULT '0' NOT NULL,
	relation_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	freq varchar(128) DEFAULT '',
	until int(11) DEFAULT '0' NOT NULL,
	cnt tinyint(4) unsigned DEFAULT '0',
	byday varchar(128) DEFAULT '',
	bymonthday varchar(128) DEFAULT '',
	bymonth varchar(128) DEFAULT '',
	intrval tinyint(4) unsigned DEFAULT '1',
	rdate text,
	rdate_type varchar(10) DEFAULT 'none' NOT NULL,
	monitor_cnt int(11) unsigned DEFAULT '0',

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_organizer (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	description text,
	street varchar(128) DEFAULT '' NOT NULL,
	zip varchar(16) DEFAULT '' NOT NULL,
	city varchar(128) DEFAULT '' NOT NULL,
	country_zone varchar(16) DEFAULT '' NOT NULL,
	country varchar(16) DEFAULT '' NOT NULL,
	phone varchar(24) DEFAULT '' NOT NULL,
	fax varchar(24) DEFAULT '' NOT NULL,
	email varchar(64) DEFAULT '' NOT NULL,
	image varchar(64) DEFAULT '' NOT NULL,
	imagecaption text,
	imagealttext text,
	imagetitletext text,
	link varchar(255) DEFAULT '' NOT NULL,
	fe_user_id int(11) unsigned DEFAULT '0' NOT NULL,
	shared_user_cnt int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_organizer_shared_user_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_location (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	description text,
	street varchar(128) DEFAULT '' NOT NULL,
	zip varchar(16) DEFAULT '' NOT NULL,
	city varchar(128) DEFAULT '' NOT NULL,
	country_zone varchar(16) DEFAULT '' NOT NULL,
	country varchar(16) DEFAULT '' NOT NULL,
	phone varchar(24) DEFAULT '' NOT NULL,
	fax varchar(24) DEFAULT '' NOT NULL,
	email varchar(64) DEFAULT '' NOT NULL,
	image varchar(64) DEFAULT '' NOT NULL,
	imagecaption text,
	imagealttext text,
	imagetitletext text,
	link varchar(255) DEFAULT '' NOT NULL,
	shared_user_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	latitude double default '0',
	longitude double default '0',

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_location_shared_user_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

CREATE TABLE tt_address (
	tx_cal_controller_isorganizer tinyint(4) DEFAULT '0' NOT NULL,
	tx_cal_controller_islocation tinyint(4) DEFAULT '0' NOT NULL,
	tx_cal_controller_latitude VARCHAR(255) DEFAULT '' NOT NULL,
	tx_cal_controller_longitude VARCHAR(255) DEFAULT '' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE be_users (
	tx_cal_enable_accesscontroll tinyint(4) DEFAULT '0' NOT NULL,
	tx_cal_calendar VARCHAR(255) DEFAULT '' NOT NULL,
	tx_cal_category VARCHAR(255) DEFAULT '' NOT NULL
);

CREATE TABLE be_groups (
	tx_cal_enable_accesscontroll tinyint(4) DEFAULT '0' NOT NULL,
	tx_cal_calendar VARCHAR(255) DEFAULT '' NOT NULL,
	tx_cal_category VARCHAR(255) DEFAULT '' NOT NULL
);

CREATE TABLE fe_users (
	tx_cal_calendar VARCHAR(255) DEFAULT '' NOT NULL,
	tx_cal_calendar_subscription VARCHAR(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_cal_attendee (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	event_id int(11) unsigned DEFAULT '0' NOT NULL,
	email varchar(128) DEFAULT '',
	fe_user_id int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group_id int(11) unsigned DEFAULT '0' NOT NULL,
	attendance varchar(16) DEFAULT '' NOT NULL,
	status varchar(12) DEFAULT '' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

CREATE TABLE tx_cal_index (
	uid int(11) unsigned NOT NULL auto_increment,
	tablename varchar(30) DEFAULT '' NOT NULL,
	start_datetime bigint(14) NOT NULL DEFAULT '0',
	end_datetime bigint(14) NOT NULL DEFAULT '0',
	event_uid int(11) DEFAULT '-1' NOT NULL,
	event_deviation_uid int(11) DEFAULT '-1' NOT NULL,
	PRIMARY KEY (uid),
	KEY start_datetime (start_datetime),
	KEY event_uid_start_datetime (event_uid, start_datetime)
);

#
# Table structure for table 'tx_cal_event_deviation'
#
CREATE TABLE tx_cal_event_deviation (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	parentid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	orig_start_date int(11) DEFAULT '0' NOT NULL,
	orig_start_time int(11) DEFAULT '0' NOT NULL,
	start_date int(11) DEFAULT '0' NOT NULL,
	end_date int(11) DEFAULT '0' NOT NULL,
	start_time int(11) unsigned DEFAULT '0' NOT NULL,
	end_time int(11) unsigned DEFAULT '0' NOT NULL,
	allday tinyint(4) unsigned DEFAULT '0' NOT NULL,
	timezone varchar(5) DEFAULT 'UTC' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	organizer varchar(128) DEFAULT '' NOT NULL,
	organizer_id int(11) unsigned DEFAULT '0' NOT NULL,
	organizer_pid int(11) DEFAULT '0' NOT NULL,
	organizer_link varchar(255) DEFAULT '' NOT NULL,
	location varchar(128) DEFAULT '' NOT NULL,
	location_id int(11) unsigned DEFAULT '0' NOT NULL,
	location_pid int(11) DEFAULT '0' NOT NULL,
	location_link varchar(255) DEFAULT '' NOT NULL,
	teaser text,
	description text,
	isTemp tinyint(1) DEFAULT '0' NOT NULL,
	icsUid text,
	image VARCHAR(255) DEFAULT '' NOT NULL,
	imagecaption text,
	imagealttext text,
	imagetitletext text,
	attachment text,
	attachmentcaption text,
	ref_event_id int(11) unsigned DEFAULT '0',

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)
);

#
# Table structure for table 'tx_cal_cache'
#
CREATE TABLE tx_cal_cache (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(128) DEFAULT '' NOT NULL,
	content text,
	crdate int(11) DEFAULT '0' NOT NULL,
	lifetime int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier)
)
	ENGINE = InnoDB;

#
# Table structure for table 'tx_cal_cache_tags'
#
CREATE TABLE tx_cal_cache_tags (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(128) DEFAULT '' NOT NULL,
	tag varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier),
	KEY cache_tag (tag)
)
	ENGINE = InnoDB;
