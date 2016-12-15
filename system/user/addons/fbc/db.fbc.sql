CREATE TABLE IF NOT EXISTS `exp_fbc_preferences` (
	`fbc_preference_id`		int(10) unsigned		NOT NULL AUTO_INCREMENT,
	`fbc_preference_name`	varchar(100)			NOT NULL DEFAULT '',
	`fbc_preference_value`	varchar(100)			NOT NULL DEFAULT '',
	`site_id`				int(5) unsigned			NOT NULL DEFAULT 1,
	PRIMARY KEY				(`fbc_preference_id`),
	KEY `site_id`			(`site_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;
