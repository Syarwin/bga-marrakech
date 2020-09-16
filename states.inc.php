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

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 5 )
    ),
    
    // Player turn
    5 => array(
      "name" => "playerTurn",
      "description" => "",
      "type" => "game",
      "action" => "stPlayerTurn",
      "transitions" => array( "assamDirection" => 10, "assamMove" => 11, "zombiePass" => 20 )
    ),
    
    // Rotate or not Assam
    10 => array(
      "name" => "assamDirection",
      "description" => clienttranslate('${actplayer} may rotate Assam left or right'),
      "descriptionmyturn" => clienttranslate('${you} may rotate Assam left or right'),
      "type" => "activeplayer",
      "possibleactions" => array( "directionRight", "directionLeft", "skipDirection" ),
      "transitions" => array( "assamMove" => 11, "nextPlayer" => 20, "zombiePass" => 20 )
    ),
    
    // Throw the die, move Assam and pay taxes
    11 => array(
      "name" => "assamMove",
      "description" => clienttranslate('Assam is moving'),
      "type" => "game",
      "action" => "stAssamMove",
      "transitions" => array( "placeCarpet" => 12, "nextPlayer" => 20, "zombiePass" => 20 )
    ),
    
    // Place a rug adjacent to Assam
    12 => array(
      "name" => "placeCarpet",
      "description" => clienttranslate('${actplayer} must place a rug adjacent to Assam'),
      "descriptionmyturn" => clienttranslate('${you} must place a rug adjacent to Assam'),
      "type" => "activeplayer",
      "possibleactions" => array( "placeCarpet" ),
      "transitions" => array( "nextPlayer" => 20, "assamDirection" => 10, "zombiePass" => 20 )
    ),
    
    // Check for end or go to next player
    20 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "playerTurn" => 5 )
    ),  
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



