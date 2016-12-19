CREATE TABLE IF NOT EXISTS `exp_fbc_params` (
	`params_id`		int(10) unsigned		NOT NULL AUTO_INCREMENT,
	`hash`			varchar(32)				NOT NULL DEFAULT '',
	`entry_date`	int(10)					NOT NULL DEFAULT 0,
	`data`			text,
	PRIMARY KEY		(`params_id`),
	KEY				`hash` (`hash`)
) ;;