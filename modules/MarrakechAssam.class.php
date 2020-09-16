<?php

class MarrakechAssam extends APP_GameClass
{
  public static function init(){
    Marrakech::$instance->setGameStateInitialValue('assamX',   4);
    Marrakech::$instance->setGameStateInitialValue('assamY',   4);
    Marrakech::$instance->setGameStateInitialValue('assamDir', SOUTH);
  }

  /*
   * Return Assam state as associative array : x,y and dir
   */
  public static function get() {
    return [
      'x'   => (int) Marrakech::$instance->getGameStateValue('assamX'),
      'y'   => (int) Marrakech::$instance->getGameStateValue('assamX'),
      'dir' => (int) Marrakech::$instance->getGameStateValue('assamDir'),
    ];
  }


  /*
   * Set the new position/direction of Assam and notify frontend
   */
  public static function set($state, $silent = false){
    Marrakech::$instance->setGameStateValue('assamX',   $state['x']);
    Marrakech::$instance->setGameStateValue('assamY',   $state['y']);
    Marrakech::$instance->setGameStateValue('assamDir', $state['dir']);

    if(!$silent){
      NotificationManager::moveAssam($state);
    }
  }


  /*
   * Possible direction for Assam and corresponding deltas
   */
  public static $deltas = [
    NORTH => ['x' =>  0, 'y' => -1],
    EAST  => ['x' =>  1, 'y' =>  0],
    SOUTH => ['x' =>  0, 'y' =>  1],
    WEST  => ['x' => -1, 'y' =>  0],
  ];


  // Representation of the board edges (R : turn right, L : turn left, C : corner (keep moving))
  public static $board = [
    'CRLRLRLRC',
    'L_______L',
    'R_______R',
    'L_______L',
    'R_______R',
    'L_______L',
    'R_______R',
    'L_______L',
    'CRLRLRLRC',
  ];
  public static function getCaseRotation(){
    $assam = self::get();
    return self::$board[$assam['y']][8 - $assam['x']];
  }

  /*
   *
   */
  public static function rotate($d, $silent = false){
    $assam = self::get();
    self::set([
      'x' => $assam['x'],
      'y' => $assam['y'],
      'dir' => ($assam['dir'] + $d + 4) % 4,
    ], $silent);
  }


  /*
   * moveForward : move Assam one step forward
   */
  public static function moveForward(){
    $assam = self::get();
    self::set([
      'x' => $assam['x'] + self::$deltas[$assam['dir']]['x'],
      'y' => $assam['y'] + self::$deltas[$assam['dir']]['y'],
      'dir' => $assam['dir'],
    ]);
  }

  /*
   * moveOneSetp : first make a move forward, then handle the turnarounds at the edges of the board
   */
  public static function moveOneStep(){
    // (Rotate) and move one step forward, until Assam is back *inside* the board
    $rotation = null;
    do {
      if(!is_null($rotation))
        self::rotate($rotation, true);
      self::moveForward();

      $caseRotation = self::getCaseRotation();
      if(is_null($rotation))
        $rotation = $caseRotation;
    }
    while($caseRotation != '_');
  }


  public static function move($roll){
    for ($i = 0; $i < $roll; $i++)
      self::moveOneStep();
  }
}





/*
TODO
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
}
*/
