/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Marrakech implementation : © Tanguy Dechiron <tanguy.dechiron@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * marrakech.js
 *
 * Marrakech user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

//# sourceURL=marrakech.js
//@ sourceURL=marrakech.js
var isDebug = true;
var debug = isDebug ? console.info.bind(window.console) : function () { };
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter"], function (dojo, declare) {
	return declare("bgagame.marrakech", ebg.core.gamegui, {

/*
 * Constructor
 */
constructor: function () {
  this._assam = {
    x: null,
    y: null,
    dir: null
  };
  this.nextDirectionConnections = [];
},


////////////////////////////
////////////////////////////
///////		Framework 	//////
////////////////////////////
////////////////////////////

/*
 * onEnteringState:
 * 	this method is called each time we are entering into a new game state.
 * params:
 *	- str stateName : name of the state we are entering
 *	- mixed args : additional information
 */
onEnteringState: function (stateName, args) {
	debug('Entering state: ' + stateName, args);

	// Stop here if it's not the current player's turn for some states
	if (["rotateAssam","placeCarpet","moveAssam"].includes(stateName) && !this.isCurrentPlayerActive()) return;

	// Call appropriate method
	var methodName = "onEnteringState" + stateName.charAt(0).toUpperCase() + stateName.slice(1);
	if (this[methodName] !== undefined)
		this[methodName](args.args);
},

/*
 * onLeavingState:
 * 	this method is called each time we are leaving a game state.
 *
 * params:
 *	- str stateName : name of the state we are leaving
 */
onLeavingState: function (stateName) {
	debug('Leaving state: ' + stateName);
	this.clearPossible();
},


/*
 * onUpdateActionButtons:
 * 	called by BGA framework before onEnteringState
 *	in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function (stateName, args) {
	debug('Update action buttons: ' + stateName, args);
},



////////////////////////
////////////////////////
///////		SETUP 	//////
////////////////////////
////////////////////////
/*
 * Setup:
 *	This method set up the game user interface according to current game situation specified in parameters
 *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
 *
 * Params :
 *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
 */
setup: function (gamedatas) {
	debug('SETUP', gamedatas);

  // Setting up player boards
  gamedatas.bplayers.forEach(player => this.addPlayerInfos(player));

  // Setting up Assam
  this.setAssam(gamedatas.assam);

  // Setting up dice
  var dice = dojo.place(this.format_block( 'jstpl_dice', { face: gamedatas.dice}), 'dice-container');
  dojo.connect($("dice"), 'click', () => this.rollDice());

  // Click listener on square_action
  dojo.query(".square").connect("click", this, this.onClickCell);

  // Add carpets
  gamedatas.carpets.forEach(carpet => this.addCarpetOnBoard(carpet, false));

  // Setup game notifications
  this.setupNotifications();
},


/*
 * addCarpetInfo : add a carpet info on a player's board
 */
addCarpetInfo: function(pId, carpet_type, carpet_count){
  var div = dojo.place(this.format_block( 'jstpl_carpet_info', {
    pId: pId,
    type: carpet_type,
    count: carpet_count,
  }) , 'player_board_' + pId);

  this.addTooltip(div.id, _('Number of rugs left'), '' );
},

/*
 * addNextCarpetInfo
 */
addNextCarpetInfo: function(pId, next_carpet){
  dojo.destroy('next_carpet_' + pId);
  if(next_carpet != 0){
    dojo.place(this.format_block('jstpl_next_carpet', { pId: pId }) , "carpet_info_" + next_carpet);
    this.addTooltip('next_carpet_' + pId, _('Next carpet to place'), '');
  }
},

/*
 * addPlayerInfos : add all infos about player in the player's board
 */
addPlayerInfos: function(player){
  var pId = player.id;

  // Add carpet count for the player
  this.addCarpetInfo(pId, player.carpet_type, player['carpet_' + player.carpet_type]);

  if(this.gamedatas.bplayers.length == 2){ // If only two players => two carpet counts
    var carpet_type_bis = parseInt(player.carpet_type) + 1;
    this.addCarpetInfo(pId, carpet_type_bis, player['carpet_' + carpet_type_bis]);
    this.addNextCarpetInfo(pId, player.next_carpet);
  }

  // Add money count for the player
  dojo.place( this.format_block( 'jstpl_money_info', {
    id: pId,
    money: player.money
  }), 'player_board_' + pId);
  this.addTooltip('money_player_' + pId, _('Number of dirhams'), '' );

  // Disable player panel if eliminated
  if(player.player_eliminated == 1)
    this.disablePlayerPanel(pId);
},


/*
 * setAssam at a particular spot (and create it first if it does not exists)
 */
setAssam: function(assam){
  if(!$('assam')){
    dojo.place( this.format_block( 'jstpl_assam', assam) , 'board');
    this.addTooltip( 'assam', _('Assam'), '' );
  }

  this._assam = assam;
  this.placeOnObject( 'assam', 'square_' + assam.x + '_' + assam.y);
},



addCarpetOnBoard: function(carpet, animation){
  dojo.place( this.format_block( 'jstpl_carpet', carpet) , 'board');
  dojo.style('carpet_' + carpet.id, {
    zIndex: 10 + parseInt(carpet.id)
  });

  let carpetId = 'carpet_' + carpet.id;
  let squareId = 'square_' + carpet.x + '_' + carpet.y;
  let horiz = carpet.orientation == 'h';

  if(animation){
    this.placeOnObject(carpetId, 'overall_player_board_'+ carpet.pId );
    this.slideToObjectPos(carpetId, squareId, 0, 0, 1000).play();
  } else {
    this.placeOnObjectPos(carpetId, squareId, horiz? 40 : 0, horiz? 0 : 40);
  }
},



////////////////////////
////////////////////////
///////		Assam		//////
////////////////////////
////////////////////////

/////////////
// Rotate  //
/////////////
onEnteringStateRotateAssam: function() {
  var directions = [
    { n: 'N', x:0,  'y':-1},
    { n: 'E', x:1,  'y':0},
    { n: 'S', x:0,  'y':1},
    { n: 'W', x:-1, 'y':0},
  ];

  for(var i = -1; i < 2; i++){
    let dir = directions[(this._assam.dir + i + 4) % 4];
    let elemId = 'next_direction_' + dir.n;
    let x = this._assam.x + dir.x;
    let y = this._assam.y + dir.y;
    let delta = i;

    dojo.style(elemId, 'display', 'block');
    dojo.place(elemId, 'square_' + x + '_' + y );
    this.nextDirectionConnections.push(
      dojo.connect($(elemId), 'click', (evt) => { evt.stopPropagation(); this.rotateAssam(delta); })
    );
  }
},


rotateAssam: function(delta){
  this.takeAction('rotateAssam', {delta : delta});
},



////////////
//  Move  //
////////////
onEnteringStateMoveAssam: function() {
  dojo.addClass("dice", 'clickable');
  this.addActionButton('btnRollDice', _('Roll dice'), 'rollDice');
},


rollDice: function(){
  if(!this.checkAction('rollDice'))
    return;

  this.takeAction('rollDice', {});
},


notif_rollDice: function(n){
  if(n.args.face == 0)
    dojo.attr('dice', 'data-show', '0');
  else {
    dojo.addClass("dice", "roll");
    setTimeout( () => {
      dojo.removeClass("dice", "roll");
      $('dice').offsetWidth;
      dojo.attr('dice', 'data-show', n.args.face);
      dojo.removeClass("dice", "roll");
    }, 1500);
  }
},


//////////////
//  Notifs  //
//////////////
notif_rotateAssam: function(n){
  debug("Notif: rotate Assam", n);
  dojo.attr('assam', 'data-dir', n.args.assam.dir);
  this._assam = n.args.assam;
},

notif_moveAssam: function(n){
  debug("Notif: move Assam", n);
  this.slideToObject( 'assam', 'square_' + n.args.assam.x + '_' + n.args.assam.y, 500).play();
  this._assam = n.args.assam;
},


notif_payTaxes: function(n){
  debug("Notif: paying taxes", n);
  n.args.zone.forEach((place, i) => {
    if(i < n.args.payed)
      this.slideTemporaryObject('<div class="coin"></div>', "board", "square_" + place.x + "_" + place.y, 'money_count_' + n.args.taxerId, 1500, 0);
  });

  setTimeout(() => {
    var tax_dec = dojo.place( this.format_block( 'jstpl_tax_dec', { amount: n.args.payed} ) , 'money_' + n.args.pId );
    var tax_inc = dojo.place( this.format_block( 'jstpl_tax_inc', { amount: n.args.payed} ) , 'money_' + n.args.taxerId );
    this.fadeOutAndDestroy(tax_dec, 4000);
    this.fadeOutAndDestroy(tax_inc, 4000);
    this.incCounter('money_count_' + n.args.pId, -n.args.payed);
    this.incCounter('money_count_' + n.args.taxerId, n.args.payed);
  }, 1400);
},


////////////////////////
////////////////////////
///////		Carpet  //////
////////////////////////
////////////////////////
onEnteringStatePlaceCarpet: function(args){
  this._possiblePlaces = args.places;
  this._selectedFirstSquare = null;
  this._selectedSecondSquare = null;
  dojo.query(".square").removeClass('selectable selected');
  this.removeActionButtons();
  this.makeSquareSelectable();
},

makeSquareSelectable: function(){
  this._possiblePlaces.forEach(place => {
    if(this._selectedFirstSquare == null)
      dojo.addClass('square_' + place.x1 + '_' + place.y1, "selectable");

    else if(this._selectedFirstSquare.x == place.x1 && this._selectedFirstSquare.y == place.y1)
      dojo.addClass('square_' + place.x2 + '_' + place.y2, "selectable");
  });
},



onClickCell: function(evt) {
  if(!dojo.hasClass(evt.target, 'selectable')
  || dojo.hasClass(evt.target, 'selected')
  || !this.checkAction('placeCarpet'))
    return;

  this.removeActionButtons();
  this.addActionButton('btnCancelCarpet', _('Cancel'), () => this.onEnteringStatePlaceCarpet({ places : this._possiblePlaces }), null, false, 'gray');

  var x = dojo.attr(evt.target, 'data-x');
  var y = dojo.attr(evt.target, 'data-y');

  if(this._selectedFirstSquare == null){
    dojo.query(".square").removeClass('selectable');
    dojo.addClass(evt.target, 'selected');
    this._selectedFirstSquare = { x : x, y : y};
    this.makeSquareSelectable();
  } else {
    dojo.query(".square").removeClass('selectable');
    dojo.addClass(evt.target, 'selected');
    this._selectedSecondSquare = { x : x, y : y};
    this.addActionButton('btnValidateCarpet', _('Validate'), 'onValidateCarpet');
  }
},



onValidateCarpet: function() {
  if(!this.checkAction( 'placeCarpet')) return;

  this.takeAction('placeCarpet',{
    x1: this._selectedFirstSquare.x,
    y1: this._selectedFirstSquare.y,
    x2: this._selectedSecondSquare.x,
    y2: this._selectedSecondSquare.y,
  });
},



notif_placeCarpet: function(n){
  debug("Notif: place carpet", n);
  this.addCarpetOnBoard(n.args, true);
  this.incCounter('carpet_count_' + n.args.pId + '_' + n.args.type, -1);
},



////////////////////////////
////////////////////////////
///////		Utility 		//////
////////////////////////////
////////////////////////////

/*
 * takeAction: default AJAX call with locked interface
 */
takeAction: function (action, data, callback) {
	data = data || {};
	data.lock = true;
	callback = callback || function (res) { };
	this.ajaxcall("/marrakech/marrakech/" + action + ".html", data, this, callback);
},


clearPossible: function(){
  dojo.query(".next_direction").style('display', 'none');
  dojo.forEach( this.nextDirectionConnections, dojo.disconnect);
  dojo.query(".square").removeClass("selectable selected");
},

incCounter: function(id, value){
  $(id).innerHTML = parseInt($(id).innerHTML) + value;
},


///////////////////////////////////////////////////
//////	 Reaction to cometD notifications	 ///////
///////////////////////////////////////////////////

/*
 * setupNotifications:
 *	In this method, you associate each of your game notifications with your local method to handle it.
 *	Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" in the santorini.game.php file.
 */
setupNotifications: function () {
	var notifs = [
		['rotateAssam', 500],
    ['moveAssam', 500],
    ['rollDice', 2000],
    ['placeCarpet', 1000],
    ['updatePlayersInfo', 10],
    ['payTaxes', 1500],
	];

	notifs.forEach(notif => {
		dojo.subscribe(notif[0], this, "notif_" + notif[0]);
		this.notifqueue.setSynchronous(notif[0], notif[1]);
	});
},


notif_updatePlayersInfo: function(n){
  debug("Notif: update users", n);

  n.args.players.forEach(player => {
    // Update money and scores
    this.scoreCtrl[player.id].setValue(player.score);
    $('money_count_' + player.id).innerHTML = player.money;

    // Update carpets stocks
    for(var i = 1; i <= 4; i++){
      var carpetInfo = $('carpet_count_' + player.id + '_' + i);
      if(carpetInfo)
        carpetInfo.innerHTML = player.eliminated == 1? 0 : player['carpet_' + i];
    }

    // Update next carpet info
    this.addNextCarpetInfo(player.id, player.next_carpet);

    // Eliminate
    if(player.eliminated == 1){
      dojo.destroy('next_carpet_' + player.id);
      this.disablePlayerPanel(player.id);
    }
  });
},


   });
});
