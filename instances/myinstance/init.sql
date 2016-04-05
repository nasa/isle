CREATE TABLE IF NOT EXISTS `myinstance_locations`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `center` VARCHAR(255) NOT NULL,
  `bldg` VARCHAR(255),
  `room` VARCHAR(255),
  UNIQUE KEY (`center`,`bldg`,`room`)
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_manufacturers`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) UNIQUE NOT NULL,
  `url` VARCHAR(255),
  `parent` INT(10) UNSIGNED,
  FOREIGN KEY (`parent`)
    REFERENCES `myinstance_manufacturers` (`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_asset_models`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `mfr` INT(10) UNSIGNED NOT NULL,
  `model` VARCHAR(255) NOT NULL,
  `desc` VARCHAR(255) NOT NULL,
  `series` VARCHAR(255),
  `url` VARCHAR(255),
  `img` CHAR(3),
  `img_modified` DATETIME,
  UNIQUE KEY (`mfr`,`model`),
  FOREIGN KEY (`mfr`)
    REFERENCES `myinstance_manufacturers` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_assets`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `model` INT(10) UNSIGNED NOT NULL,
  `location` INT(10) UNSIGNED NOT NULL,
  `serial` VARCHAR(255),
  `notes` VARCHAR(255),
  FOREIGN KEY (`model`)
    REFERENCES `myinstance_asset_models` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
  FOREIGN KEY (`location`)
    REFERENCES `myinstance_locations` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_asset_attachments`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `asset` INT(10) UNSIGNED NOT NULL,
  `num` INT(10) UNSIGNED NOT NULL,
  `extension` VARCHAR(4) NOT NULL,
  UNIQUE KEY (`asset`,`num`,`extension`),
  FOREIGN KEY (`asset`)
    REFERENCES `myinstance_assets` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_attribute_types`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `unit` VARCHAR(255) NOT NULL UNIQUE,
  `abbr` VARCHAR (255),
  `parent` INT(10) UNSIGNED,
  FOREIGN KEY (`parent`)
    REFERENCES `myinstance_attribute_types` (`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

# Scalar types (http://www.php.net/manual/en/language.types.intro.php)
INSERT INTO `myinstance_attribute_types` (`id`,`unit`) VALUES
(1,'boolean'),
(2,'integer'),
(3,'float'),
(4,'string')
 ON DUPLICATE KEY UPDATE `unit` = VALUES(`unit`);

CREATE TABLE IF NOT EXISTS `myinstance_attributes`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) UNIQUE NOT NULL,
  `type` INT(10) UNSIGNED NOT NULL,
  FOREIGN KEY (`type`)
    REFERENCES `myinstance_attribute_types` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_asset_model_attributes`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `model` INT(10) UNSIGNED NOT NULL,
  `attribute` INT(10) UNSIGNED NOT NULL,
  `value` VARCHAR (255),
  FOREIGN KEY (`model`)
    REFERENCES `myinstance_asset_models` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`attribute`)
    REFERENCES `myinstance_attributes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_categories`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `parent` INT(10) UNSIGNED,
  FOREIGN KEY (`parent`)
    REFERENCES `myinstance_categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_asset_model_categories`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `model` INT(10) UNSIGNED NOT NULL,
  `category` INT(10) UNSIGNED NOT NULL,
  UNIQUE KEY (`model`,`category`),
  FOREIGN KEY (`model`)
    REFERENCES `myinstance_asset_models` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`category`)
    REFERENCES `myinstance_categories` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_asset_model_attachments`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `model` INT(10) UNSIGNED NOT NULL,
  `num` INT(10) UNSIGNED NOT NULL,
  `extension` VARCHAR(4) NOT NULL,
  UNIQUE KEY (`model`,`num`,`extension`),
  FOREIGN KEY (`model`)
    REFERENCES `myinstance_asset_models` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_roles`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `myinstance_roles` (`id`, `name`) VALUES
(1, 'Disabled'),
(2, 'Viewer'),
(4, 'User'),
(8, 'Contributor'),
(16, 'Administrator')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

CREATE TABLE IF NOT EXISTS `myinstance_users`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` INT(9) ZEROFILL UNSIGNED NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `role` INT(10) UNSIGNED NOT NULL,
  FOREIGN KEY (`role`)
    REFERENCES `myinstance_roles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_transaction_types`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `myinstance_transaction_types` (`id`,`name`) VALUES
(1,'Check-out'),
(2,'Check-in'),
(3,'Restrict'),
(4,'Unrestrict')
 ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

CREATE TABLE IF NOT EXISTS `myinstance_transactions`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `type` INT(10) UNSIGNED NOT NULL,
  `user` INT(10) UNSIGNED NOT NULL,
  `asset` INT(10) UNSIGNED NOT NULL,
  `time` DATETIME NOT NULL,
  `location` INT(10) UNSIGNED,
  `purpose` VARCHAR(255),
  `finish` DATE,
  `notes` VARCHAR(255),
  FOREIGN KEY (`type`)
    REFERENCES `myinstance_transaction_types` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,  
  FOREIGN KEY (`user`)
    REFERENCES `myinstance_users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`asset`)
    REFERENCES `myinstance_assets` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`location`)
    REFERENCES `myinstance_locations` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_relations`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `myinstance_asset_model_relations`
( `id` INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `source` INT(10) UNSIGNED NOT NULL,
  `relation` INT(10) UNSIGNED NOT NULL,
  `target` INT(10) UNSIGNED NOT NULL,
  UNIQUE KEY (`source`,`relation`,`target`),
  FOREIGN KEY (`source`)
    REFERENCES `myinstance_asset_models` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`relation`)
    REFERENCES `myinstance_relations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (`target`)
    REFERENCES `myinstance_asset_models` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;