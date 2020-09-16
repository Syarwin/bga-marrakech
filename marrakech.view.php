<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * marrakech.view.php
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_marrakech_marrakech extends game_view
{
  function getGameName() {
    return "marrakech";
  }

  function build_page($viewArgs) {
    $this->page->begin_block( "marrakech_marrakech", "square" );
    for($y = 0; $y <= 8; $y++){
      for($x = 0; $x <= 8; $x++){
        $this->page->insert_block( "square", [
          'X' => $x,
          'Y' => $y,
        ]);
      }
    }
  }
}
