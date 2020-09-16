<?php

$gameinfos = [
  'game_name' => "Marrakech",
  'designer' => 'Dominique Ehrhard',
  'artist' => 'Victor Boden, Marie Cardouat, Dominique Ehrhard',
  'year' => 2007,
  'publisher' => 'Gigamic',
  'publisher_website' => 'https://www.gigamic.com/',
  'publisher_bgg_id' => 155,
  'bgg_id' => 29223,

  'players' => [ 2,3,4 ],
  'suggest_player_number' => null,
  'not_recommend_player_number' => null,

  'estimated_duration' => 30,
  'fast_additional_time' => 30,
  'medium_additional_time' => 40,
  'slow_additional_time' => 50,

  'tie_breaker_description' => totranslate("Number of dirhams"),
  'losers_not_ranked' => false,

  'is_beta' => 1,
  'is_coop' => 0,

  'complexity' => 1,
  'luck' => 4,
  'strategy' => 2,
  'diplomacy' => 1,

  'player_colors' => ["ff0000", "008000", "0000ff", "ffa500", "773300"],
  'favorite_colors_support' => true,

  'disable_player_order_swap_on_rematch' => false,

  'game_interface_width' => [
    'min' => 720,
    'max' => null
  ],

  'presentation' => [
    totranslate("In Marrakech each player takes the role of a rug salesperson who tries to outwit the competition. Each player starts with 10 coins and an equal number of carpets."),
    totranslate("On your turn, you may rotate Assam 90 degrees. Then roll the die and move him forward as many spaces as showing (d6: 1, 2, 2, 3, 3, 4). If Assam reaches the edge of the board, follow the curve and continue moving in the next row. If Assam lands on another player's carpet, you must pay that player 1 coin per square showing that is contiguous with the landed-on square. Then, you place one of your carpets orthogonally adjacent to Assam (but may not directly overlay another carpet)."),
    totranslate("The game ends when all players have played all carpets. Each gets 1 coin per visible square. The player with most coins wins!")
  ],

  'tags' => [ 2, 11, 206 ],


  //////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)
  'is_sandbox' => false,
  'turnControl' => 'simple'
  ////////
];
