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
  * marrakech.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Marrakech extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
					"AssamVariant" => 100
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "marrakech";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)
				self::initStat( 'table', 'table_turns_number', 0 );
				self::initStat( 'table', 'table_largest_carpet_zone', 0 );
				self::initStat( 'table', 'table_highest_taxes_collected', 0 );
				self::initStat( 'player', 'player_turns_number', 0 );
				self::initStat( 'player', 'player_money_paid', 0 );
				self::initStat( 'player', 'player_money_earned', 0 );
				self::initStat( 'player', 'player_largest_carpet_zone', 0 );
				self::initStat( 'player', 'player_highest_taxes_collected', 0 );

				
				// Each player receive 30 dirhams, set initial score
				$sql = "UPDATE player SET player_money = 30, player_score = 30, player_score_aux = 30";
				self::DbQuery( $sql );
				
				// Distribute carpets (depending of number of players)
				$this->distributeCarpets( $players );
				
				// Set Assam on the middle of the board looking to the South
				$sql = "INSERT INTO assam (assam_x,assam_y,direction) VALUES (4,4,'S')";
				self::DbQuery( $sql );
				

        // Activate first player (which is in general a good idea :) )
        $player_id = $this->activeNextPlayer();
				
				// New turn increment stats
				self::incStat( 1, 'table_turns_number' );
				self::incStat( 1, 'player_turns_number', $player_id );

        /************ End of the game initialization *****/
    }
		
		private function distributeCarpets( $players )
		{
			$sql = "INSERT INTO player_carpets (player_id,carpet_type,carpet_1,carpet_2,carpet_3,carpet_4,next_carpet) VALUES ";
			$values = array();
			switch( count($players) ) {
				case 4:
					// 12 carpets each
					foreach ( array_keys( $players ) as $index => $player_id )
					{
						$values[] = "(" . $player_id . "," . 
							($index + 1) . "," .
							($index==0?'12':'0') . "," .
							($index==1?'12':'0') . "," .
							($index==2?'12':'0') . "," .
							($index==3?'12':'0') . "," .
							"0)";
					}
					break;
				case 3:
					// 15 carpets each
					foreach ( array_keys( $players ) as $index => $player_id )
					{
						$values[] = "(" . $player_id . "," . 
							($index + 1) . "," .
							($index==0?'15':'0') . "," .
							($index==1?'15':'0') . "," .
							($index==2?'15':'0') . "," .
							"0,0)";
					}
					break;
				case 2:
					// 24 carpets each (12 by color)
					foreach ( array_keys( $players ) as $index => $player_id )
					{
						$values[] = "(" . $player_id . "," . 
							($index * 2 + 1) . "," .
							($index==0?'12':'0') . "," .
							($index==0?'12':'0') . "," .
							($index==1?'12':'0') . "," .
							($index==1?'12':'0') . "," .
							($index==0?random_int(1,2):random_int(3,4)) . ")";
					}
					break;
			}
			$sql .= implode($values, ",");
			self::DbQuery( $sql );
		}

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    		
				$result['players_number'] = self::getPlayersNumber();
				
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT " .
				 	"player.player_id id, " .
					"player.player_eliminated player_eliminated, " .
					"player.player_score score, " .
					"player.player_money money, " .
					"player_carpets.carpet_type carpet_type, " .
					"player_carpets.carpet_1 carpet_1, " .
					"player_carpets.carpet_2 carpet_2, " .
					"player_carpets.carpet_3 carpet_3, " .
					"player_carpets.carpet_4 carpet_4, " .
					"player_carpets.next_carpet next_carpet " .
					"FROM player " .
					"INNER JOIN player_carpets ON player_carpets.player_id = player.player_id ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        $sql = "SELECT assam_x x, assam_y y, direction FROM assam LIMIT 1";
				$result['assam'] = self::getObjectFromDB( $sql );
				
				$sql = "SELECT turn, board_x x, board_y y, carpet_type, carpet_orientation, player_id FROM board ORDER BY turn ASC";
				$result['carpets_on_board'] = self::getCollectionFromDb( $sql );
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
			// Get total of carpets depending of the number of players and eliminated players
			$carpets_total = 0;
			
			$sql = "SELECT count(player_id) FROM player WHERE player_eliminated = 1";
			$players_eliminated = self::getUniqueValueFromDB( $sql );
			
      switch (self::getPlayersNumber())
			{
				case 2:
					$carpets_total = 48 - ($players_eliminated * 24);
					break;
				case 3:
					$carpets_total = 45 - ($players_eliminated * 15);
					break;
				case 4:
					$carpets_total = 48 - ($players_eliminated * 12);
					break;
			}
			
			// Get the carpets left
			$sql = "SELECT SUM(carpet_1) + SUM(carpet_2) + SUM(carpet_3) + SUM(carpet_4) as TOTAL  FROM player_carpets";
			$carpets_left = self::getUniqueValueFromDB( $sql );
			
			// Return the progression
      return round( ($carpets_total - intval($carpets_left)) * 100 / $carpets_total );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
		
		function updateScores()
		{
			// Set score for all players
			$sql = "SELECT player_id, player_money FROM player";
			$players = self::getCollectionFromDb( $sql );
			
			$visibleCarpetsOnBoard = $this->getVisibleCarpetsOnBoard();
			
			$scores = array();
			
			foreach ($players as $player) {
				$player_id = $player['player_id'];
				$player_money = $player['player_money'];
				$carpets_total = 0;
				
				for($x=1;$x<=7;$x++)
				{
					for($y=1;$y<=7;$y++)
					{
						if( $visibleCarpetsOnBoard[$x][$y]['player_id'] == $player_id )
						{
							$carpets_total += 1;
						}
					}
				}
				
				$player_score = intval($player_money) + $carpets_total;
				$player_score_aux = intval($player_money);
				
				// Update scores for current player
				self::DbQuery( "UPDATE player SET player_score = $player_score, player_score_aux = $player_score_aux WHERE player_id='$player_id'" );
				
				$scores[] = array(
					'player_id' => $player_id,
					'score' => $player_score
				);
			}
			
			return $scores;
		}
		
		function getVisibleCarpetsOnBoard()
		{
			$sql = "SELECT turn, board_x x, board_y y, carpet_type, carpet_orientation, player_id FROM board ORDER BY turn ASC";
			$carpets_on_board = self::getCollectionFromDb( $sql );
			
			$visibleCarpetsOnBoard = array();
			for($x=0;$x<=8;$x++)
			{
				$visibleCarpetsOnBoard[] = array();
				for($y=0;$y<=8;$y++)
				{
					$visibleCarpetsOnBoard[$x][] = null;
				}
			}
			
			foreach( $carpets_on_board as $carpet )
			{
				$carpet_id = $carpet['turn'];
				$x = $carpet['x'];
				$y = $carpet['y'];
				$carpet_type = $carpet['carpet_type'];
				$carpet_orientation = $carpet['carpet_orientation'];
				$player_id = $carpet['player_id'];
				
				if ( $carpet_orientation == 'h' )
				{
					$x2 = $x+1;
					$y2 = $y;
				}
				else
				{
					$x2 = $x;
					$y2 = $y+1;
				}
				
				// Set first cell of carpet
				$visibleCarpetsOnBoard[$x][$y] = array(
					'carpet_id' => $carpet_id,
					'carpet_type' => $carpet_type,
					'player_id' => $player_id
				);
				
				// Set second cell of carpet
				$visibleCarpetsOnBoard[$x2][$y2] = array(
					'carpet_id' => $carpet_id,
					'carpet_type' => $carpet_type,
					'player_id' => $player_id
				);
			}
			
			return $visibleCarpetsOnBoard;
		}
		
		function generateAssamPath($assam, $roll)
		{
			$path = array();
			
			$current_x = $assam['x'];
			$current_y = $assam['y'];
			$current_direction = $assam['direction'];
			
			for($i=0; $i<$roll; $i++)
			{
				switch( $current_direction )
				{
					case 'S':
						$current_y += 1;
						break;
					case 'E':
						$current_x += 1;
						break;
					case 'N':
						$current_y -= 1;
						break;
					case 'W':
						$current_x -= 1;
						break;
				}
				
				$path[] = array( 'x' => $current_x, 'y' => $current_y, 'direction' => $current_direction );
				
				// Check if on a border of the board
				if ($current_x == 0)
				{
					switch ($current_y) {
						case 1:
						case 3:
						case 5:
							$path[] = array( 'x' => $current_x, 'y' => $current_y+1, 'direction' => 'S' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y+1, 'direction' => 'E' );
							break;
						case 2:
						case 4:
						case 6:
							$path[] = array( 'x' => $current_x, 'y' => $current_y-1, 'direction' => 'N' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y-1, 'direction' => 'E' );
							break;
						case 7:
							$path[] = array( 'x' => $current_x, 'y' => $current_y+1, 'direction' => 'S' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y+1, 'direction' => 'E' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y, 'direction' => 'N' );
							break;
					}
				}
				
				if ($current_x == 8)
				{
					switch ($current_y) {
						case 2:
						case 4:
						case 6:
							$path[] = array( 'x' => $current_x, 'y' => $current_y+1, 'direction' => 'S' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y+1, 'direction' => 'W' );
							break;
						case 3:
						case 5:
						case 7:
							$path[] = array( 'x' => $current_x, 'y' => $current_y-1, 'direction' => 'N' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y-1, 'direction' => 'W' );
							break;
						case 1:
							$path[] = array( 'x' => $current_x, 'y' => $current_y-1, 'direction' => 'N' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y-1, 'direction' => 'W' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y, 'direction' => 'S' );
							break;
					}
				}
				
				if ($current_y == 0)
				{
					switch ($current_x) {
						case 1:
						case 3:
						case 5:
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y, 'direction' => 'E' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y+1, 'direction' => 'S' );
							break;
						case 2:
						case 4:
						case 6:
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y, 'direction' => 'W' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y+1, 'direction' => 'S' );
							break;
						case 7:
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y, 'direction' => 'E' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y+1, 'direction' => 'S' );
							$path[] = array( 'x' => $current_x, 'y' => $current_y+1, 'direction' => 'W' );
							break;
					}
				}
				
				if ($current_y == 8)
				{
					switch ($current_x) {
						case 2:
						case 4:
						case 6:
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y, 'direction' => 'E' );
							$path[] = array( 'x' => $current_x+1, 'y' => $current_y-1, 'direction' => 'N' );
							break;
						case 3:
						case 5:
						case 7:
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y, 'direction' => 'W' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y-1, 'direction' => 'N' );
							break;
						case 1:
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y, 'direction' => 'W' );
							$path[] = array( 'x' => $current_x-1, 'y' => $current_y-1, 'direction' => 'N' );
							$path[] = array( 'x' => $current_x, 'y' => $current_y-1, 'direction' => 'E' );
							break;
					}
				}
				
				// Update current variables
				$last_path = end($path);
				$current_x = $last_path['x'];
				$current_y = $last_path['y'];
				$current_direction = $last_path['direction'];
			}
			
			return $path;
		}
		
		function getTaxesZone( $visibleCarpetsOnBoard, $taxes_player_id, $carpet_type, $x, $y )
		{
			$taxes_zone = array();
			$next_cells = array();
			$explored_cells = array();
			
			// Set current cell to the zone
			$taxes_zone[] = array( 'x' => $x, 'y' => $y );
			$explored_cells[] = array( 'x' => $x, 'y' => $y );
			
			// Set next cells
			if ( $x > 1 )
			{
				$next_cells[] = array( 'x' => $x - 1, 'y' => $y );
				$explored_cells[] = array( 'x' => $x - 1, 'y' => $y );
			}
			if ( $x < 7 ) 
			{
				$next_cells[] = array( 'x' => $x + 1, 'y' => $y );
				$explored_cells[] = array( 'x' => $x + 1, 'y' => $y );
			}
			if ( $y > 1 )
			{
				$next_cells[] = array( 'x' => $x, 'y' => $y - 1 );
				$explored_cells[] = array( 'x' => $x, 'y' => $y - 1 );
			}
			if ( $y < 7 )
			{
				$next_cells[] = array( 'x' => $x, 'y' => $y + 1 );
				$explored_cells[] = array( 'x' => $x, 'y' => $y + 1 );
			}
			
			while ( count($next_cells) > 0 )
			{
				$next_cells_tmp = array();
				foreach ($next_cells as $cell)
				{
					$x = $cell['x'];
					$y = $cell['y'];
					// Check for player_id and carpet_type (2 players games)
					if ( $visibleCarpetsOnBoard[$x][$y]['player_id'] == $taxes_player_id 
						&& $visibleCarpetsOnBoard[$x][$y]['carpet_type'] == $carpet_type )
					{
						// Add cell to taxes zone
						$taxes_zone[] = array( 'x' => $x, 'y' => $y );
						
						// Set next cells
						if ( $x > 1 && !$this->isInArrayOfCells($next_cells_tmp, $x-1, $y) && !$this->isInArrayOfCells($explored_cells, $x-1, $y) )
						{
							$next_cells_tmp[] = array( 'x' => $x - 1, 'y' => $y );
							$explored_cells[] = array( 'x' => $x - 1, 'y' => $y );
						}
						if ( $x < 7 && !$this->isInArrayOfCells($next_cells_tmp, $x+1, $y) && !$this->isInArrayOfCells($explored_cells, $x+1, $y) )
						{
							$next_cells_tmp[] = array( 'x' => $x + 1, 'y' => $y );
							$explored_cells[] = array( 'x' => $x + 1, 'y' => $y );
						}
						if ( $y > 1 && !$this->isInArrayOfCells($next_cells_tmp, $x, $y-1) && !$this->isInArrayOfCells($explored_cells, $x, $y-1) )
						{
							$next_cells_tmp[] = array( 'x' => $x, 'y' => $y - 1 );
							$explored_cells[] = array( 'x' => $x, 'y' => $y - 1 );
						}
						if ( $y < 7 && !$this->isInArrayOfCells($next_cells_tmp, $x, $y+1) && !$this->isInArrayOfCells($explored_cells, $x, $y+1) )
						{
							$next_cells_tmp[] = array( 'x' => $x, 'y' => $y + 1 );
							$explored_cells[] = array( 'x' => $x, 'y' => $y + 1 );
						}
					}
				}
				$next_cells = $next_cells_tmp;
			}
			
			return $taxes_zone;
		}
		
		function isInArrayOfCells($arrayOfCells, $x, $y)
		{
			$result = false;
			
			foreach ($arrayOfCells as $cell) {
				if ( $cell['x'] == $x && $cell['y'] == $y )
				{
					$result = true;
					break;
				}
			}
			
			return $result;
		}
		
		function getPossiblePlaces()
		{
			$sql = "SELECT assam_x x,assam_y y,direction FROM assam LIMIT 1";
			$assam = self::getObjectFromDB( $sql );
			
			$possiblePlaces = array();
			
			if( $assam['x'] > 1 )
			{
				if( $assam['x'] > 2 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x']-1, 'y1' => $assam['y'], 'x2' => $assam['x']-2, 'y2' => $assam['y']
					) );
				}
				if( $assam['y'] > 1 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x']-1, 'y1' => $assam['y'], 'x2' => $assam['x']-1, 'y2' => $assam['y']-1
					) );
				}
				if( $assam['y'] < 7 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x']-1, 'y1' => $assam['y'], 'x2' => $assam['x']-1, 'y2' => $assam['y']+1
					) );
				}
			}
			
			if( $assam['x'] < 7 )
			{
				if( $assam['x'] < 6 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x']+1, 'y1' => $assam['y'], 'x2' => $assam['x']+2, 'y2' => $assam['y']
					) );
				}
				if( $assam['y'] > 1 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x']+1, 'y1' => $assam['y'], 'x2' => $assam['x']+1, 'y2' => $assam['y']-1
					) );
				}
				if( $assam['y'] < 7 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x']+1, 'y1' => $assam['y'], 'x2' => $assam['x']+1, 'y2' => $assam['y']+1
					) );
				}
			}
			
			if( $assam['y'] > 1 )
			{
				if( $assam['y'] > 2 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x'], 'y1' => $assam['y']-1, 'x2' => $assam['x'], 'y2' => $assam['y']-2
					) );
				}
				if( $assam['x'] > 1 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x'], 'y1' => $assam['y']-1, 'x2' => $assam['x']-1, 'y2' => $assam['y']-1
					) );
				}
				if( $assam['x'] < 7 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x'], 'y1' => $assam['y']-1, 'x2' => $assam['x']+1, 'y2' => $assam['y']-1
					) );
				}
			}
			
			if( $assam['y'] < 7 )
			{
				if( $assam['y'] < 6 ) {
					array_push($possiblePlaces, array(
						'x1' => $assam['x'], 'y1' => $assam['y']+1, 'x2' => $assam['x'], 'y2' => $assam['y']+2
					) );
				}
				if( $assam['x'] > 1 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x'], 'y1' => $assam['y']+1, 'x2' => $assam['x']-1, 'y2' => $assam['y']+1
					) );
				}
				if( $assam['x'] < 7 )
				{
					array_push($possiblePlaces, array(
						'x1' => $assam['x'], 'y1' => $assam['y']+1, 'x2' => $assam['x']+1, 'y2' => $assam['y']+1
					) );
				}
			}
			
			return $possiblePlaces;
		}
		
		function isEndOfGame()
		{
			$sql = "SELECT SUM(carpet_1) + SUM(carpet_2) + SUM(carpet_3) + SUM(carpet_4) as TOTAL  FROM player_carpets";
			$carpets_left = self::getUniqueValueFromDB( $sql );
			$sql = "SELECT count(player_id) FROM player WHERE player_eliminated = 1";
			$players_eliminated = self::getUniqueValueFromDB( $sql );
			
			if ( $carpets_left > 0 && $players_eliminated < (self::getPlayersNumber()-1) )
			{
				return false;
			}
			else
			{
				return true;
			}
		}


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 
		
		
		function directionRight()
		{
			// Check that it's the player turn and this is a possible action at current state
			self::checkAction('directionRight');
			
			// Rotate assam to right (clockwise)
			$sql = "
			UPDATE assam
			SET direction = CASE 
				WHEN direction = 'S' THEN 'W'
				WHEN direction = 'E' THEN 'S'
				WHEN direction = 'N' THEN 'E'
				WHEN direction = 'W' THEN 'N'
			END
			";
			self::DbQuery( $sql );
			
			// Get the new direction of Assam
			$sql = "SELECT direction FROM assam LIMIT 1";
			$direction = self::getUniqueValueFromDB( $sql );
			
			// Notify players
			self::notifyAllPlayers( "assamDirection", clienttranslate( '${player_name} rotates Assam right' ), array(
				"player_name" => self::getActivePlayerName(),
				"newAssamDirection" => $direction
			) );
			
			// Go to next state
			if ( self::getGameStateValue('AssamVariant') == 2 )
			{
				// Game variant : Assam is rotated at the end of turn
				$this->gamestate->nextState( "nextPlayer" );
			}
			else
			{
				// Normal mode
				$this->gamestate->nextState( "assamMove" );
			}
		}
		
		function directionLeft()
		{
			// Check that it's the player turn and this is a possible action at current state
			self::checkAction('directionLeft');
			
			// Rotate assam to left (anticlockwise)
			$sql = "
			UPDATE assam
			SET direction = CASE 
				WHEN direction = 'S' THEN 'E'
				WHEN direction = 'E' THEN 'N'
				WHEN direction = 'N' THEN 'W'
				WHEN direction = 'W' THEN 'S'
			END
			";
			self::DbQuery( $sql );
			
			// Get the new direction of Assam
			$sql = "SELECT direction FROM assam LIMIT 1";
			$direction = self::getUniqueValueFromDB( $sql );
			
			// Notify players
			self::notifyAllPlayers( "assamDirection", clienttranslate( '${player_name} rotates Assam left' ), array(
				"player_name" => self::getActivePlayerName(),
				"newAssamDirection" => $direction
			) );
			
			// Go to next state
			if ( self::getGameStateValue('AssamVariant') == 2 )
			{
				// Game variant : Assam is rotated at the end of turn
				$this->gamestate->nextState( "nextPlayer" );
			}
			else
			{
				// Normal mode
				$this->gamestate->nextState( "assamMove" );
			}
		}
		
		function skipDirection()
		{
			// Check that it's the player turn and this is a possible action at current state
			self::checkAction('skipDirection');
			
			// No update here
			
			// Notify players
			self::notifyAllPlayers( "assamDirection", clienttranslate( '${player_name} does not rotate Assam' ), array(
				"player_name" => self::getActivePlayerName()
			) );
			
			// Go to next state
			if ( self::getGameStateValue('AssamVariant') == 2 )
			{
				// Game variant : Assam is rotated at the end of turn
				$this->gamestate->nextState( "nextPlayer" );
			}
			else
			{
				// Normal mode
				$this->gamestate->nextState( "assamMove" );
			}
		}
		
		function placeCarpet( $x1, $y1, $x2, $y2 )
		{
			// Check that it's the player turn and this is a possible action at current state
			self::checkAction('placeCarpet');
			
			$player_id = self::getActivePlayerId();
			
			// Security : check that the coordinates are not falsified
			$possiblePlaces = $this->getPossiblePlaces();
			$found_pp = false;
			foreach ($possiblePlaces as $possiblePlace) {
				if ( $x1 == $possiblePlace['x1'] && $y1 == $possiblePlace['y1'] 
					&& $x2 == $possiblePlace['x2'] && $y2 == $possiblePlace['y2'] ) {
					$found_pp = true;
					break;
				}
			}
			
			if ( !$found_pp )
			{
				throw new BgaUserException( self::_("You can not place a carpet here") );
			}
			
			// Check if carpet can be placed here
			$visibleCarpetsOnBoard = $this->getVisibleCarpetsOnBoard();
			if($visibleCarpetsOnBoard[$x1][$y1]['carpet_id'] != null && $visibleCarpetsOnBoard[$x2][$y2]['carpet_id'] != null
				&& $visibleCarpetsOnBoard[$x1][$y1]['carpet_id'] == $visibleCarpetsOnBoard[$x2][$y2]['carpet_id'] 
				&& $visibleCarpetsOnBoard[$x1][$y1]['player_id'] != $player_id)
			{
				throw new BgaUserException( self::_("An opponents rug cannot be entirely covered in one go") );
			}
			
			$carpet_orientation = null;
			$carpet_x = null;
			$carpet_y = null;
			$carpet_type = null;
			
			if ( $x1 == $x2 )
			{
				$carpet_orientation = 'v';
				$carpet_x = $x1;
				$carpet_y = min($y1,$y2);
			}
			elseif ( $y1 == $y2 )
			{
				$carpet_orientation = 'h';
				$carpet_x = min($x1,$x2);
				$carpet_y = $y1;
			}
			
			$sql = "SELECT carpet_type, carpet_1, carpet_2, carpet_3, carpet_4, next_carpet FROM player_carpets WHERE player_id='$player_id'";
			$player_carpets = self::getObjectFromDB( $sql );
			
			$next_carpet = $player_carpets['next_carpet'];
			// Save carpet and remove carpet from stock in db
			if ( self::getPlayersNumber() == 2 )
			{
				// at 2 players use next_carpet for carpet_type
				$carpet_type = $next_carpet;
			}
			else
			{
				$carpet_type = $player_carpets['carpet_type'];
			}
			
			// Add carpet to board
			$sql = "INSERT INTO board (board_x,board_y,carpet_type,carpet_orientation,player_id) " .
				"VALUES ($carpet_x,$carpet_y,$carpet_type,'$carpet_orientation',$player_id)";
			self::DbQuery( $sql );
			
			$carpet_id = self::getUniqueValueFromDB( "SELECT max(turn) last_turn FROM board" );
			
			// Remove carpet from player stock
			$sql = "UPDATE player_carpets SET carpet_$carpet_type = carpet_$carpet_type - 1 WHERE player_id='$player_id'";
			self::DbQuery( $sql );
			
			// Update next_carpet in db for next move
			if ( self::getPlayersNumber() == 2 )
			{
				$sql = "SELECT carpet_type, carpet_1, carpet_2, carpet_3, carpet_4, next_carpet FROM player_carpets WHERE player_id='$player_id'";
				$player_carpets = self::getObjectFromDB( $sql );
				
				if ( $player_carpets['carpet_type'] == 1 )
				{
					if ( (intval($player_carpets['carpet_1']) + intval($player_carpets['carpet_2'])) <= 0 ) { $next_carpet = 0; }
					elseif ( bga_rand( 1, intval($player_carpets['carpet_1']) + intval($player_carpets['carpet_2']) ) <= intval($player_carpets['carpet_1']) ) { $next_carpet = 1; }
					else { $next_carpet = 2; }
				}
				else
				{
					if ( (intval($player_carpets['carpet_3']) + intval($player_carpets['carpet_4'])) <= 0 ) { $next_carpet = 0; }
					elseif ( bga_rand( 1, intval($player_carpets['carpet_3']) + intval($player_carpets['carpet_4']) ) <= intval($player_carpets['carpet_3']) ) { $next_carpet = 3; }
					else { $next_carpet = 4; }
				}
				
				// Update next_carpet in db
				self::DbQuery( "UPDATE player_carpets SET next_carpet = $next_carpet WHERE player_id='$player_id'" );
			}
			
			$carpets_left = self::getUniqueValueFromDB( "SELECT carpet_$carpet_type FROM player_carpets WHERE player_id='$player_id'" );
			
			// Update stats
			$visibleCarpetsOnBoard = $this->getVisibleCarpetsOnBoard();
			$current_carpet_zone = $this->getTaxesZone( $visibleCarpetsOnBoard, $player_id, $carpet_type, $x1, $y1 );
			$current_carpet_zone_count = count( $current_carpet_zone );
			
			$table_largest_carpet_zone = self::getStat( 'table_largest_carpet_zone' );
			$player_largest_carpet_zone = self::getStat( 'player_largest_carpet_zone', $player_id );
			
			if( $table_largest_carpet_zone < $current_carpet_zone_count ) {
				self::setStat( $current_carpet_zone_count, 'table_largest_carpet_zone' );
			}
			if( $player_largest_carpet_zone < $current_carpet_zone_count ) {
				self::setStat( $current_carpet_zone_count, 'player_largest_carpet_zone', $player_id );
			}
			
			// Notify players
			self::notifyAllPlayers( "carpetPlaced", clienttranslate( '${player_name} places a rug' ), array(
				"playerId" => $player_id,
				"player_name" => self::getActivePlayerName(),
				"carpet_id" => $carpet_id,
				"x" => $carpet_x,
				"y" => $carpet_y,
				"carpet_orientation" => $carpet_orientation,
				"carpet_type" => $carpet_type,
				"carpets_left" => $carpets_left,
				"nextCarpet" => $next_carpet
			) );
			
			// Update scores and notify all players
			$scores = $this->updateScores();
			self::notifyAllPlayers( "updateScores", '', array(
				'scores' => $scores
			) );
			
			// Go to next state
			if ( self::getGameStateValue('AssamVariant') == 2 && !$this->isEndOfGame() )
			{
				// Game variant : Assam is rotated at the end of turn
				$this->gamestate->nextState( "assamDirection" );
			}
			else
			{
				// Normal mode
				$this->gamestate->nextState( "nextPlayer" );
			}
		}
    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in marrakech.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */
		
		function stPlayerTurn()
		{
			if ( self::getGameStateValue('AssamVariant') == 2 )
			{
				// Game variant : Assam is rotated at the end of turn
				$this->gamestate->nextState( "assamMove" );
			}
			else
			{
				// Normal mode
				$this->gamestate->nextState( "assamDirection" );
			}
		}
		
		function stAssamMove()
		{
			$player_id = self::getActivePlayerId();
			$player_name = self::getActivePlayerName();
			$player_eliminated = false;
			
			// Roll die
			$roll = $this->marrakechDice[ bga_rand(1, 6) ];
			
			// Get current position and direction of Assam
			$sql = "SELECT assam_x x,assam_y y,direction FROM assam LIMIT 1";
			$assam = self::getObjectFromDB( $sql );
			
			// Move Assam
			$path = $this->generateAssamPath( $assam, $roll );
			
			// Update Assam position / direction
			$last_path = end($path);
			$sql = "UPDATE assam SET assam_x = " . $last_path['x'] . ",assam_y = " . $last_path['y'] . ",direction = '" . $last_path['direction'] . "'";
			self::DbQuery( $sql );
			
			// Notify players
			self::notifyAllPlayers( "diceRoll", clienttranslate( '${player_name} moves Assam ${roll} steps'), array(
				"player_name" => $player_name,
				"roll" => $roll,
				"path" => $path,
				"assam" => $last_path
			) );
			
			// Check for taxes
			$visibleCarpetsOnBoard = $this->getVisibleCarpetsOnBoard();
			
			// Check if carpet is owned by eliminated player
			$currentCarpetPlayerId = $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['player_id'];
			$currentCarpetPlayerIsEliminated = self::getUniqueValueFromDB("SELECT player_eliminated FROM player WHERE player_id='$currentCarpetPlayerId'");
			$currentCarpetPlayerIsEliminated = intval($currentCarpetPlayerIsEliminated);
			
			if ( $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']] != null 
				&& $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['player_id'] != $player_id
				&& $currentCarpetPlayerIsEliminated == 0 )
			{
				// Pay taxes
				$taxes_player_id = $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['player_id'];
				$carpet_type = $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['carpet_type'];
				
				$taxes_zone = $this->getTaxesZone( $visibleCarpetsOnBoard, $taxes_player_id, $carpet_type, $last_path['x'], $last_path['y'] );
				$taxes_cost = count( $taxes_zone );
				
				$playerTaxes = self::getObjectFromDB( "SELECT player_id, player_name, player_money FROM player WHERE player_id='$taxes_player_id'" );

				$player_money = self::getUniqueValueFromDB( "SELECT player_money FROM player WHERE player_id='$player_id'" );
				if ( $player_money < $taxes_cost )
				{
					// Player is eliminated !!!
					$player_eliminated = true;
					$taxes_cost = $player_money;
				}
				
				// Update stats
				self::incStat( $taxes_cost, 'player_money_paid', $player_id );
				self::incStat( $taxes_cost, 'player_money_earned', $taxes_player_id );
				
				$table_highest_taxes_collected = self::getStat( 'table_highest_taxes_collected' );
				$player_highest_taxes_collected = self::getStat( 'player_highest_taxes_collected', $taxes_player_id );
				
				if( $table_highest_taxes_collected < $taxes_cost ) {
					self::setStat( $taxes_cost, 'table_highest_taxes_collected' );
				}
				if( $player_highest_taxes_collected < $taxes_cost ) {
					self::setStat( $taxes_cost, 'player_highest_taxes_collected', $taxes_player_id );
				}
				
				$sql_update1 = "UPDATE player SET player_money = (player_money - $taxes_cost) WHERE player_id='$player_id'";
				$sql_update2 = "UPDATE player SET player_money = (player_money + $taxes_cost) WHERE player_id='$taxes_player_id'";
				self::DbQuery( $sql_update1 );
				self::DbQuery( $sql_update2 );
				
				// Updated money of players
				$player_money -= $taxes_cost;
				$player_taxes_money = $playerTaxes['player_money'] + $taxes_cost;
				
				// Notify players
				self::notifyAllPlayers( "payTaxes", clienttranslate( '${player_name} pays ${taxesCost} to ${playerTaxesName}'), array(
					"playerId" => $player_id,
					"player_name" => $player_name,
					"playerMoney" => $player_money,
					"playerTaxesId" => $playerTaxes['player_id'],
					"playerTaxesName" => $playerTaxes['player_name'],
					"playerTaxesMoney" => $player_taxes_money,
					"taxesZone" => $taxes_zone,
					"taxesCost" => $taxes_cost
				) );
			}
			
			if ( $player_eliminated )
			{
				// Remove all carpets from eliminated player, score will be set to 0
				self::DbQuery( "UPDATE player_carpets SET carpet_1=0, carpet_2=0, carpet_3=0, carpet_4=0, next_carpet=0 WHERE player_id='$player_id'" );
			}
			
			// Update scores and notify all players
			$scores = $this->updateScores();
			self::notifyAllPlayers( "updateScores", '', array(
				'scores' => $scores
			) );
			
			if ( $player_eliminated )
			{
				// Player has been eliminated, notify and go to nextPlayer
				self::eliminatePlayer( $player_id );
				$sql = "SELECT carpet_type FROM player_carpets WHERE player_id='$player_id'";
				$carpet_type = self::getUniqueValueFromDB( $sql );
				
				self::notifyAllPlayers( "playerEliminatedInfos", '', array(
					'playerId' => $player_id,
					'carpetType' => $carpet_type
				) );
				
				$this->gamestate->nextState( "nextPlayer" );
			}
			else
			{
				// Go to next state (placeCarpet)
				$this->gamestate->nextState( "placeCarpet" );
			}
		}
		
		function stNextPlayer()
		{			
			if ( !$this->isEndOfGame() )
			{
				// Active next player
				$player_id = $this->activeNextPlayer();
				
				// New turn increment stats
				self::incStat( 1, 'table_turns_number' );
				self::incStat( 1, 'player_turns_number', $player_id );
				
				self::giveExtraTime( $player_id );
				$this->gamestate->nextState( "playerTurn" );
			}
			else
			{
				// Game ended
				$this->gamestate->nextState( "endGame" );
			}
			
		}

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
			
			if ( $statename == 'playerTurn'
				|| $statename == 'assamDirection'
			 	|| $statename == 'assamMove' 
				|| $statename == 'placeCarpet' )
			{
				$this->gamestate->nextState( "zombiePass" );
			}
    	else {
    		throw new feException( "Zombie mode not supported at this game state: ".$statename );
    	}
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
