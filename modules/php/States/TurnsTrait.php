<?php
namespace MKH\States;

use Marrakech;
use MKH\NotificationManager;
use MKH\PlayerManager;
use MKH\StatManager;


///////////////////////////////////////////////////////
//////////// Next player / start of turn   ////////////
///////////////////////////////////////////////////////
trait TurnsTrait {
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
			if (\MKH\PlayerManager::isEliminated($pId)) {
	      $this->stNextPlayer();
	      return;
	    }

			self::giveExtraTime($pId);
			\MKH\StatManager::newTurn($pId);
			$this->gamestate->nextState("startTurn");
		}
	}

	/*
	 * isEndOfGame : ends the game whenever only 1 players is left, or when all the rugs where placed
	 */
	function isEndOfGame()
	{
		return \MKH\PlayerManager::getPlayersLeft() == 1 || \MKH\PlayerManager::getCarpetsLeft() == 0;
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
    \MKH\PlayerManager::eliminate($pId);
    $this->stNextPlayer(false);
  }
}
?>
