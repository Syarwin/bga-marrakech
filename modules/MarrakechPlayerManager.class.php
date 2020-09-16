<?php

/*
 * MarrakechPlayerManager: all utility functions concerning players
 */

//require_once('MarrakechPlayer.class.php');

class MarrakechPlayerManager extends APP_GameClass
{
	public static function setupNewGame($players)	{
		self::DbQuery('DELETE FROM player');
		$gameInfos = Marrakech::$instance->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_score, player_score_aux, player_money) VALUES ';

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
    Marrakech::$instance->reattributeColorsBasedOnPreferences($players,	$gameInfos['player_colors']);
		Marrakech::$instance->reloadPlayersBasicInfos();

    self::distributeCarpets($players);
	}




  public static function distributeCarpets($players){
      $sql = "INSERT INTO player_carpets (player_id,carpet_type,carpet_1,carpet_2,carpet_3,carpet_4,next_carpet) VALUES ";
      $values = [];
      switch (count($players)) {
        case 4:
          // 12 carpets each
          foreach (array_keys($players) as $index => $player_id) {
            $values[] =
              "(" .
              $player_id .
              "," .
              ($index + 1) .
              "," .
              ($index == 0 ? '12' : '0') .
              "," .
              ($index == 1 ? '12' : '0') .
              "," .
              ($index == 2 ? '12' : '0') .
              "," .
              ($index == 3 ? '12' : '0') .
              "," .
              "0)";
          }
          break;
        case 3:
          // 15 carpets each
          foreach (array_keys($players) as $index => $player_id) {
            $values[] =
              "(" .
              $player_id .
              "," .
              ($index + 1) .
              "," .
              ($index == 0 ? '15' : '0') .
              "," .
              ($index == 1 ? '15' : '0') .
              "," .
              ($index == 2 ? '15' : '0') .
              "," .
              "0,0)";
          }
          break;
        case 2:
          // 24 carpets each (12 by color)
          foreach (array_keys($players) as $index => $player_id) {
            $values[] =
              "(" .
              $player_id .
              "," .
              ($index * 2 + 1) .
              "," .
              ($index == 0 ? '12' : '0') .
              "," .
              ($index == 0 ? '12' : '0') .
              "," .
              ($index == 1 ? '12' : '0') .
              "," .
              ($index == 1 ? '12' : '0') .
              "," .
              ($index == 0 ? random_int(1, 2) : random_int(3, 4)) .
              ")";
          }
          break;
      }
      $sql .= implode($values, ",");
      self::DbQuery($sql);
    }



  public static function getUiData(){
    // Get information about players
    // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
    $sql =
      "SELECT " .
      "player.player_id id, " .
      "player.player_eliminated player_eliminated, " .
      "player.player_score score, " .
      "player.player_money money, " .
      "player_carpets.carpet_type carpet_type, " .
      "player_carpets.carpet_1 carpet_1, " .
      "player_carpets.carpet_2 carpet_2, " .
      "player_carpets.carpet_3 carpet_3, " .
      "player_carpets.carpet_4 carpet_4, " .
      "player_carpets.next_carpet next_carpet " .
      "FROM player " .
      "INNER JOIN player_carpets ON player_carpets.player_id = player.player_id ";
    return self::getObjectListFromDb($sql);
  }
}
