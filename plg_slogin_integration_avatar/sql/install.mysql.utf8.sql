CREATE TABLE IF NOT EXISTS `#__plg_slogin_avatar` (
  `id` int(13) NOT NULL AUTO_INCREMENT,
  `provider` text NOT NULL,
  `userid` int(13) NOT NULL,
  `photo_src` text NOT NULL,
  `main` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

# v1.5
ALTER TABLE  `#__plg_slogin_avatar` ADD  `profile` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;