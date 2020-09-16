<?php

class MarrakechBoard extends APP_GameClass
{
  public static function getUiData()
  {
    return [];
  }

/*
$sql = "SELECT assam_x x, assam_y y, direction FROM assam LIMIT 1";
$result['assam'] = self::getObjectFromDB($sql);

$sql =
  "SELECT turn, board_x x, board_y y, carpet_type, carpet_orientation, player_id FROM board ORDER BY turn ASC";
$result['carpets_on_board'] = self::getCollectionFromDb($sql);




function getVisibleCarpetsOnBoard()
{
  $sql =
    "SELECT turn, board_x x, board_y y, carpet_type, carpet_orientation, player_id FROM board ORDER BY turn ASC";
  $carpets_on_board = self::getCollectionFromDb($sql);

  $visibleCarpetsOnBoard = [];
  for ($x = 0; $x <= 8; $x++) {
    $visibleCarpetsOnBoard[] = [];
    for ($y = 0; $y <= 8; $y++) {
      $visibleCarpetsOnBoard[$x][] = null;
    }
  }

  foreach ($carpets_on_board as $carpet) {
    $carpet_id = $carpet['turn'];
    $x = $carpet['x'];
    $y = $carpet['y'];
    $carpet_type = $carpet['carpet_type'];
    $carpet_orientation = $carpet['carpet_orientation'];
    $player_id = $carpet['player_id'];

    if ($carpet_orientation == 'h') {
      $x2 = $x + 1;
      $y2 = $y;
    } else {
      $x2 = $x;
      $y2 = $y + 1;
    }

    // Set first cell of carpet
    $visibleCarpetsOnBoard[$x][$y] = [
      'carpet_id' => $carpet_id,
      'carpet_type' => $carpet_type,
      'player_id' => $player_id,
    ];

    // Set second cell of carpet
    $visibleCarpetsOnBoard[$x2][$y2] = [
      'carpet_id' => $carpet_id,
      'carpet_type' => $carpet_type,
      'player_id' => $player_id,
    ];
  }

  return $visibleCarpetsOnBoard;
}




function generateAssamPath($assam, $roll)
{
  $path = [];

  $current_x = $assam['x'];
  $current_y = $assam['y'];
  $current_direction = $assam['direction'];

  for ($i = 0; $i < $roll; $i++) {
    switch ($current_direction) {
      case 'S':
        $current_y += 1;
        break;
      case 'E':
        $current_x += 1;
        break;
      case 'N':
        $current_y -= 1;
        break;
      case 'W':
        $current_x -= 1;
        break;
    }

    $path[] = [
      'x' => $current_x,
      'y' => $current_y,
      'direction' => $current_direction,
    ];

    // Check if on a border of the board
    if ($current_x == 0) {
      switch ($current_y) {
        case 1:
        case 3:
        case 5:
          $path[] = [
            'x' => $current_x,
            'y' => $current_y + 1,
            'direction' => 'S',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y + 1,
            'direction' => 'E',
          ];
          break;
        case 2:
        case 4:
        case 6:
          $path[] = [
            'x' => $current_x,
            'y' => $current_y - 1,
            'direction' => 'N',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y - 1,
            'direction' => 'E',
          ];
          break;
        case 7:
          $path[] = [
            'x' => $current_x,
            'y' => $current_y + 1,
            'direction' => 'S',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y + 1,
            'direction' => 'E',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y,
            'direction' => 'N',
          ];
          break;
      }
    }

    if ($current_x == 8) {
      switch ($current_y) {
        case 2:
        case 4:
        case 6:
          $path[] = [
            'x' => $current_x,
            'y' => $current_y + 1,
            'direction' => 'S',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y + 1,
            'direction' => 'W',
          ];
          break;
        case 3:
        case 5:
        case 7:
          $path[] = [
            'x' => $current_x,
            'y' => $current_y - 1,
            'direction' => 'N',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y - 1,
            'direction' => 'W',
          ];
          break;
        case 1:
          $path[] = [
            'x' => $current_x,
            'y' => $current_y - 1,
            'direction' => 'N',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y - 1,
            'direction' => 'W',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y,
            'direction' => 'S',
          ];
          break;
      }
    }

    if ($current_y == 0) {
      switch ($current_x) {
        case 1:
        case 3:
        case 5:
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y,
            'direction' => 'E',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y + 1,
            'direction' => 'S',
          ];
          break;
        case 2:
        case 4:
        case 6:
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y,
            'direction' => 'W',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y + 1,
            'direction' => 'S',
          ];
          break;
        case 7:
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y,
            'direction' => 'E',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y + 1,
            'direction' => 'S',
          ];
          $path[] = [
            'x' => $current_x,
            'y' => $current_y + 1,
            'direction' => 'W',
          ];
          break;
      }
    }

    if ($current_y == 8) {
      switch ($current_x) {
        case 2:
        case 4:
        case 6:
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y,
            'direction' => 'E',
          ];
          $path[] = [
            'x' => $current_x + 1,
            'y' => $current_y - 1,
            'direction' => 'N',
          ];
          break;
        case 3:
        case 5:
        case 7:
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y,
            'direction' => 'W',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y - 1,
            'direction' => 'N',
          ];
          break;
        case 1:
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y,
            'direction' => 'W',
          ];
          $path[] = [
            'x' => $current_x - 1,
            'y' => $current_y - 1,
            'direction' => 'N',
          ];
          $path[] = [
            'x' => $current_x,
            'y' => $current_y - 1,
            'direction' => 'E',
          ];
          break;
      }
    }

    // Update current variables
    $last_path = end($path);
    $current_x = $last_path['x'];
    $current_y = $last_path['y'];
    $current_direction = $last_path['direction'];
  }

  return $path;
}




function getPossiblePlaces()
{
  $sql = "SELECT assam_x x,assam_y y,direction FROM assam LIMIT 1";
  $assam = self::getObjectFromDB($sql);

  $possiblePlaces = [];

  if ($assam['x'] > 1) {
    if ($assam['x'] > 2) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'] - 1,
        'y1' => $assam['y'],
        'x2' => $assam['x'] - 2,
        'y2' => $assam['y'],
      ]);
    }
    if ($assam['y'] > 1) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'] - 1,
        'y1' => $assam['y'],
        'x2' => $assam['x'] - 1,
        'y2' => $assam['y'] - 1,
      ]);
    }
    if ($assam['y'] < 7) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'] - 1,
        'y1' => $assam['y'],
        'x2' => $assam['x'] - 1,
        'y2' => $assam['y'] + 1,
      ]);
    }
  }

  if ($assam['x'] < 7) {
    if ($assam['x'] < 6) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'] + 1,
        'y1' => $assam['y'],
        'x2' => $assam['x'] + 2,
        'y2' => $assam['y'],
      ]);
    }
    if ($assam['y'] > 1) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'] + 1,
        'y1' => $assam['y'],
        'x2' => $assam['x'] + 1,
        'y2' => $assam['y'] - 1,
      ]);
    }
    if ($assam['y'] < 7) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'] + 1,
        'y1' => $assam['y'],
        'x2' => $assam['x'] + 1,
        'y2' => $assam['y'] + 1,
      ]);
    }
  }

  if ($assam['y'] > 1) {
    if ($assam['y'] > 2) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'],
        'y1' => $assam['y'] - 1,
        'x2' => $assam['x'],
        'y2' => $assam['y'] - 2,
      ]);
    }
    if ($assam['x'] > 1) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'],
        'y1' => $assam['y'] - 1,
        'x2' => $assam['x'] - 1,
        'y2' => $assam['y'] - 1,
      ]);
    }
    if ($assam['x'] < 7) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'],
        'y1' => $assam['y'] - 1,
        'x2' => $assam['x'] + 1,
        'y2' => $assam['y'] - 1,
      ]);
    }
  }

  if ($assam['y'] < 7) {
    if ($assam['y'] < 6) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'],
        'y1' => $assam['y'] + 1,
        'x2' => $assam['x'],
        'y2' => $assam['y'] + 2,
      ]);
    }
    if ($assam['x'] > 1) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'],
        'y1' => $assam['y'] + 1,
        'x2' => $assam['x'] - 1,
        'y2' => $assam['y'] + 1,
      ]);
    }
    if ($assam['x'] < 7) {
      array_push($possiblePlaces, [
        'x1' => $assam['x'],
        'y1' => $assam['y'] + 1,
        'x2' => $assam['x'] + 1,
        'y2' => $assam['y'] + 1,
      ]);
    }
  }

  return $possiblePlaces;
}



rotateAssam($dir){
  // Rotate assam to right (clockwise)
  $sql = "
    UPDATE assam
    SET direction = CASE
      WHEN direction = 'S' THEN 'W'
      WHEN direction = 'E' THEN 'S'
      WHEN direction = 'N' THEN 'E'
      WHEN direction = 'W' THEN 'N'
    END
    ";
  self::DbQuery($sql);

  // Get the new direction of Assam
  $sql = "SELECT direction FROM assam LIMIT 1";
  $direction = self::getUniqueValueFromDB($sql);

  // Notify players
  self::notifyAllPlayers(
    "assamDirection",
    clienttranslate('${player_name} rotates Assam right'),
    [
      "player_name" => self::getActivePlayerName(),
      "newAssamDirection" => $direction,
    ]
  );

}


moveAssam($n){
  // Get current position and direction of Assam
  $sql = "SELECT assam_x x,assam_y y,direction FROM assam LIMIT 1";
  $assam = self::getObjectFromDB($sql);

  // Move Assam
  $path = $this->generateAssamPath($assam, $roll);

  // Update Assam position / direction
  $last_path = end($path);
  $sql =
    "UPDATE assam SET assam_x = " .
    $last_path['x'] .
    ",assam_y = " .
    $last_path['y'] .
    ",direction = '" .
    $last_path['direction'] .
    "'";
  self::DbQuery($sql);

  // Notify players
  self::notifyAllPlayers(
    "diceRoll",
    clienttranslate('${player_name} moves Assam ${roll} steps'),
    [
      "player_name" => $player_name,
      "roll" => $roll,
      "path" => $path,
      "assam" => $last_path,
    ]
  );
}


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
