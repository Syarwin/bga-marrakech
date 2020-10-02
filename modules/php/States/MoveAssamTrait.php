<?php
namespace MKH\States;

use Marrakech;
use MKH\NotificationManager;
use MKH\Assam;
use MKH\Board;

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
		NotificationManager::rollDice($face, $roll);
		Assam::move($roll);
		$eliminated = Board::payTaxes();

		$this->gamestate->nextState($eliminated? "eliminate" : "placeCarpet");
	}
}
?>
