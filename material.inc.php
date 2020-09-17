<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * Marrakech game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

require_once("modules/constants.inc.php");
require_once("modules/Utils.class.php");
require_once("modules/MarrakechPlayerManager.class.php");
require_once("modules/MarrakechBoard.class.php");
require_once("modules/MarrakechAssam.class.php");
require_once("modules/NotificationManager.class.php");


// Marrakech die (D6 with faces 1,2,2,3,3,4)
$this->marrakechDice = [
  1 => 1,
  2 => 2,
  3 => 4,
  4 => 2,
  5 => 3,
  6 => 3
];
