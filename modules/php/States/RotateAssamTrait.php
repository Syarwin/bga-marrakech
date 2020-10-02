<?php
namespace MKH\States;

use Marrakech;
use MKH\NotificationManager;
use MKH\Assam;

///////////////////////////////////////
//////////// Rotate Assam  ////////////
///////////////////////////////////////
trait RotateAssamTrait {
  function rotateAssam($delta)
  {
    self::checkAction('adjust');
    NotificationManager::rotate($delta);
    Assam::rotate($delta);
    $newState = self::getGameStateValue('RotateAssam') == ROTATE_AT_END_OF_TURN? "nextPlayer" : "moveAssam";
    $this->gamestate->nextState($newState);
  }
}
?>
