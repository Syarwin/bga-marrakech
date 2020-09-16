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
 * marrakech.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in marrakech_marrakech.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_marrakech_marrakech extends game_view
  {
    function getGameName() {
        return "marrakech";
    }    
  	function build_page( $viewArgs )
  	{		
        
      $this->page->begin_block( "marrakech_marrakech", "square" );
      
      $hor_scale = 80;
      $ver_scale = 80;
      for( $x=0; $x<=8; $x++ )
      {
          for( $y=0; $y<=8; $y++ )
          {
              $this->page->insert_block( "square", array(
                  'X' => $x,
                  'Y' => $y,
                  'LEFT' => round($x * $hor_scale),
                  'TOP' => round($y * $ver_scale)
              ) );
          }        
      }
      
      $this->page->begin_block( "marrakech_marrakech", "square_action" );
      
      for( $x=1; $x<=7; $x++ )
      {
          for( $y=1; $y<=7; $y++ )
          {
              $this->page->insert_block( "square_action", array(
                  'X' => $x,
                  'Y' => $y,
                  'LEFT' => round($x * $hor_scale),
                  'TOP' => round($y * $ver_scale)
              ) );
          }        
      }
        
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();

        /*********** Place your code below:  ************/
        
        
        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "marrakech_marrakech", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
  }
  

