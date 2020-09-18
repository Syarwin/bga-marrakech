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
 * gameoptions.inc.php
 *
 */
require_once("modules/constants.inc.php");

$game_options = [
  OPTION_ROTATE_ASSAM => [
    'name' => totranslate('Rotate Assam at the end of turn'),
    'values' => [
      ROTATE_AT_START_OF_TURN => [ 'name' => totranslate('no'), 'tmdisplay' => totranslate('Rotate Assam at the start of turn') ],
      ROTATE_AT_END_OF_TURN => [ 'name' => totranslate('yes'), 'tmdisplay' => totranslate('Rotate Assam at the end of turn') ],
    ]
  ]
];
