CREATE TABLE IF NOT EXISTS `#__slogin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `slogin_id` varchar(150) NOT NULL,
  `provider` varchar(100) NOT NULL,
  `confirmed` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`slogin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
