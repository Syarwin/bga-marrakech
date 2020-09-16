{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    marrakech_marrakech.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div class="container">
  
  <div id="board">
    <!-- BEGIN square -->
      <div id="square_{X}_{Y}" class="square" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->
    
    <!-- BEGIN square_action -->
      <div id="square_action_{X}_{Y}" class="square_action" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square_action -->
    
    <div class="next_direction" id="next_direction_S"></div>
    <div class="next_direction" id="next_direction_E"></div>
    <div class="next_direction" id="next_direction_N"></div>
    <div class="next_direction" id="next_direction_W"></div>
  </div>
  
  <div class="whiteblock" id="dice_display">
    <div id="dice">
    </div>
  </div>
  
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_assam = '<div id="assam" class="direction_${direction}"></div>';
var jstpl_carpet = '<div id="carpet_${carpet_id}" class="carpet carpet_${carpet_orientation} carpet_${carpet_type}_${carpet_orientation}"></div>';

var jstpl_next_carpet = '<div id="next_carpet_${id}" class="next_carpet"></div>';
var jstpl_carpet_info = '<div class="player_info" id="carpet_info_player_${id}"><div class="carpet_info carpet_info_${carpet_type}" style="display:inline-block;"></div>&nbsp;<span id="carpet_${carpet_type}_count">${carpet_count}</span></div>';
var jstpl_money_info = '<div class="player_info" id="money_player_${id}"><div class="money" id="money_${id}"></div>&nbsp;<span id="money_${id}_count">${money_count}</span></div>';

var jstpl_tax_inc = '<div class="tax_inc">+${amount}</div>';
var jstpl_tax_dec = '<div class="tax_dec">-${amount}</div>';
</script>  

{OVERALL_GAME_FOOTER}
