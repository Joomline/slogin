CREATE TABLE IF NOT EXISTS `#__plg_slogin_avatar` (
  `id` int(13) NOT NULL AUTO_INCREMENT,
  `provider` text NOT NULL,
  `userid` int(13) NOT NULL,
  `photo_src` text NOT NULL,
  `main` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
