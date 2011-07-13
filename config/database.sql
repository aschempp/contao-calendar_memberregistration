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
  `member` int(10) unsigned NOT NULL default '0',
  `disable` char(1) NOT NULL default '',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

