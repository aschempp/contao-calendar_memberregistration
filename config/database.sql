-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_calendar_memberregistration`
-- 

CREATE TABLE `tl_calendar_memberregistration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `registered` int(10) unsigned NOT NULL default '0',
  `member` int(10) unsigned NOT NULL default '0',
  `disable` char(1) NOT NULL default '',
  `participated` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_calendar_events`
-- 

CREATE TABLE `tl_calendar_events` (
  `register` char(1) NOT NULL default '',
  `register_until` varchar(10) NOT NULL default '',
  `register_limit` int(10) unsigned NOT NULL default '0',
  `registered_message` text NULL,
  `register_jumpTo` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_module`
-- 

CREATE TABLE `tl_module` (
  `cal_anonymous` char(1) NOT NULL default '',
  `cal_listParticipants` char(1) NOT NULL default '',
  `cal_pastEvents` char(1) NOT NULL default '',
  `mail_createAccount` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

