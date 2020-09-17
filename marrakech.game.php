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
		MarrakechPlayerManager::setupNewGame($players);

		// Init game statistics
		self::initStat('table', 'table_turns_number', 0);
		self::initStat('table', 'table_largest_carpet_zone', 0);
		self::initStat('table', 'table_highest_taxes_collected', 0);
		self::initStat('player', 'player_turns_number', 0);
		self::initStat('player', 'player_money_paid', 0);
		self::initStat('player', 'player_money_earned', 0);
		self::initStat('player', 'player_largest_carpet_zone', 0);
		self::initStat('player', 'player_highest_taxes_collected', 0);

		// Activate first player (which is in general a good idea :) )
		$player_id = $this->activeNextPlayer();

		// New turn increment stats
		self::incStat(1, 'table_turns_number');
		self::incStat(1, 'player_turns_number', $player_id);
	}




	/*
	 * getAllDatas:
	 *  Gather all informations about current game situation (visible by the current player).
	 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
	 */
	protected function getAllDatas() {
		return [
			'bplayers' => MarrakechPlayerManager::getUiData(),
			'assam' => MarrakechAssam::get(),
			'board' => MarrakechBoard::getUiData(),
		];
	}


	/*
   * getGameProgression:
   */
	function getGameProgression()
	{
		// Get total of carpets depending of the number of players and eliminated players
		$carpets_total = 0;

		$sql = "SELECT count(player_id) FROM player WHERE player_eliminated = 1";
		$players_eliminated = self::getUniqueValueFromDB($sql);

		switch (self::getPlayersNumber()) {
			case 2:
				$carpets_total = 48 - $players_eliminated * 24;
				break;
			case 3:
				$carpets_total = 45 - $players_eliminated * 15;
				break;
			case 4:
				$carpets_total = 48 - $players_eliminated * 12;
				break;
		}

		// Get the carpets left
		$sql =
			"SELECT SUM(carpet_1) + SUM(carpet_2) + SUM(carpet_3) + SUM(carpet_4) as TOTAL  FROM player_carpets";
		$carpets_left = self::getUniqueValueFromDB($sql);

		// Return the progression
		return round(
			(($carpets_total - intval($carpets_left)) * 100) / $carpets_total
		);
	}




	///////////////////////////////////////////////////////
	//////////// Next player / start of turn   ////////////
	///////////////////////////////////////////////////////


	function stNextPlayer()
	{
		if ($this->isEndOfGame()) {
			$this->gamestate->nextState("endGame");
		} else {
			// Active next player
			$pId = $this->activeNextPlayer();
			self::giveExtraTime($pId);
			$this->gamestate->nextState("startTurn");
		}
	}


	function isEndOfGame()
	{
		// TODO
		$sql =
			"SELECT SUM(carpet_1) + SUM(carpet_2) + SUM(carpet_3) + SUM(carpet_4) as TOTAL  FROM player_carpets";
		$carpets_left = self::getUniqueValueFromDB($sql);
		$sql = "SELECT count(player_id) FROM player WHERE player_eliminated = 1";
		$players_eliminated = self::getUniqueValueFromDB($sql);

		if (
			$carpets_left > 0 &&
			$players_eliminated < self::getPlayersNumber() - 1
		) {
			return false;
		} else {
			return true;
		}
	}




	function stStartOfTurn()
	{
		// New turn increment stats
		self::incStat(1, 'table_turns_number');
		self::incStat(1, 'player_turns_number', self::getActivePlayerId());

		// Rotate assam at the beginning/end of turn depending on game option
		$newState = self::getGameStateValue('RotateAssam') == ROTATE_AT_END_OF_TURN? "moveAssam" : "rotateAssam";
		$this->gamestate->nextState($newState);
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
/*
		$player_id = self::getActivePlayerId();
		$player_name = self::getActivePlayerName();
		$player_eliminated = false;
*/

		// Roll die and move Assam
		$face = bga_rand(1, 6);
		$roll = $this->marrakechDice[$face];
		NotificationManager::rollDice($face, $roll);
		MarrakechAssam::move($roll);
//		MarrakechBoard::payTaxes();

		$this->gamestate->nextState("roll");
//		$this->gamestate->nextState("nextPlayer");
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
			//$this->playerManager->eliminate(); TODO
			$this->gamestate->nextState('zombiePass');
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
