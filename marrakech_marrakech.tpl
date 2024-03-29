{OVERALL_GAME_HEADER}
<div id="marrakech_container">

  <div id="board">
    <!-- BEGIN square -->
      <div id="square_{X}_{Y}" data-x="{X}" data-y="{Y}" class="square"></div>
    <!-- END square -->

    <!-- BEGIN square_action -->
      <div id="square_action_{X}_{Y}" class="square_action" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square_action -->

    <div class="next_direction" id="next_direction_S"></div>
    <div class="next_direction" id="next_direction_E"></div>
    <div class="next_direction" id="next_direction_N"></div>
    <div class="next_direction" id="next_direction_W"></div>
  </div>

  <div class="whiteblock" id="dice-container"></div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_assam = '<div id="assam" data-dir="${dir}"></div>';
var jstpl_carpet = '<div id="carpet_${id}" class="carpet carpet_${orientation} carpet_${type}_${orientation}"></div>';

var jstpl_next_carpet = '<div id="next_carpet_${pId}" class="next_carpet"></div>';
var jstpl_carpet_info = `
<div class="player_info" id="carpet_player_info_\${type}">
  <div class="carpet_info" id="carpet_info_\${type}"></div>
  &nbsp;
  <span id="carpet_count_\${pId}_\${type}">\${count}</span>
</div>`;


var jstpl_money_info = `
<div class="player_info" id="money_player_\${id}">
  <div class="money" id="money_\${id}"></div>
  &nbsp;
  <span id="money_count_\${id}">\${money}</span>
</div>`;

var jstpl_tax_inc = '<div class="tax_inc">+${amount}</div>';
var jstpl_tax_dec = '<div class="tax_dec">-${amount}</div>';

var jstpl_dice = `
<div id="dice" data-show="\${face}">
  <div class="face1"><div></div><div></div></div>
  <div class="face2"><div></div><div></div></div>
  <div class="face3"><div></div><div></div></div>
  <div class="face4"><div></div><div></div></div>
  <div class="face5"><div></div><div></div></div>
  <div class="face6"><div></div><div></div></div>
</div>
`;
</script>

{OVERALL_GAME_FOOTER}
