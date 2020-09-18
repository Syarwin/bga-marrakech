<?php

class NotificationManager extends APP_GameClass
{
  public static function rollDice($face, $value){
    Marrakech::$instance->notifyAllPlayers("rollDice", '', [
      "face" => $face,
    ]);

    Marrakech::$instance->notifyAllPlayers("message", clienttranslate('${player_name} moves Assam ${roll} steps'), [
      "player_name" => Marrakech::$instance->getActivePlayerName(),
      "roll" => $value,
    ]);
  }


  public static function rotateAssam($target){
    Marrakech::$instance->notifyAllPlayers("rotateAssam", '', [ "assam" => $target ]);
  }

  public static function moveAssam($target){
    Marrakech::$instance->notifyAllPlayers("moveAssam", '', [ "assam" => $target ]);
  }

  public static function rotate($delta){
    $msg = clienttranslate('${player_name} does not rotate Assam');
    if($delta < 0) $msg = clienttranslate('${player_name} rotates Assam left');
    if($delta > 0) $msg = clienttranslate('${player_name} rotates Assam right');

    Marrakech::$instance->notifyAllPlayers("message", $msg, [ "player_name" => Marrakech::$instance->getActivePlayerName() ]);
  }


  public static function placeCarpet($cId, $x, $y, $orientation, $type){
    Marrakech::$instance->notifyAllPlayers("placeCarpet", '${player_name} places a rug', [
      "player_name" => Marrakech::$instance->getActivePlayerName(),
      "pId" => Marrakech::$instance->getActivePlayerId(),
      'id' => $cId,
      'x' => $x,
      'y' => $y,
      'orientation' => $orientation,
      'type' => $type,
    ]);
  }

  public static function updatePlayersInfos($players){
    Marrakech::$instance->notifyAllPlayers("updatePlayersInfo", '', ['players' => $players ]);
  }

  public static function noTaxes($player){
    Marrakech::$instance->notifyAllPlayers("message", clienttranslate('${player_name} pays no taxes'), [
      'player_name' => $player['name'],
    ]);
  }

  public static function payTaxes($player, $taxer, $cost, $payed, $zone, $eliminated){
    // TODO
    //if($eliminated){
    Marrakech::$instance->notifyAllPlayers("payTaxes", clienttranslate('${player_name} pays ${payed} to ${taxer_name}'), [
      "player_name" => $player['name'],
      "taxer_name" => $taxer['name'],
      "pId" => $player['id'],
      "taxerId" => $taxer['id'],
      "payed" => $payed,
      "zone" => $zone,
    ]);
  }

}

?>
