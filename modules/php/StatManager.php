<?php
namespace MKH;
use Marrakech;

class StatManager extends \APP_DbObject
{
  protected static function init($type, $name, $value = 0){
    Marrakech::get()->initStat($type, $name, $value);
  }

  protected static function inc($name, $player = null, $value = 1){
    Marrakech::get()->incStat($value, $name, $player);
  }

  protected static function get($name, $player = null){
    Marrakech::get()->getStat($name, $player);
  }

  protected static function set($value, $name, $player = null){
    Marrakech::get()->setStat($value, $name, $player);
  }


  public static function setupNewGame(){
    self::init('table', 'table_turns_number');
		self::init('table', 'table_largest_carpet_zone');
		self::init('table', 'table_highest_taxes_collected');
		self::init('player', 'player_turns_number');
		self::init('player', 'player_money_paid');
		self::init('player', 'player_money_earned');
		self::init('player', 'player_largest_carpet_zone');
		self::init('player', 'player_highest_taxes_collected');
  }


  public static function newTurn($pId){
    self::inc('table_turns_number');
    self::inc('player_turns_number', $pId);
  }


  public static function payTaxes($player, $taxer, $cost){
    self::inc('player_money_paid', $player['id'], $cost);
    self::inc('player_money_earned', $taxer['id'], $cost);

    $tableHighestPaid = self::get('table_highest_taxes_collected');
    if ($tableHighestPaid < $cost)
      self::set($cost, 'table_highest_taxes_collected');

    $taxerHighestPaid = self::get('player_highest_taxes_collected', $taxer['id']);
    if ($taxerHighestPaid < $cost)
      self::set($cost, 'player_highest_taxes_collected', $taxer['id']);
  }

  public static function placeCarpet($pId, $type, $pos){
    $currentZone = Board::getTaxesZone($pId, $type, $pos);
    $size = count($currentZone);

    $tableLargestZone = self::get('table_largest_carpet_zone');
    if ($tableLargestZone < $size )
      self::set($size, 'table_largest_carpet_zone');

    $playerLargestZone = self::get('player_largest_carpet_zone', $pId);
    if ($playerLargestZone < $size)
      self::set($size, 'player_largest_carpet_zone', $pId);
  }
}

?>
