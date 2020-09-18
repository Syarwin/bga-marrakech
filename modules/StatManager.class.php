<?php

class StatManager extends APP_GameClass
{
  protected static function init($type, $name, $value = 0){
    Marrakech::$instance->initStat($type, $name, $value);
  }

  protected static function inc($name, $player = null){
    Marrakech::$instance->incStat(1, $name, $player);
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


  /* PayTax
  // Update stats
  self::incStat($taxes_cost, 'player_money_paid', $player_id);
  self::incStat($taxes_cost, 'player_money_earned', $taxes_player_id);

  $table_highest_taxes_collected = self::getStat(
    'table_highest_taxes_collected'
  );
  $player_highest_taxes_collected = self::getStat(
    'player_highest_taxes_collected',
    $taxes_player_id
  );

  if ($table_highest_taxes_collected < $taxes_cost) {
    self::setStat($taxes_cost, 'table_highest_taxes_collected');
  }
  if ($player_highest_taxes_collected < $taxes_cost) {
    self::setStat(
      $taxes_cost,
      'player_highest_taxes_collected',
      $taxes_player_id
    );
  }
*/


/*
PlaceCarpet
$carpets_left = self::getUniqueValueFromDB( "SELECT carpet_$carpet_type FROM player_carpets WHERE player_id='$player_id'" );

// Update stats
$visibleCarpetsOnBoard = $this->getVisibleCarpetsOnBoard();
$current_carpet_zone = $this->getTaxesZone( $visibleCarpetsOnBoard, $player_id, $carpet_type, $x1, $y1 );
$current_carpet_zone_count = count( $current_carpet_zone );

$table_largest_carpet_zone = self::getStat( 'table_largest_carpet_zone' );
$player_largest_carpet_zone = self::getStat( 'player_largest_carpet_zone', $player_id );

if( $table_largest_carpet_zone < $current_carpet_zone_count ) {
  self::setStat( $current_carpet_zone_count, 'table_largest_carpet_zone' );
}
if( $player_largest_carpet_zone < $current_carpet_zone_count ) {
  self::setStat( $current_carpet_zone_count, 'player_largest_carpet_zone', $player_id );
}
*/
}

?>
