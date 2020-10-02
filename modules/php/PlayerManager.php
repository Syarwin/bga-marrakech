<?php
namespace MKH;
use Marrakech;

/*
 * PlayerManager: all utility functions concerning players
 */


class PlayerManager extends  \APP_DbObject
{
	public static function setupNewGame($players)	{
		self::DbQuery('DELETE FROM player');
		$gameInfos = Marrakech::get()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_score, player_score_aux, money) VALUES ';

		$values = [];
		foreach ($players as $pId => $player) {
			$color = array_shift($colors);
			$canal = $player['player_canal'];
      $name = addslashes($player['player_name']);
			$avatar = addslashes($player['player_avatar']);
      $money = 30;
			$values[] = "($pId, '$color','$canal','$name','$avatar', $money, $money, $money)";
		}

		self::DbQuery($sql . implode($values, ','));
    Marrakech::get()->reattributeColorsBasedOnPreferences($players,	$gameInfos['player_colors']);
		Marrakech::get()->reloadPlayersBasicInfos();

    self::distributeCarpets($players);
	}

  public static function distributeCarpets($players){
    foreach(array_keys($players) as $i => $pId) {
      $carpets = [0,0,0,0];
      $next = 0;
      $type = $i + 1;
      if(count($players) != 2){
        $carpets[$i] = count($players) == 4? 12 : 15;
      } else {
        $type = 2*$i + 1;
        $carpets[2*$i]     = 12;
        $carpets[2*$i + 1] = 12;
        $next = $type + random_int(0, 1); // Carpet indexing start at 1
      }

      self::DbQuery("UPDATE player SET carpet_type = $type, next_carpet = $next,
        carpet_1 = {$carpets[0]}, carpet_2 = {$carpets[1]}, carpet_3 = {$carpets[2]}, carpet_4 = {$carpets[3]} WHERE player_id = $pId");
    }
  }


  public static function getUiData(){
    return self::getObjectListFromDb("SELECT player_id id, player_eliminated eliminated, player_score score,
      money, carpet_type, carpet_1, carpet_2, carpet_3, carpet_4, next_carpet FROM player");
  }

	public static function updateUi(){
		NotificationManager::updatePlayersInfos(self::getUiData());
	}


	public static function getById($pId){
		return self::getObjectFromDB("SELECT player_id id, player_name name, player_eliminated eliminated, player_score score,
      money, carpet_type, carpet_1, carpet_2, carpet_3, carpet_4, next_carpet FROM player WHERE player_id = $pId");
	}


	public static function getPlayersLeft(){
		return self::getUniqueValueFromDB("SELECT count(player_id) FROM player WHERE player_eliminated = 0");
	}

	public static function getCarpetsLeft(){
		return self::getUniqueValueFromDB("SELECT SUM(carpet_1) + SUM(carpet_2) + SUM(carpet_3) + SUM(carpet_4) as TOTAL FROM player");
	}

	public static function getActivePlayerId(){
		return Marrakech::get()->getActivePlayerId();
	}


  public static function isEliminated($pId){
    $v = self::getUniqueValueFromDB("SELECT player_eliminated FROM player WHERE player_id = $pId");
    return $v == 1;
  }

  public function placeCarpet($pId, $x, $y, $orientation){
    $nPlayers = count(self::getUiData());

    $carpets = self::getObjectFromDB("SELECT * FROM player WHERE player_id = $pId");
    $type = $nPlayers == 2? $carpets['next_carpet'] : $carpets['carpet_type'];

    // Add carpet to board
    self::DbQuery("INSERT INTO carpets (x, y, type, orientation, player_id) VALUES ($x, $y, $type,'$orientation',$pId)");
    $cId = self::getUniqueValueFromDB("SELECT max(id) last_id FROM carpets");

    // Remove carpet from player stock
    self::DbQuery("UPDATE player SET carpet_$type = carpet_$type - 1 WHERE player_id = $pId");

    // Notify players
    NotificationManager::placeCarpet($cId, $x, $y, $orientation, $type);

		// Update stats
		StatManager::placeCarpet($pId, $type, ['x' => $x, 'y' => $y]);


    // Update next_carpet in db for next move for 2 players
    if($nPlayers == 2){
      $carpets = self::getObjectFromDB("SELECT * FROM player WHERE player_id = $pId");
      $playerType = (int) $carpets['carpet_type'];
      $firstType  = (int) $carpets['carpet_' . $playerType];
      $secondType = (int) $carpets['carpet_' . ($playerType + 1)];

      $nextCarpet = 0;
      if($firstType != 0 && $secondType != 0){
        $nextCarpet = bga_rand($playerType, $playerType + 1);
      } else {
        $nextCarpet = $playerType + ($firstType == 0? 1 : 0);
      }

      // Update next_carpet in db
      self::DbQuery( "UPDATE player SET next_carpet = $nextCarpet WHERE player_id = $pId");
    }
  }


	function updateMoney($pId, $delta){
    self::DbQuery( "UPDATE player SET money = (money + $delta) WHERE player_id = $pId");
  }



  function updateScores(){
    // Set score for all players
    $players = self::getCollectionFromDB("SELECT player_id id, money FROM player");
    foreach ($players as &$player)
      $player['carpet_score'] = 0;

    $board = Board::getBoard();
    for($x = 1; $x <= 7; $x++) {
      for($y = 1; $y <= 7; $y++) {
        $carpet = $board[$x][$y];
        if(!is_null($carpet))
          $players[$carpet['pId']]['carpet_score']++;
      }
    }

    foreach ($players as $pId => &$player) {
      $score_aux = (int) $player['money'];
      $score = $score_aux + $player['carpet_score'];

      // Update scores for current player
      self::DbQuery("UPDATE player SET player_score = $score, player_score_aux = $score_aux WHERE player_id = $pId");
    }
  }



	public static function eliminate($pId){
		self::DbQuery("UPDATE player SET money = 0, player_score = 0, player_score_aux = 0, carpet_1 = 0, carpet_2 = 0, carpet_3 = 0, carpet_4 = 0, next_carpet = 0 WHERE player_id = $pId");
		Marrakech::get()->eliminatePlayer($pId);
		self::updateUi();
	}
}
