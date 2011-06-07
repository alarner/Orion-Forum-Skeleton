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
CREATE TABLE `cms` (
  `key` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

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
CREATE TABLE `posts` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `company_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned default NULL,
  `topic` varchar(255) default NULL,
  `body` text NOT NULL,
  `parent_post_id` int(10) unsigned default NULL,
  `root_post_id` int(10) unsigned default NULL,
  `created` datetime NOT NULL,
  `last_reply` datetime default NULL,
  `ip` varchar(64) NOT NULL,
  `votes` int(11) NOT NULL default '0',
  `reports` int(10) unsigned NOT NULL default '0',
  `comments` int(10) unsigned NOT NULL default '0',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `company_id` (`company_id`),
  KEY `user_id` (`user_id`),
  KEY `topic` (`topic`),
  KEY `parent_post_id` (`parent_post_id`),
  KEY `created` (`created`),
  KEY `last_reply` (`last_reply`),
  KEY `root_post_id` (`root_post_id`),
  KEY `votes` (`votes`),
  KEY `comments` (`comments`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `post_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned default NULL,
  `ip` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(128) character set latin1 NOT NULL,
  `password` varchar(128) character set latin1 NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `salt` varchar(24) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `created` datetime NOT NULL,
  `last_login` datetime default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes` (
  `post_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned default NULL,
  `ip` varchar(64) NOT NULL,
  `up` tinyint(1) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
