
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

ALTER TABLE `player` ADD `money` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `carpet_type` SMALLINT(5) UNSIGNED;
ALTER TABLE `player` ADD `carpet_1` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `carpet_2` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `carpet_3` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `carpet_4` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `next_carpet` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `carpets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `x` smallint(5) unsigned NOT NULL,
  `y` smallint(5) unsigned NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `orientation` varchar(1) NOT NULL,
  `player_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `assam` (
  `x` smallint(5) unsigned NOT NULL,
  `y` smallint(5) unsigned NOT NULL,
  `dir` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`x`,`y`)
) ENGINE=InnoDB;
