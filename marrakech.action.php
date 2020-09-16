<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * marrakech.action.php
 *
 * Marrakech main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/marrakech/marrakech/myAction.html", ...)
 *
 */
  
  
  class action_marrakech extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "marrakech_marrakech";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	public function directionRight()
    {
      self::setAjaxMode();
      
      $this->game->directionRight();

      self::ajaxResponse( );
    }
    
    public function directionLeft()
    {
      self::setAjaxMode();
      
      $this->game->directionLeft();

      self::ajaxResponse( );
    }
    
    public function skipDirection()
    {
      self::setAjaxMode();
      
      $this->game->skipDirection();

      self::ajaxResponse( );
    }
    
    public function placeCarpet()
    {
      self::setAjaxMode();
      
      $x1 = self::getArg( "x1", AT_posint, true );
      $y1 = self::getArg( "y1", AT_posint, true );
      $x2 = self::getArg( "x2", AT_posint, true );
      $y2 = self::getArg( "y2", AT_posint, true );
      $this->game->placeCarpet( $x1, $y1, $x2, $y2 );

      self::ajaxResponse( );
    }


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

  }
  

