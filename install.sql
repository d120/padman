CREATE TABLE `padman_group_cache` (
	`group_mapper` VARCHAR(100) NOT NULL DEFAULT '',
	`group_id` VARCHAR(100) NOT NULL DEFAULT '',
	`tags` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`group_mapper`)
);
CREATE TABLE `padman_pad_cache` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_mapper` VARCHAR(100) NOT NULL DEFAULT '',
	`group_id` VARCHAR(100) NOT NULL DEFAULT '',
	`pad_name` VARCHAR(100) NOT NULL DEFAULT '',
	`last_edited` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`password` VARCHAR(100) NULL DEFAULT NULL,
	`access_level` INT(11) NOT NULL,
	`tags` VARCHAR(255) NOT NULL DEFAULT '',
	`shortlink` VARCHAR(100) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `pad_id` (`group_mapper`, `pad_name`)
);


