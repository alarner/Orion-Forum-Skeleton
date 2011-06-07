--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category` varchar(255) character set latin1 NOT NULL,
  `order` tinyint(4) NOT NULL,
  PRIMARY KEY  (`category`),
  KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `cms`
--

DROP TABLE IF EXISTS `cms`;
CREATE TABLE `cc`.`cms` (
  `key` varchar(255)  NOT NULL,
  `description` varchar(255)  NOT NULL,
  `value` text  NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE = MyISAM CHARACTER SET utf8;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `company_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) character set latin1 NOT NULL,
  `slug` varchar(64) character set latin1 NOT NULL,
  `url` varchar(64) character set latin1 NOT NULL,
  `twitter_user` varchar(64) character set latin1 NOT NULL,
  PRIMARY KEY  (`company_id`),
  KEY `name` (`name`),
  KEY `slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `company_terms`
--

DROP TABLE IF EXISTS `company_terms`;
CREATE TABLE `company_terms` (
  `company_id` int(10) unsigned NOT NULL,
  `term` varchar(255) character set latin1 NOT NULL,
  PRIMARY KEY  (`company_id`,`term`),
  KEY `company_id` (`company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE  `cc`.`posts` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `company_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned default NULL,
  `topic` varchar(255) default NULL,
  `body` text NOT NULL,
  `parent_post_id` int(10) unsigned default NULL,
  `root_post_id` int(10) unsigned default NULL,
  `created` datetime NOT NULL,
  `last_reply` datetime NOT NULL,
  `ip` varchar(24) NOT NULL,
  `votes` int(11) NOT NULL default '0',
  `reports` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `company_id` (`company_id`),
  KEY `user_id` (`user_id`),
  KEY `topic` (`topic`),
  KEY `parent_post_id` (`parent_post_id`),
  KEY `created` (`created`),
  KEY `last_reply` (`last_reply`),
  KEY `root_post_id` (`root_post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(128) character set latin1 NOT NULL,
  `password` varchar(128) character set latin1 NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes` (
  `post_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ip` varchar(24) NOT NULL,
  `up` tinyint(1) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
