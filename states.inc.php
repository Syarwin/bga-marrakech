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
 * states.inc.php
 *
 * Marrakech game states description
 *
 */


$machinestates = [

    // The initial state. Please do not modify.
    ST_GAME_SETUP => [
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => [
          "" => ST_START_OF_TURN
        ]
    ],

    // Player start of turn => depending on variant, go to assam or to roll dice
    ST_START_OF_TURN => [
      "name" => "startOfTurn",
      "description" => "",
      "type" => "game",
      "action" => "stStartOfTurn",
      "transitions" => [
        "rotateAssam" => ST_ROTATE_ASSAM,
        "moveAssam" => ST_MOVE_ASSAM,
        "zombiePass" => ST_ELIMINATE_PLAYER
      ]
    ],

    // Rotate or not Assam
    ST_ROTATE_ASSAM => [
      "name" => "rotateAssam",
      "description" => clienttranslate('${actplayer} may rotate Assam left or right'),
      "descriptionmyturn" => clienttranslate('${you} may rotate Assam left or right'),
      "type" => "activeplayer",
      "possibleactions" => [ "adjust" ],
      "transitions" => [
        "moveAssam" => ST_MOVE_ASSAM,
        "nextPlayer" => ST_NEXT_PLAYER,
        "zombiePass" => ST_ELIMINATE_PLAYER,
        'eliminate' => ST_ELIMINATE_PLAYER,
      ]
    ],

    // Throw the die, move Assam and pay taxes
    ST_MOVE_ASSAM => [
      "name" => "moveAssam",
      "description" => clienttranslate('${actplayer} must roll the dice to move Assam'),
      "descriptionmyturn" => clienttranslate('${you} must roll the dice to move Assam'),
      "type" => "activeplayer",
      "possibleactions" => [ "rollDice" ],
      "transitions" => [
        "placeCarpet" => ST_PLACE_CARPET,
        "nextPlayer" => ST_NEXT_PLAYER,
        "zombiePass" => ST_ELIMINATE_PLAYER,
        'eliminate' => ST_ELIMINATE_PLAYER,
      ]
    ],

    // Place a rug adjacent to Assam
    ST_PLACE_CARPET => [
      "name" => "placeCarpet",
      "description" => clienttranslate('${actplayer} must place a rug adjacent to Assam'),
      "descriptionmyturn" => clienttranslate('${you} must place a rug adjacent to Assam'),
      "type" => "activeplayer",
      'args' => 'argPlaceCarpets',
      "possibleactions" => ["placeCarpet"],
      "transitions" => [
        "nextPlayer" => ST_NEXT_PLAYER,
        "rotateAssam" => ST_ROTATE_ASSAM,
        "zombiePass" => ST_ELIMINATE_PLAYER,
        'eliminate' => ST_ELIMINATE_PLAYER,
      ]
    ],

    // Check for end or go to next player
    ST_NEXT_PLAYER => [
      "name" => "nextPlayer",
      "description" => '',
      "type" => "game",
      "action" => "stNextPlayer",
      "updateGameProgression" => true,
      "transitions" => [
        "endGame" => ST_GAME_END,
        "startTurn" => ST_START_OF_TURN
      ]
    ],

    // Eliminate player and go to next player
    ST_ELIMINATE_PLAYER => [
      'name' => 'eliminatePlayer',
      'description' => '',
      'type' => 'game',
      'action' => 'stEliminatePlayer',
      'transitions' => [
        'startTurn' => ST_START_OF_TURN,
        'endgame' => ST_GAME_END,
      ],
    ],


    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_GAME_END => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ]
];
