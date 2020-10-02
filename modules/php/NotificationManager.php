<?php
namespace MKH;
use Marrakech;

class NotificationManager extends \APP_DbObject
{
  protected static function notifyAll($name, $msg, $data){
    Marrakech::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($pId, $name, $msg, $data){
    Marrakech::get()->notifyPlayer($pId, $name, $msg, $data);
  }


  public static function rollDice($face, $value){
    self::notifyAll("rollDice", '', [
      "face" => $face,
    ]);

    self::notifyAll("message", clienttranslate('${player_name} moves Assam ${roll} steps'), [
      "player_name" => Marrakech::get()->getActivePlayerName(),
      "roll" => $value,
    ]);
  }


  public static function rotateAssam($target){
    self::notifyAll("rotateAssam", '', [ "assam" => $target ]);
  }

  public static function moveAssam($target){
    self::notifyAll("moveAssam", '', [ "assam" => $target ]);
  }

  public static function rotate($delta){
    $msg = clienttranslate('${player_name} does not rotate Assam');
    if($delta < 0) $msg = clienttranslate('${player_name} rotates Assam left');
    if($delta > 0) $msg = clienttranslate('${player_name} rotates Assam right');

    self::notifyAll("message", $msg, [ "player_name" => Marrakech::get()->getActivePlayerName() ]);
  }


  public static function placeCarpet($cId, $x, $y, $orientation, $type){
    self::notifyAll("placeCarpet", clienttranslate('${player_name} places a rug'), [
      "player_name" => Marrakech::get()->getActivePlayerName(),
      "pId" => Marrakech::get()->getActivePlayerId(),
      'id' => $cId,
      'x' => $x,
      'y' => $y,
      'orientation' => $orientation,
      'type' => $type,
    ]);
  }

  public static function updatePlayersInfos($players){
    self::notifyAll("updatePlayersInfo", '', ['players' => $players ]);
  }

  public static function noTaxes($player){
    self::notifyAll("message", clienttranslate('${player_name} pays no taxes'), [
      'player_name' => $player['name'],
    ]);
  }

  public static function payTaxes($player, $taxer, $cost, $payed, $zone, $eliminated){
    // TODO
    //if($eliminated){
    self::notifyAll("payTaxes", clienttranslate('${player_name} pays ${payed} to ${taxer_name}'), [
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
