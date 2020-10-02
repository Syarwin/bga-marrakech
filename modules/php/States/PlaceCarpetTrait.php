<?php
namespace MKH\States;

use Marrakech;
use MKH\NotificationManager;
use MKH\PlayerManager;
use MKH\Utils;
use MKH\Assam;
use MKH\Board;

/////////////////////////////////////
//////////// Place carpet  //////////
/////////////////////////////////////
trait placeCarpetTrait {
	function argPlaceCarpets()
	{
		return [
			'places' => Board::getPossiblePlaces()
		];
	}

	function placeCarpet($x1, $y1, $x2, $y2)
	{
		self::checkAction('placeCarpet');

		// Security : check that the coordinates are not falsified
	 	$places = Board::getPossiblePlaces();
		Utils::filter($places, function($place) use ($x1,$y1,$x2,$y2){
			return $x1 == $place['x1'] && $y1 == $place['y1']
					&& $x2 == $place['x2'] && $y2 == $place['y2'];
		});
		if (empty($places)){
			throw new \BgaUserException( self::_("You can not place a carpet here") );
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
}
?>
