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

require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

class Marrakech extends Table
{
	public static $instance = null;
	function __construct()
	{
		parent::__construct();
		self::$instance = $this;

		self::initGameStateLabels([
			"RotateAssam" => OPTION_ROTATE_ASSAM,
		]);
	}

	protected function getGameName()
	{
		return "marrakech";
	}

	/*
	 *  setupNewGame:
	 */
	protected function setupNewGame($players, $options = [])
	{
		// Init Assam position
		MarrakechAssam::init();

		// Create and give each player 30 dirhams
		PlayerManager::setupNewGame($players);

		// Init game statistics
		StatManager::setupNewGame();

		// Activate first player (which is in general a good idea :) )
		$pId = $this->activeNextPlayer();
		StatManager::newTurn($pId);
	}




	/*
	 * getAllDatas:
	 *  Gather all informations about current game situation (visible by the current player).
	 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
	 */
	protected function getAllDatas() {
		return [
			'bplayers' => PlayerManager::getUiData(),
			'assam' => MarrakechAssam::get(),
			'carpets' => MarrakechBoard::getUiData(),
		];
	}


	/*
   * getGameProgression:
   */
	function getGameProgression()
	{
		// Compute total carpets depending on #players, remeaning players
		$carpetPerPlayer = [0, 0, 24, 15, 12];
		$carpetsTotal = PlayerManager::getPlayersLeft() * $carpetPerPlayer[self::getPlayersNumber()];

		// Get carpets left
		$carpetsLeft = PlayerManager::getCarpetsLeft();

		return (($carpetsTotal - intval($carpetsLeft)) * 100) / $carpetsTotal;
	}




	///////////////////////////////////////////////////////
	//////////// Next player / start of turn   ////////////
	///////////////////////////////////////////////////////

	/*
	 * stNextPlayer : go to the next (non eliminated) player
	 */
	function stNextPlayer($next = true)
	{
		if ($this->isEndOfGame()) {
			$this->gamestate->nextState("endGame");
		} else {
			// Active next player
			$pId = $next ? $this->activeNextPlayer() : $this->getActivePlayerId();
			if (PlayerManager::isEliminated($pId)) {
	      $this->stNextPlayer();
	      return;
	    }

			self::giveExtraTime($pId);
			StatManager::newTurn($pId);
			$this->gamestate->nextState("startTurn");
		}
	}

	/*
	 * isEndOfGame : ends the game whenever only 1 players is left, or when all the rugs where placed
	 */
	function isEndOfGame()
	{
		return PlayerManager::getPlayersLeft() == 1 || PlayerManager::getCarpetsLeft() == 0;
	}



	/*
   * stStartOfTurn: is called at the start of a player's turn and go to right step according to variant
   */
	function stStartOfTurn()
	{
		// Rotate assam at the beginning/end of turn depending on game option
		$newState = self::getGameStateValue('RotateAssam') == ROTATE_AT_END_OF_TURN? "moveAssam" : "rotateAssam";
		$this->gamestate->nextState($newState);
	}


	/*
   * stEliminatePlayer: this function is called when the active player is eliminated
   */
  public function stEliminatePlayer()
  {
    $pId = $this->getActivePlayerId();
    $this->activeNextPlayer();
    PlayerManager::eliminate($pId);
    $this->stNextPlayer(false);
  }


	///////////////////////////////////////
	//////////// Rotate Assam  ////////////
	///////////////////////////////////////
	function rotateAssam($delta)
	{
		self::checkAction('adjust');
		NotificationManager::rotate($delta);
		MarrakechAssam::rotate($delta);
		$newState = self::getGameStateValue('RotateAssam') == ROTATE_AT_END_OF_TURN? "nextPlayer" : "moveAssam";
		$this->gamestate->nextState($newState);
	}


	/////////////////////////////////////
	//////////// Move Assam  ////////////
	/////////////////////////////////////
	function rollDice()
	{
		// Roll die and move Assam
		$face = bga_rand(1, 6);
		$roll = $this->marrakechDice[$face];
		NotificationManager::rollDice($face, $roll);
		MarrakechAssam::move($roll);
		$eliminated = MarrakechBoard::payTaxes();

		$this->gamestate->nextState($eliminated? "eliminate" : "placeCarpet");
	}



	/////////////////////////////////////
	//////////// Place carpet  //////////
	/////////////////////////////////////
	function argPlaceCarpets()
	{
		return [
			'places' => MarrakechBoard::getPossiblePlaces()
		];
	}

	function placeCarpet($x1, $y1, $x2, $y2)
	{
		self::checkAction('placeCarpet');

		// Security : check that the coordinates are not falsified
	 	$places = MarrakechBoard::getPossiblePlaces();
		Utils::filter($places, function($place) use ($x1,$y1,$x2,$y2){
			return $x1 == $place['x1'] && $y1 == $place['y1']
					&& $x2 == $place['x2'] && $y2 == $place['y2'];
		});
		if (empty($places)){
			throw new BgaUserException( self::_("You can not place a carpet here") );
		}

		// Compute position and direction of carpet
		$x = min($x1, $x2);
		$y = min($y1, $y2);
		$orientation = $x1 == $x2? 'v' : 'h';

		// Place carpet
		$pId = self::getActivePlayerId();
		PlayerManager::placeCarpet($pId, $x, $y, $orientation);

		// Update score and UI
		PlayerManager::updateScores();
		PlayerManager::updateUi();

		$newState = (self::getGameStateValue('RotateAssam') == ROTATE_AT_END_OF_TURN && !$this->isEndOfGame())? "rotateAssam" : "nextPlayer";
		$this->gamestate->nextState($newState);
	}


	////////////////////////////////////
	////////////   Zombie   ////////////
	////////////////////////////////////
	/*
	 * zombieTurn:
	 *   This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
	 *   You can do whatever you want in order to make sure the turn of this player ends appropriately
	 */
	public function zombieTurn($state, $activePlayer) {
		if (array_key_exists('zombiePass', $state['transitions'])) {
			$this->gamestate->nextState('eliminate');
		} else {
			throw new BgaVisibleSystemException('Zombie player ' . $activePlayer . ' stuck in unexpected state ' . $state['name']);
		}
	}

	/////////////////////////////////////
	//////////   DB upgrade   ///////////
	/////////////////////////////////////
	// You don't have to care about this until your game has been published on BGA.
	// Once your game is on BGA, this method is called everytime the system detects a game running with your old Database scheme.
	// In this case, if you change your Database scheme, you just have to apply the needed changes in order to
	//   update the game database and allow the game to continue to run with your new version.
	/////////////////////////////////////
	/*
	 * upgradeTableDb
	 *  - int $from_version : current version of this game database, in numerical form.
	 *      For example, if the game was running with a release of your game named "140430-1345", $from_version is equal to 1404301345
	 */
	public function upgradeTableDb($from_version) {
	}
}
