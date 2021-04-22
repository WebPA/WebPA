-- WebPA 2.0.0.10
--
-- Table definitions

--
-- Create tables
--

CREATE TABLE pa2_assessment (
  assessment_id char(36) NOT NULL,
  assessment_name varchar(100) NOT NULL,
  module_id int(10) unsigned NOT NULL,
  collection_id char(36) NOT NULL,
  form_xml text NOT NULL,
  open_date datetime NOT NULL,
  close_date datetime NOT NULL,
  retract_date datetime NOT NULL,
  introduction text NOT NULL,
  allow_feedback tinyint(1) NOT NULL,
  assessment_type tinyint(1) NOT NULL,
  student_feedback tinyint(1) NOT NULL,
  email_opening tinyint(1) NOT NULL,
  email_closing tinyint(1) NOT NULL,
  contact_email varchar(255) NOT NULL,
  feedback_name varchar(45) NOT NULL,
  feedback_length varchar(45) NOT NULL,
  feedback_optional tinyint(4) NOT NULL,
  PRIMARY KEY (assessment_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_assessment_group_marks (
  assessment_id char(36) NOT NULL,
  group_mark_xml text NOT NULL,
  PRIMARY KEY (assessment_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_assessment_marking (
  assessment_id char(36) NOT NULL,
  date_created datetime NOT NULL,
  date_last_marked datetime NOT NULL,
  marking_params varchar(255) NOT NULL,
  PRIMARY KEY (assessment_id,date_created)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_collection (
  collection_id char(36) NOT NULL,
  module_id int(10) unsigned NOT NULL,
  collection_name varchar(50) NOT NULL,
  collection_created_on datetime NOT NULL,
  collection_locked_on datetime DEFAULT NULL,
  PRIMARY KEY (collection_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_form (
  form_id char(36) NOT NULL,
  form_name varchar(100) NOT NULL,
  form_type varchar(20) NOT NULL,
  form_xml text NOT NULL,
  PRIMARY KEY (form_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_form_module (
  form_id char(36) NOT NULL,
  module_id int(10) unsigned NOT NULL,
  PRIMARY KEY (form_id,module_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_module (
  module_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  source_id varchar(255) NOT NULL DEFAULT '',
  module_code varchar(255) NOT NULL,
  module_title varchar(255) NOT NULL,
  PRIMARY KEY (module_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user (
  user_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  source_id varchar(255) NOT NULL DEFAULT '',
  username varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  id_number varchar(255) DEFAULT NULL,
  department_id varchar(255) DEFAULT NULL,
  forename varchar(255) NOT NULL,
  lastname varchar(255) NOT NULL,
  email varchar(255) DEFAULT NULL,
  admin tinyint(1) NOT NULL DEFAULT '0',
  disabled tinyint(1) NOT NULL DEFAULT '0',
  date_last_login datetime DEFAULT NULL,
  last_module_id int(10) DEFAULT NULL,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_group (
  group_id char(36) NOT NULL,
  collection_id char(36) NOT NULL,
  group_name varchar(50) NOT NULL,
  PRIMARY KEY (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_group_member (
  group_id char(36) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  PRIMARY KEY (group_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_justification (
  assessment_id char(36) NOT NULL,
  group_id char(36) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  marked_user_id int(10) unsigned NOT NULL,
  justification_text text NOT NULL,
  date_marked datetime NOT NULL,
  PRIMARY KEY (assessment_id,group_id,user_id,marked_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_mark (
  assessment_id char(36) NOT NULL,
  group_id char(36) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  marked_user_id int(10) unsigned NOT NULL,
  question_id tinyint(4) NOT NULL,
  date_marked datetime NOT NULL,
  score tinyint(4) NOT NULL,
  PRIMARY KEY (assessment_id,group_id,user_id,marked_user_id,question_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_module (
  user_id int(10) unsigned NOT NULL,
  module_id int(10) unsigned NOT NULL,
  user_type char(1) NOT NULL DEFAULT 'S',
  PRIMARY KEY (user_id,module_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_reset_request (
  id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_response (
  assessment_id char(36) NOT NULL,
  group_id char(36) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  ip_address varchar(20) NOT NULL,
  comp_name varchar(50) NOT NULL,
  date_responded datetime NOT NULL,
  date_opened datetime NOT NULL,
  PRIMARY KEY (assessment_id,group_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE pa2_user_tracking (
  user_id int(10) unsigned NOT NULL,
  `datetime` datetime NOT NULL,
  ip_address varchar(15) NOT NULL,
  description varchar(255) NOT NULL,
  module_id int(11) DEFAULT NULL,
  object_id varchar(36) DEFAULT NULL,
  PRIMARY KEY (user_id,`datetime`, description)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Add unique constraints
--

ALTER TABLE pa2_user add CONSTRAINT pa2_user_UC1 UNIQUE (
	source_id, username);

ALTER TABLE pa2_module add CONSTRAINT pa2_module_UC1 UNIQUE (
	source_id, module_code);

--
-- Add referential integrity constraints
--

ALTER TABLE pa2_assessment
	add CONSTRAINT pa2_module_assessment_FK1 FOREIGN KEY (
		module_id)
	 REFERENCES pa2_module (
		module_id);

ALTER TABLE pa2_assessment
	add CONSTRAINT pa2_collection_assessment_FK1 FOREIGN KEY (
		collection_id)
	 REFERENCES pa2_collection (
		collection_id);

ALTER TABLE pa2_assessment_group_marks
	add CONSTRAINT pa2_assessment_assessment_group_marks_FK1 FOREIGN KEY (
		assessment_id)
	 REFERENCES pa2_assessment (
		assessment_id);

ALTER TABLE pa2_assessment_marking
	add CONSTRAINT pa2_assessment_assessment_marking_FK1 FOREIGN KEY (
		assessment_id)
	 REFERENCES pa2_assessment (
		assessment_id);

ALTER TABLE pa2_collection
	add CONSTRAINT pa2_module_collection_FK1 FOREIGN KEY (
		module_id)
	 REFERENCES pa2_module (
		module_id);

ALTER TABLE pa2_form_module
	add CONSTRAINT pa2_form_form_module_FK1 FOREIGN KEY (
		form_id)
	 REFERENCES pa2_form (
		form_id);

ALTER TABLE pa2_form_module
	add CONSTRAINT pa2_module_form_module_FK1 FOREIGN KEY (
		module_id)
	 REFERENCES pa2_module(
		module_id);

ALTER TABLE pa2_user_group
	add CONSTRAINT pa2_collection_user_group_FK1 FOREIGN KEY (
		collection_id)
	 REFERENCES pa2_collection (
		collection_id);

ALTER TABLE pa2_user_group_member
	add CONSTRAINT pa2_user_group_user_group_member_FK1 FOREIGN KEY (
		group_id)
	 REFERENCES pa2_user_group (
		group_id);

ALTER TABLE pa2_user_group_member
	add CONSTRAINT pa2_user_user_group_member_FK1 FOREIGN KEY (
		user_id)
	 REFERENCES pa2_user (
		user_id);

ALTER TABLE pa2_user_justification
	add CONSTRAINT pa2_user_response_user_justification_FK1 FOREIGN KEY (
		assessment_id,
		group_id,
		user_id)
	 REFERENCES pa2_user_response (
		assessment_id,
		group_id,
		user_id);

ALTER TABLE pa2_user_justification
	add CONSTRAINT pa2_user_user_justification_FK1 FOREIGN KEY (
		marked_user_id)
	 REFERENCES pa2_user (
		user_id);

ALTER TABLE pa2_user_mark
	add CONSTRAINT pa2_user_response_user_mark_FK1 FOREIGN KEY (
		assessment_id,
		group_id,
		user_id)
	 REFERENCES pa2_user_response (
		assessment_id,
		group_id,
		user_id);

ALTER TABLE pa2_user_module
	add CONSTRAINT pa2_module_user_module_FK1 FOREIGN KEY (
		module_id)
	 REFERENCES pa2_module (
		module_id);

ALTER TABLE pa2_user_module
	add CONSTRAINT pa2_user_user_module_FK1 FOREIGN KEY (
		user_id)
	 REFERENCES pa2_user (
		user_id);

ALTER TABLE pa2_user_reset_request
	add CONSTRAINT pa2_user_user_reset_request_FK1 FOREIGN KEY (
		user_id)
	 REFERENCES pa2_user (
		user_id);

ALTER TABLE pa2_user_response
	add CONSTRAINT pa2_assessment_user_response_FK1 FOREIGN KEY (
		assessment_id)
	 REFERENCES pa2_assessment (
		assessment_id);

ALTER TABLE pa2_user_response
	add CONSTRAINT pa2_user_group_member_user_response_FK1 FOREIGN KEY (
		group_id,
		user_id)
	 REFERENCES pa2_user_group_member (
		group_id,
		user_id);

ALTER TABLE pa2_user_tracking
	add CONSTRAINT pa2_user_user_tracking_FK1 FOREIGN KEY (
		user_id)
	 REFERENCES pa2_user (
		user_id);
