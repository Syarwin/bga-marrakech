
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `player` ADD `player_money` INT UNSIGNED NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `player_carpets` (
  `player_id` int(10) unsigned NOT NULL,
  `carpet_type` smallint(5) unsigned NOT NULL,
  `carpet_1` smallint(5) unsigned NOT NULL DEFAULT 0,
  `carpet_2` smallint(5) unsigned NOT NULL DEFAULT 0,
  `carpet_3` smallint(5) unsigned NOT NULL DEFAULT 0,
  `carpet_4` smallint(5) unsigned NOT NULL DEFAULT 0,
  `next_carpet` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `board` (
  `turn` int NOT NULL AUTO_INCREMENT,
  `board_x` smallint(5) unsigned NOT NULL,
  `board_y` smallint(5) unsigned NOT NULL,
  `carpet_type` smallint(5) unsigned NOT NULL,
  `carpet_orientation` varchar(1) NOT NULL,
  `player_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`turn`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `assam` (
  `assam_x` smallint(5) unsigned NOT NULL,
  `assam_y` smallint(5) unsigned NOT NULL,
  `direction` varchar(1) NOT NULL,
  PRIMARY KEY (`assam_x`,`assam_y`)
) ENGINE=InnoDB;
