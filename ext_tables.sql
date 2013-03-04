#
# Table structure for table 'tx_appointments_domain_model_appointment'
#
CREATE TABLE tx_appointments_domain_model_appointment (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	begin_time int(11) DEFAULT '0' NOT NULL,
	end_time int(11) DEFAULT '0' NOT NULL,
	begin_reserved int(11) DEFAULT '0' NOT NULL,
	end_reserved int(11) DEFAULT '0' NOT NULL,
	notes text NOT NULL,
	notes_su text NOT NULL,
	type int(11) unsigned DEFAULT '0',
	form_field_values int(11) unsigned DEFAULT '0' NOT NULL,
	address int(11) unsigned DEFAULT '0',
	fe_user int(11) unsigned DEFAULT '0',
	agenda int(11) unsigned DEFAULT '0',
	creation_progress tinyint(1) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_appointments_domain_model_agenda'
#
CREATE TABLE tx_appointments_domain_model_agenda (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	holidays text NOT NULL,
	email_address int(11) unsigned DEFAULT '0' NOT NULL,
	email_text text NOT NULL,
	email_types tinyint(4) unsigned DEFAULT '0' NOT NULL,
	email_owner_types tinyint(4) unsigned DEFAULT '0' NOT NULL,
	calendar_invite_address int(11) unsigned DEFAULT '0' NOT NULL,
	calendar_invite_text text NOT NULL,
	calendar_invite_types tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_appointments_domain_model_type'
#
CREATE TABLE tx_appointments_domain_model_type (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	superuser_only tinyint(1) unsigned DEFAULT '0' NOT NULL,
	exclusive_availability tinyint(1) unsigned DEFAULT '0' NOT NULL,
	default_duration int(4) DEFAULT '0' NOT NULL,
	start_time_monday varchar(5) DEFAULT '' NOT NULL,
	start_time_tuesday varchar(5) DEFAULT '' NOT NULL,
	start_time_wednesday varchar(5) DEFAULT '' NOT NULL,
	start_time_thursday varchar(5) DEFAULT '' NOT NULL,
	start_time_friday varchar(5) DEFAULT '' NOT NULL,
	start_time_saturday varchar(5) DEFAULT '' NOT NULL,
	start_time_sunday varchar(5) DEFAULT '' NOT NULL,
	stop_time_monday varchar(5) DEFAULT '' NOT NULL,
	stop_time_tuesday varchar(5) DEFAULT '' NOT NULL,
	stop_time_wednesday varchar(5) DEFAULT '' NOT NULL,
	stop_time_thursday varchar(5) DEFAULT '' NOT NULL,
	stop_time_friday varchar(5) DEFAULT '' NOT NULL,
	stop_time_saturday varchar(5) DEFAULT '' NOT NULL,
	stop_time_sunday varchar(5) DEFAULT '' NOT NULL,
	exclude_holidays tinyint(1) unsigned DEFAULT '0' NOT NULL,
	max_amount_monday int(4) DEFAULT '0' NOT NULL,
	max_amount_tuesday int(4) DEFAULT '0' NOT NULL,
	max_amount_wednesday int(4) DEFAULT '0' NOT NULL,
	max_amount_thursday int(4) DEFAULT '0' NOT NULL,
	max_amount_friday int(4) DEFAULT '0' NOT NULL,
	max_amount_saturday int(4) DEFAULT '0' NOT NULL,
	max_amount_sunday int(4) DEFAULT '0' NOT NULL,
	minute_interval_monday int(4) DEFAULT '0' NOT NULL,
	minute_interval_tuesday int(4) DEFAULT '0' NOT NULL,
	minute_interval_wednesday int(4) DEFAULT '0' NOT NULL,
	minute_interval_thursday int(4) DEFAULT '0' NOT NULL,
	minute_interval_friday int(4) DEFAULT '0' NOT NULL,
	minute_interval_saturday int(4) DEFAULT '0' NOT NULL,
	minute_interval_sunday int(4) DEFAULT '0' NOT NULL,
	max_amount_per_var_days int(5) DEFAULT '0' NOT NULL,
	per_var_days int(1) DEFAULT '0' NOT NULL,
	per_var_days_interval int(2) DEFAULT '0' NOT NULL,
	between_minutes int(11) DEFAULT '0' NOT NULL,
	hours_mutable int(11) DEFAULT '0' NOT NULL,
	blocked_hours int(11) DEFAULT '0' NOT NULL,
	blocked_hours_workdays int(11) DEFAULT '0' NOT NULL,
	max_days_forward int(11) DEFAULT '0' NOT NULL,
	form_fields int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_appointments_domain_model_formfield'
#
CREATE TABLE tx_appointments_domain_model_formfield (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	type int(11) unsigned DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	label text NOT NULL,
	csh text NOT NULL,
	validation_types varchar(255) DEFAULT '' NOT NULL,
	field_type int(11) DEFAULT '0' NOT NULL,
	choices text NOT NULL,
	function int(11) DEFAULT '0' NOT NULL,
	time_add text NOT NULL,
	enable_field int(11) unsigned DEFAULT '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sorting int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (

	first_name tinytext NOT NULL,
	middle_name tinytext NOT NULL,
	last_name tinytext NOT NULL,
	name tinytext NOT NULL,
	gender varchar(1) DEFAULT '' NOT NULL,
	birthday int(11) DEFAULT '0' NOT NULL,
	email varchar(80) DEFAULT '' NOT NULL,
	address tinytext NOT NULL,
	zip varchar(20) DEFAULT '' NOT NULL,
	city varchar(80) DEFAULT '' NOT NULL,
	tx_appointments_social_security_number varchar(255) DEFAULT '' NOT NULL,
	tx_appointments_creation_progress tinyint(1) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_appointments_domain_model_formfieldvalue'
#
CREATE TABLE tx_appointments_domain_model_formfieldvalue (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	appointment int(11) unsigned DEFAULT '0' NOT NULL,

	value text NOT NULL,
	form_field int(11) unsigned DEFAULT '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL, 

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sorting int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_appointments_agenda_address_mm'
#
CREATE TABLE tx_appointments_agenda_address_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_appointments_agenda_calendarinviteaddress_address_mm'
#
CREATE TABLE tx_appointments_agenda_calendarinviteaddress_address_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);