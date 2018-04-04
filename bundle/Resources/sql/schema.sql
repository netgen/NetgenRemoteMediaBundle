CREATE TABLE IF NOT EXISTS `ngremotemedia_field_link` (
  `field_id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `resource_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contentobject_id` int(11) NOT NULL,
  PRIMARY KEY (`field_id`,`version`,`resource_id`,`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
