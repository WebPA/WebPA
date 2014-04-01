CREATE database IF NOT EXISTS pa;

CREATE TABLE IF NOT EXISTS `pa`.`assessment` (
  `assessment_id` varchar(36) NOT NULL default '',
  `assessment_name` varchar(100) NOT NULL default '',
  `owner_id` varchar(20) NOT NULL default '',
  `collection_id` varchar(36) default NULL,
  `form_xml` text,
  `open_date` datetime default NULL,
  `close_date` datetime default NULL,
  `introduction` text,
  `allow_feedback` tinyint(3) unsigned default '0',
  `assessment_type` tinyint(3) unsigned default '0',
  `student_feedback` tinyint(3) unsigned default '0',
  `email_opening` tinyint(3) unsigned default '0',
  `email_closing` tinyint(3) unsigned default '0', 
  `feedback_name` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`assessment_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `pa`.`assessment_group_marks` (
  `assessment_id` varchar(36) NOT NULL default '',
  `group_mark_xml` text,
  PRIMARY KEY  (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`assessment_marking` (
  `assessment_id` varchar(36) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_last_marked` datetime default NULL,
  `marking_params` varchar(255) default '0',
  PRIMARY KEY  (`assessment_id`,`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`collection_creation_method` (
  `collection_id` varchar(32) NOT NULL default '',
  `creation_dt` datetime NOT NULL default '0000-00-00 00:00:00',
  `username` varchar(10) NOT NULL default '',
  `user_id` varchar(20) NOT NULL default '',
  `creation_method` varchar(10) NOT NULL default '',
  `other_text` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`collection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`form` (
  `form_id` varchar(36) NOT NULL default '0',
  `form_name` varchar(100) default '0',
  `form_owner_id` varchar(20) default '0',
  `form_type` VARCHAR(20) NOT NULL default '0',
  PRIMARY KEY  (`form_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`form_xml` (
  `form_id` varchar(36) NOT NULL default '',
  `form_xml` text,
  PRIMARY KEY  (`form_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`module` (
  `module_id` int(20) unsigned NOT NULL auto_increment,
  `module_code` varchar(45) NOT NULL default '',
  `module_title` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `user_type` varchar(45) NOT NULL default '',
  `institutional_reference` varchar(45) NOT NULL default '',
  `forename` varchar(45) NOT NULL default '',
  `lastname` varchar(45) NOT NULL default '',
  `email` varchar(45) NOT NULL default '',
  `username` varchar(45) NOT NULL default '',
  `department_id` varchar(45) NOT NULL default '',
  `course_id` varchar(45) NOT NULL default '',
  `password` varchar(45) NOT NULL default '',
  `admin` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_collection` (
  `collection_id` varchar(36) NOT NULL default '',
  `collection_name` varchar(50) NOT NULL default '',
  `collection_created_on` datetime default NULL,
  `collection_locked_on` datetime default NULL,
  `collection_owner_id` varchar(50) NOT NULL default '',
  `collection_owner_app` varchar(10) NOT NULL default '',
  `collection_owner_type` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`collection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_collection_module` (
  `collection_id` char(36) NOT NULL default '0',
  `module_id` char(45) NOT NULL default '0',
  PRIMARY KEY  (`collection_id`,`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_group` (
  `group_id` char(36) NOT NULL default '0',
  `collection_id` char(36) NOT NULL default '0',
  `group_name` char(50) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`collection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_group_member` (
  `group_id` char(36) NOT NULL default '',
  `collection_id` char(36) NOT NULL default '',
  `user_id` char(50) NOT NULL default '',
  `user_role` char(12) NOT NULL default '',
  PRIMARY KEY  (`group_id`,`collection_id`,`user_id`),
  KEY `user_role` (`user_role`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_mark` (
  `assessment_id` varchar(36) NOT NULL default '',
  `collection_id` varchar(36) NOT NULL default '',
  `group_id` varchar(36) NOT NULL default '',
  `user_id` varchar(50) NOT NULL default '',
  `marked_user_id` varchar(50) NOT NULL default '',
  `question_id` int(10) unsigned NOT NULL default '0',
  `score` int(10) unsigned NOT NULL default '0',
  `date_marked` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`assessment_id`,`user_id`,`collection_id`,`group_id`,`marked_user_id`,`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_module` (
  `user_module_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `module_id` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`user_module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pa`.`user_response` (
  `assessment_id` varchar(36) NOT NULL default '',
  `collection_id` varchar(36) NOT NULL default '',
  `group_id` varchar(36) NOT NULL default '',
  `user_id` varchar(50) NOT NULL default '',
  `ip_address` varchar(20) NOT NULL default '',
  `comp_name` varchar(50) NOT NULL default '',
  `date_responded` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_opened` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`assessment_id`,`collection_id`,`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `pa`.`user_justification` (
  `assessment_id` VARCHAR(36) NOT NULL,
  `collection_id` VARCHAR(36) NOT NULL,
  `group_id` VARCHAR(36) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `marked_user_id` VARCHAR(50) NOT NULL,
  `justification_text` TEXT NOT NULL,
  `date_marked` DATETIME NOT NULL,
  PRIMARY KEY (`assessment_id`, `collection_id`, `group_id`, `user_id`, `marked_user_id`)
)ENGINE = MyISAM DEFAULT CHARSET=latin1;