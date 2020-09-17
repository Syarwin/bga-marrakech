<?php

class MarrakechBoard extends APP_GameClass
{
  public static function getUiData(){
    return [];
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

    $sql = "SELECT * FROM carpet ORDER BY id ASC";
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
		while(!empty($queue)){
      $cell = array_shift($queue);
      array_push($zone, $cell);

      for($i = 0; $i < 4; $i++){
        $npos = self::moveInDir($cell, $i);
        // Position must be inside the board and not be on Asssam
        if(!self::isPositionValidForCarpet($npos, $pos)) continue;
        // Should contain a carpet
        $carpet = $board[$npos['x']][$npos['y']];
        if(is_null($carpet)) continue;
        // Should not be already visited
        if(array_key_exists('visited', $carpet)) continue;
        $carpet['visited'] = true;
        // Should belong to same player and same type of carpet (in particular for 2 players game)
        if(($carpet['pId'] != $pId) || ($carpet['type'] != $type)) continue;

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
      if(!self::isPositionValidForCarpet($pos1, $assam)) continue;

      for($j = 0; $j < 4; $j++){
        $pos2 = self::moveInDir($pos1, $j);
        if(!self::isPositionValidForCarpet($pos2, $assam)) continue;

        array_push($places, [
          'x1' => $pos1['x'], 'y1' => $pos1['y'],
          'x2' => $pos2['x'], 'y2' => $pos2['y']
        ]);
      }
    }

    // Keep only valid carpet places : cannot entirely cover opponent in one go
		$board = $this->getBoard();
    $pId = Marrakech::$instance->getActivePlayerId();
    Utils::filter($places, function($s) use ($board, $pId){
      return is_null($board[$s['x1']][$s['y1']]) || is_null($board[$s['x2']][$s['y2']]) // Either one of the two spot is empty
        || $board[$s['x1']][$s['y1']]['id'] != $board[$s['x2']][$s['y2']]['id'] // Either the rug are not the same on the two spot
        || $board[$s['x1']][$s['y1']]['pId'] == $pId; // Either the rub belongs to me (eventhough not very useful move...)
    });

    return $places;
  }



/*

payTaxes(){
  // Check for taxes
  $visibleCarpetsOnBoard = $this->getVisibleCarpetsOnBoard();

  // Check if carpet is owned by eliminated player
  $currentCarpetPlayerId =
    $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['player_id'];
  $currentCarpetPlayerIsEliminated = self::getUniqueValueFromDB(
    "SELECT player_eliminated FROM player WHERE player_id='$currentCarpetPlayerId'"
  );
  $currentCarpetPlayerIsEliminated = intval($currentCarpetPlayerIsEliminated);

  if (
    $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']] != null &&
    $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['player_id'] !=
      $player_id &&
    $currentCarpetPlayerIsEliminated == 0
  ) {
    // Pay taxes
    $taxes_player_id =
      $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['player_id'];
    $carpet_type =
      $visibleCarpetsOnBoard[$last_path['x']][$last_path['y']]['carpet_type'];

    $taxes_zone = $this->getTaxesZone(
      $visibleCarpetsOnBoard,
      $taxes_player_id,
      $carpet_type,
      $last_path['x'],
      $last_path['y']
    );
    $taxes_cost = count($taxes_zone);

    $playerTaxes = self::getObjectFromDB(
      "SELECT player_id, player_name, player_money FROM player WHERE player_id='$taxes_player_id'"
    );

    $player_money = self::getUniqueValueFromDB(
      "SELECT player_money FROM player WHERE player_id='$player_id'"
    );
    if ($player_money < $taxes_cost) {
      // Player is eliminated !!!
      $player_eliminated = true;
      $taxes_cost = $player_money;
    }

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

    $sql_update1 = "UPDATE player SET player_money = (player_money - $taxes_cost) WHERE player_id='$player_id'";
    $sql_update2 = "UPDATE player SET player_money = (player_money + $taxes_cost) WHERE player_id='$taxes_player_id'";
    self::DbQuery($sql_update1);
    self::DbQuery($sql_update2);

    // Updated money of players
    $player_money -= $taxes_cost;
    $player_taxes_money = $playerTaxes['player_money'] + $taxes_cost;

    // Notify players
    self::notifyAllPlayers(
      "payTaxes",
      clienttranslate(
        '${player_name} pays ${taxesCost} to ${playerTaxesName}'
      ),
      [
        "playerId" => $player_id,
        "player_name" => $player_name,
        "playerMoney" => $player_money,
        "playerTaxesId" => $playerTaxes['player_id'],
        "playerTaxesName" => $playerTaxes['player_name'],
        "playerTaxesMoney" => $player_taxes_money,
        "taxesZone" => $taxes_zone,
        "taxesCost" => $taxes_cost,
      ]
    );
  }

  if ($player_eliminated) {
    // Remove all carpets from eliminated player, score will be set to 0
    self::DbQuery(
      "UPDATE player_carpets SET carpet_1=0, carpet_2=0, carpet_3=0, carpet_4=0, next_carpet=0 WHERE player_id='$player_id'"
    );
  }

  // Update scores and notify all players
  $scores = $this->updateScores();
  self::notifyAllPlayers("updateScores", '', [
    'scores' => $scores,
  ]);

  if ($player_eliminated) {
    // Player has been eliminated, notify and go to nextPlayer
    self::eliminatePlayer($player_id);
    $sql = "SELECT carpet_type FROM player_carpets WHERE player_id='$player_id'";
    $carpet_type = self::getUniqueValueFromDB($sql);

    self::notifyAllPlayers("playerEliminatedInfos", '', [
      'playerId' => $player_id,
      'carpetType' => $carpet_type,
    ]);

    $this->gamestate->nextState("nextPlayer");
  } else {
    // Go to next state (placeCarpet)
    $this->gamestate->nextState("placeCarpet");
  }
}
*/
}
