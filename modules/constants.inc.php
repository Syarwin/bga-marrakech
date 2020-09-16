<?php

/*
 * State constants
 */
define('ST_GAME_SETUP', 1);
define('ST_START_OF_TURN', 5);
define('ST_ROTATE_ASSAM', 10);
define('ST_MOVE_ASSAM', 11);
define('ST_PLACE_CARPET', 12);

define('ST_NEXT_PLAYER', 20);
define('ST_GAME_END', 99);


/*
 * Game options
 */

define("OPTION_ROTATE_ASSAM", 100);
define("ROTATE_AT_START_OF_TURN", 1);
define("ROTATE_AT_END_OF_TURN", 2);


/*
 * Globals
 */

define("ASSAM_X", 30);
define("ASSAM_Y", 31);
define("ASSAM_DIR", 32);

define("NORTH", 0);
define("EAST", 1);
define("SOUTH", 2);
define("WEST", 3);
