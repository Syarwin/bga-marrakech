<?php

class NotificationManager extends APP_GameClass
{
  public static function rollDice($n){
    Marrakech::$instance->notifyAllPlayers("diceRoll", clienttranslate('${player_name} moves Assam ${roll} steps'), [
        "player_name" => Marrakech::$instance->getActivePlayerName(),
        "roll" => $n,
      ]
    );
  }


  public static function moveAssam($target){
    Marrakech::$instance->notifyAllPlayers("assamMoved", '', [ "assam" => $target ]);
  }

  public static function rotate($delta){
    $msg = clienttranslate('${player_name} does not rotate Assam');
    if($delta < 0) $msg = clienttranslate('${player_name} rotates Assam left');
    if($delta > 0) $msg = clienttranslate('${player_name} rotates Assam right');

    Marrakech::$instance->notifyAllPlayers("message", $msg, [ "player_name" => Marrakech::$instance->getActivePlayerName() ]);
  }
}

?>
