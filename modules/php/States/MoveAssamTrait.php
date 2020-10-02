<?php
namespace MKH\States;

use Marrakech;
use MKH\NotificationManager;
use MKH\Assam;

/////////////////////////////////////
//////////// Move Assam  ////////////
/////////////////////////////////////
trait MoveAssamTrait {
	function rollDice()
	{
		// Roll die and move Assam
		$face = bga_rand(1, 6);
 		$this->setGameStateValue('diceFace', $face);
 		$roll = $this->marrakechDice[$face];
		\MKH\NotificationManager::rollDice($face, $roll);
		\MKH\Assam::move($roll);
		$eliminated = \MKH\Board::payTaxes();

		$this->gamestate->nextState($eliminated? "eliminate" : "placeCarpet");
	}
}
?>
