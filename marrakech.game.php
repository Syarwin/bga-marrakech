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

use MKH\Assam;
use MKH\Board;
use MKH\PlayerManager;
use MKH\NotificationManager;
use MKH\StatManager;
use MKH\Utils;

$swdNamespaceAutoload = function ($class) {
   $classParts = explode('\\', $class);
   if ($classParts[0] == 'MKH') {
       array_shift($classParts);
       $file = dirname(__FILE__) . "/modules/php/" . implode(DIRECTORY_SEPARATOR, $classParts) . ".php";
       if (file_exists($file)) {
           require_once($file);
       }
   }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';


class Marrakech extends Table
{
	use MKH\States\TurnsTrait;
	use MKH\States\RotateAssamTrait;
	use MKH\States\MoveAssamTrait;
	use MKH\States\PlaceCarpetTrait;

	public static $instance = null;
	public static function get(){ return self::$instance; }
	function __construct()
	{
		parent::__construct();
		self::$instance = $this;

		self::initGameStateLabels([
			"RotateAssam" => OPTION_ROTATE_ASSAM,
			"diceFace"   => DICE_FACE,
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
		MKH\Assam::init();

		// Create and give each player 30 dirhams
		MKH\PlayerManager::setupNewGame($players);

		// Init game statistics
		MKH\StatManager::setupNewGame();
		// Init dice face
		self::setGameStateInitialValue('diceFace', 0);

		// Activate first player
		$pId = $this->activeNextPlayer();
		MKH\StatManager::newTurn($pId);
	}


	/*
	 * getAllDatas:
	 *  Gather all informations about current game situation (visible by the current player).
	 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
	 */
	protected function getAllDatas() {
		return [
			'players' => 	self::getCollectionFromDb("SELECT player_id id, player_score score, player_color, player_name FROM player "),
			'bplayers' => MKH\PlayerManager::getUiData(),
			'assam' => MKH\Assam::get(),
			'carpets' => MKH\Board::getUiData(),
			'dice' => $this->getGameStateValue('diceFace'),
		];
	}


	/*
   * getGameProgression:
   */
	function getGameProgression()
	{
		// Compute total carpets depending on #players, remeaning players
		$carpetPerPlayer = [0, 0, 24, 15, 12];
		$carpetsTotal = MKH\PlayerManager::getPlayersLeft() * $carpetPerPlayer[self::getPlayersNumber()];

		// Get carpets left
		$carpetsLeft = MKH\PlayerManager::getCarpetsLeft();

		return (($carpetsTotal - intval($carpetsLeft)) * 100) / $carpetsTotal;
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
