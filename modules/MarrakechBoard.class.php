<?php

class MarrakechBoard extends APP_GameClass
{
  public static function getUiData(){
    return self::getObjectListFromDb("SELECT * FROM carpets ORDER BY id ASC");
  }


  /*
   *  Return the current status of board :  9*9 array with inside cell [x][y]
   *    - either null if no carpet on this cell
   *    - or visible carpet [id, type, pId] otherwise
   */
  function getBoard() {
    $board = [];
    for ($x = 0; $x <= 8; $x++) {
      $board[] = [];
      for ($y = 0; $y <= 8; $y++) {
        $board[$x][] = null;
      }
    }

    $sql = "SELECT * FROM carpets ORDER BY id ASC";
    foreach (self::getObjectListFromDb($sql) as $carpet){
      $data = [
        'id' => $carpet['id'],
        'type' => $carpet['type'],
        'pId' => $carpet['player_id'],
      ];

      // Set first cell of carpet
      $x = $carpet['x'];
      $y = $carpet['y'];
      $board[$x][$y] = $data;

      // Set second cell of carpet
      $x2 = $x + ($carpet['orientation'] == 'h'? 1 : 0);
      $y2 = $y + ($carpet['orientation'] == 'v'? 1 : 0);
      $board[$x2][$y2] = $data;
    }

    return $board;
  }



  function getTaxesZone($pId, $type, $pos){
		$zone = [];
		$queue = [];
    $board = self::getBoard();

    $board[$pos['x']][$pos['y']]['visited'] = true;
    array_push($queue, $pos);
		while(!empty($queue)){
      $cell = array_shift($queue);
      array_push($zone, $cell);

      for($i = 0; $i < 4; $i++){
        $npos = self::moveInDir($cell, $i);
        // Position must be inside the board and not be on Asssam
        if(!self::isPositionValid($npos, $pos)) continue;
        // Should contain a carpet
        $carpet = &$board[$npos['x']][$npos['y']];
        if(is_null($carpet)) continue;
        // Should not be already visited
        if(array_key_exists('visited', $carpet)) continue;
        $carpet['visited'] = true;
        // Should belong to same player and same type of carpet (in particular for 2 players game)
        if($carpet['pId'] != $pId || $carpet['type'] != $type) continue;

        // If this is reached, add the pos to the queue to process it later
        array_push($queue, $npos);
      }
    }

    return $zone;
  }


  /*
   * moveInDir : return new position if moving in given direction from starting pos
   */
  public static function moveInDir($pos, $dir){
    return [
      'x' => $pos['x'] + MarrakechAssam::$deltas[$dir]['x'],
      'y' => $pos['y'] + MarrakechAssam::$deltas[$dir]['y'],
    ];
  }


  /*
   * isPositionValid : to place a carpet, the position must be inside the board, and not on Assam
   */
  public static function isPositionValid($pos, $assam){
    return $pos['x'] > 0 && $pos['x'] < 8
        && $pos['y'] > 0 && $pos['y'] < 8
        && !($pos['x'] == $assam['x'] && $pos['y'] == $assam['y']);
  }


  /*
   * getPossiblePlaces: return list of possible locations for placing a carpet
   */
  function getPossiblePlaces(){
    $assam = MarrakechAssam::get();
    $places = [];
    for($i = 0; $i < 4; $i++){
      $pos1 = self::moveInDir($assam, $i);
      if(!self::isPositionValid($pos1, $assam)) continue;

      for($j = 0; $j < 4; $j++){
        $pos2 = self::moveInDir($pos1, $j);
        if(!self::isPositionValid($pos2, $assam)) continue;

        array_push($places, [
          'x1' => $pos1['x'], 'y1' => $pos1['y'],
          'x2' => $pos2['x'], 'y2' => $pos2['y']
        ]);
      }
    }

    // Keep only valid carpet places : cannot entirely cover opponent in one go
		$board = self::getBoard();
    $pId = Marrakech::$instance->getActivePlayerId();
    Utils::filter($places, function($s) use ($board, $pId){
      return is_null($board[$s['x1']][$s['y1']]) || is_null($board[$s['x2']][$s['y2']]) // Either one of the two spot is empty
        || $board[$s['x1']][$s['y1']]['id'] != $board[$s['x2']][$s['y2']]['id'] // Either the rug are not the same on the two spot
        || $board[$s['x1']][$s['y1']]['pId'] == $pId; // Either the rub belongs to me (eventhough not very useful move...)
    });

    return $places;
  }



  public static function payTaxes(){
    $pId = Marrakech::$instance->getActivePlayerId();
    $board = self::getBoard();
    $assam = MarrakechAssam::get();
    $cell = $board[$assam['x']][$assam['y']];
    $player = PlayerManager::getById($pId);

    // Check if carpet is owned by eliminated player
    if(is_null($cell) || PlayerManager::isEliminated($cell['pId']) || $cell['pId'] == $pId){
      NotificationManager::noTaxes($player);
      return;
    }

    // Compute the taxe cost
    $taxerId = $cell['pId'];
    $taxer = PlayerManager::getById($taxerId);
    $type = $cell['type'];
    $zone = self::getTaxesZone($taxerId, $type, ['x' => $assam['x'], 'y' => $assam['y']]);
    $cost = count($zone);

    // Get remeaning money of player
    $eliminated = false;
    if ($player['money'] < $cost) {
      // Player is eliminated !!!
      $eliminated = true;
      $cost = $player['money'];
    }

    // Update stat
    StatManager::payTaxes($player, $taxer, $cost);

    // Update moneys
    PlayerManager::updateMoney($taxerId, $cost);
    PlayerManager::updateMoney($pId, -$cost);

    // Notify players
    NotificationManager::payTaxes($player, $taxer, count($zone), $cost, $zone, $eliminated);

    // Update score and UI, and proceed to next state/
    // Warning : cannot eliminate an active player so we must go to "eliminate" gamestate
    PlayerManager::updateScores();
    PlayerManager::updateUi();
    return $eliminated;
  }
}
