{OVERALL_GAME_HEADER}
<div class="container">

  <div id="board">
    <!-- BEGIN square -->
      <div id="square_{X}_{Y}" class="square"></div>
    <!-- END square -->

    <!-- BEGIN square_action -->
      <div id="square_action_{X}_{Y}" class="square_action" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square_action -->

    <div class="next_direction" id="next_direction_S"></div>
    <div class="next_direction" id="next_direction_E"></div>
    <div class="next_direction" id="next_direction_N"></div>
    <div class="next_direction" id="next_direction_W"></div>
  </div>

  <div class="whiteblock" id="dice-container">
    <div id="dice" data-show="0">
      <div class="face1"></div>
      <div class="face2"></div>
      <div class="face3"></div>
      <div class="face4"></div>
      <div class="face5"></div>
      <div class="face6"></div>
    </div>
  </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_assam = '<div id="assam" data-dir="${dir}"></div>';
var jstpl_carpet = '<div id="carpet_${carpet_id}" class="carpet carpet_${carpet_orientation} carpet_${carpet_type}_${carpet_orientation}"></div>';

var jstpl_carpet_info = `
<div class="player_info" id="carpet_player_info_\${carpet_type}">
  <div class="carpet_info" id="carpet_info_\${carpet_type}"></div>
  &nbsp;
  <span id="carpet_count_\${carpet_type}">\${carpet_count}</span>
</div>`;


var jstpl_money_info = `
<div class="player_info" id="money_player_\${id}">
  <div class="money" id="money_\${id}"></div>
  &nbsp;
  <span id="money_count_\${id}">\${money}</span>
</div>`;

var jstpl_tax_inc = '<div class="tax_inc">+${amount}</div>';
var jstpl_tax_dec = '<div class="tax_dec">-${amount}</div>';
</script>

{OVERALL_GAME_FOOTER}
