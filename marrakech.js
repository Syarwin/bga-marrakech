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
  this.playersNumber = null;

  this._assam = {
    x: null,
    y: null,
    dir: null
  };

  this.carpetPlaced = {
    x1: null,
    y1: null,
    x2: null,
    y2: null
  };

  this.nextDirectionConnections = [];
},


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

/*
  TODO
            var carpets_on_board = gamedatas.carpets_on_board;
            this.addCarpetsOnBoard(carpets_on_board);

            // Add events on square_action
            dojo.query(".square_action").connect("click", this, this.onClickCell);

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

        },
*/
},


/*
 * addCarpetInfo : add a carpet info on a player's board
 */
addCarpetInfo: function(pId, carpet_type, carpet_count){
  var div = dojo.place(this.format_block( 'jstpl_carpet_info', {
    carpet_type: carpet_type,
    carpet_count: carpet_count,
  }) , 'player_board_' + pId);

  this.addTooltip(div.id, _('Number of rugs left'), '' );
},

/*
 * addPlayerInfos : add all infos about player in the player's board
 */
addPlayerInfos: function(player){
  var pId = player.id;

  // Add carpet count for the player
  this.addCarpetInfo(pId, player.carpet_type, player['carpet_' + player.carpet_type]);

  if(this.playersNumber == 2){ // If only two players => two carpet counts
    var carpet_type_bis = parseInt(player.carpet_type) + 1;
    this.addCarpetInfo(pId, carpet_type_bis, player['carpet_' + carpet_type_bis]);

    if(player.next_carpet != 0){
      dojo.addClass('carpet_info_' + player.next_carpet, 'nextCarpet');
//      this.addTooltip( 'next_carpet_' + player_id, _('Next carpet to place'), '' );
    }
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
	if (["rotateAssam",].includes(stateName) && !this.isCurrentPlayerActive()) return;

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
//	this.clearPossible(); TODO
},






/*
 * takeAction: default AJAX call with locked interface
 */
takeAction: function (action, data, callback) {
	data = data || {};
	data.lock = true;
	callback = callback || function (res) { };
	this.ajaxcall("/marrakech/marrakech/" + action + ".html", data, this, callback);
},


////////////////////////////////
////////////////////////////////
///////		Rotate Assam		//////
////////////////////////////////
////////////////////////////////
onEnteringStateRotateAssam: function() {
  //  dojo.removeClass( "assam" ); ??

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
      dojo.connect($(elemId), 'click', () => this.rotateAssam(delta) )
    );
  }
},


rotateAssam: function(delta){
  this.takeAction('rotateAssam', {delta : delta});
},


/*
        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );

            switch( stateName )
            {
              case 'assamDirection':
                if( this.isCurrentPlayerActive() )
                {
                  this.setAssamDirections();
                }
                break;

              case 'placeCarpet':
                if( this.isCurrentPlayerActive() )
                {
                  this.setCarpetActions();
                }
                break;

        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );

            switch( stateName )
            {
              case 'assamMove':
                // Hide the die
                dojo.style( 'dice', 'display', 'none' );
                break;

            /* Example:

            case 'myGameState':

                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );

                break;
           *


            case 'dummmy':
                break;
            }
        },
*/
        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );

            if( this.isCurrentPlayerActive() )
            {
                switch( stateName )
                {
                  /*case 'assamDirection':
                    this.addActionButton( 'button_direction_right', _('Turn right'), 'onDirectionRight' );
                    this.addActionButton( 'button_direction_left', _('Turn left'), 'onDirectionLeft' );
                    this.addActionButton( 'button_skip_direction', _('Move forward'), 'onSkipDirection' );
                    break;*/
                  case 'placeCarpet':
                    this.addActionButton( 'button_validate', _('Validate'), 'onValidateCarpet' );
                    this.addActionButton( 'button_cancel', _('Cancel'), 'onCancelCarpet' );
                    break;
/*
                 Example:

                 case 'myGameState':

                    // Add 3 action buttons in the action status bar:

                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                    break;
*/
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods


        addCarpetsOnBoard: function( carpets_on_board )
        {
          for( var row in carpets_on_board )
          {
            var current_carpet = carpets_on_board[row];
            dojo.place( this.format_block( 'jstpl_carpet', {
                carpet_id: current_carpet.turn,
                carpet_type: current_carpet.carpet_type,
                carpet_orientation: current_carpet.carpet_orientation
            } ) , 'square_' + current_carpet.x + '_' + current_carpet.y);
            dojo.query('#carpet_' + current_carpet.turn).style({
              zIndex: 10 + parseInt(current_carpet.turn)
            });
          }
        },



        removeAssamDirections: function() {
          dojo.query(".next_direction").style({
            display: 'none'
          });
          dojo.forEach( this.next_direction_connections, dojo.disconnect);
        },

        setCarpetActions: function()
        {
          // Initialize carpet actions
          this.carpetPlaced = {
            x1: null,
            y1: null,
            x2: null,
            y2: null
          };

          dojo.query(".square_action").removeClass('selected');
          dojo.query(".square_action").style({
            display: 'none',
            backgroundColor: 'transparent'
          });

          if ( this.assamPosition.x > 1 )
          {
            dojo.query('#square_action_' + (parseInt(this.assamPosition.x)-1) + '_' + parseInt(this.assamPosition.y) ).style({
              display: 'block',
              backgroundColor: 'rgba(100,100,255,0.3)'
            });
          }
          if ( this.assamPosition.x < 7 )
          {
            dojo.query('#square_action_' + (parseInt(this.assamPosition.x)+1) + '_' + parseInt(this.assamPosition.y) ).style({
              display: 'block',
              backgroundColor: 'rgba(100,100,255,0.3)'
            });
          }
          if ( this.assamPosition.y > 1 )
          {
            dojo.query('#square_action_' + parseInt(this.assamPosition.x) + '_' + (parseInt(this.assamPosition.y)-1) ).style({
              display: 'block',
              backgroundColor: 'rgba(100,100,255,0.3)'
            });
          }
          if ( this.assamPosition.y < 7 )
          {
            dojo.query('#square_action_' + parseInt(this.assamPosition.x) + '_' + (parseInt(this.assamPosition.y)+1) ).style({
              display: 'block',
              backgroundColor: 'rgba(100,100,255,0.3)'
            });
          }
        },


        ///////////////////////////////////////////////////
        //// Player's action

        onValidateCarpet: function ( evt )
        {
          dojo.stopEvent( evt );

          if( ! this.checkAction( 'placeCarpet' ) )
          { return; }

          if ( this.carpetPlaced.x1 == null
            || this.carpetPlaced.y1 == null
            || this.carpetPlaced.x2 == null
            || this.carpetPlaced.y2 == null)
          {
            this.showMessage( _('Please select 2 cells to place your carpet'), 'error' );
            return;
          }

          var x1 = this.carpetPlaced.x1;
          var y1 = this.carpetPlaced.y1;
          var x2 = this.carpetPlaced.x2;
          var y2 = this.carpetPlaced.y2;

          this.ajaxcall( "/marrakech/marrakech/placeCarpet.html",
            {
              x1: x1,
              y1: y1,
              x2: x2,
              y2: y2,
              lock: true
            },
            this,
            function( result ) {
              // Remove selected cells
              dojo.query(".square_action").removeClass('selected');
              dojo.query(".square_action").style({
                display: 'none',
                backgroundColor: 'transparent'
              });
            },
            function( is_error ) {
              if (is_error) {
                this.setCarpetActions();
              }
            }
          );
        },

        onCancelCarpet: function ( evt )
        {
          dojo.stopEvent( evt );

          this.setCarpetActions();
        },

        onClickCell: function( evt )
        {
          if( ! this.checkAction( 'placeCarpet' ) )
          { return; }

          // Action id is on the form 'square_action_x_y'
          var action_id = evt.target.id;
          var action_infos = action_id.split('_');
          var x = action_infos[2];
          var y = action_infos[3];

          if (dojo.hasClass(action_id, 'selected'))
          {
            return;
          }

          if (this.carpetPlaced.x1 == null && this.carpetPlaced.y1 == null)
          {
            this.carpetPlaced.x1 = x;
            this.carpetPlaced.y1 = y;

            dojo.query('#' + action_id).style({
              backgroundColor: 'rgba(0,255,0,0.4)'
            })
            dojo.addClass(action_id, 'selected');
            dojo.query(".square_action:not(.selected)").style({
              display: 'none',
              backgroundColor: 'transparent'
            });

            // Set square_action for 2d carpet cell
            if ( x > 1 && ( (parseInt(x)-1) != this.assamPosition.x || parseInt(y) != this.assamPosition.y ) )
            {
              dojo.query('#square_action_' + (parseInt(x)-1) + '_' + parseInt(y) ).style({
                display: 'block',
                backgroundColor: 'rgba(100,100,255,0.3)'
              });
            }
            if ( x < 7 && ( (parseInt(x)+1) != this.assamPosition.x || parseInt(y) != this.assamPosition.y ) )
            {
              dojo.query('#square_action_' + (parseInt(x)+1) + '_' + parseInt(y) ).style({
                display: 'block',
                backgroundColor: 'rgba(100,100,255,0.3)'
              });
            }
            if ( y > 1 && ( parseInt(x) != this.assamPosition.x || (parseInt(y)-1) != this.assamPosition.y ) )
            {
              dojo.query('#square_action_' + parseInt(x) + '_' + (parseInt(y)-1) ).style({
                display: 'block',
                backgroundColor: 'rgba(100,100,255,0.3)'
              });
            }
            if ( y < 7 && ( parseInt(x) != this.assamPosition.x || (parseInt(y)+1) != this.assamPosition.y ) )
            {
              dojo.query('#square_action_' + parseInt(x) + '_' + (parseInt(y)+1) ).style({
                display: 'block',
                backgroundColor: 'rgba(100,100,255,0.3)'
              });
            }
          }
          else if (this.carpetPlaced.x2 == null && this.carpetPlaced.y2 == null)
          {
            this.carpetPlaced.x2 = x;
            this.carpetPlaced.y2 = y;

            dojo.query('#' + action_id).style({
              backgroundColor: 'rgba(0,255,0,0.4)'
            })
            dojo.addClass(action_id, 'selected');
            dojo.query(".square_action:not(.selected)").style({
              display: 'none',
              backgroundColor: 'transparent'
            });
          }
        },

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */

        /* Example:

        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/marrakech/marrakech/myAction.html", {
                                                                    lock: true,
                                                                    myArgument1: arg1,
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 },
                         this, function( result ) {

                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );
        },

        */


        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:

            In this method, you associate each of your game notifications with your local method to handle it.

            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your marrakech.game.php file.

        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );

            dojo.subscribe( "assamDirection", this, "notif_assamDirection" );
            this.notifqueue.setSynchronous( "assamDirection", 1500 );

            dojo.subscribe( "diceRoll", this, "notif_diceRoll" );
            this.notifqueue.setSynchronous( "diceRoll", 1500 );

            dojo.subscribe( "payTaxes", this, "notif_payTaxes" );
            this.notifqueue.setSynchronous( "payTaxes", 1500 );

            dojo.subscribe( "carpetPlaced", this, "notif_carpetPlaced" );
            this.notifqueue.setSynchronous( "carpetPlaced", 1500 );

            dojo.subscribe( "updateScores", this, "notif_updateScores" );

            dojo.subscribe( "playerEliminatedInfos", this, "notif_playerEliminatedInfos" );


            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //
        },

        notif_assamDirection: function( notif )
        {
          if ( notif.args.newAssamDirection )
          {
            dojo.removeClass( "assam" );
            dojo.addClass( "assam", "direction_" + notif.args.newAssamDirection );
            this.assamPosition.direction = notif.args.newAssamDirection;
          }
        },

        notif_diceRoll: function( notif )
        {
          // Show the result of the die
          dojo.removeClass( 'dice' );
          dojo.addClass( 'dice', 'dice_' + notif.args.roll );
          dojo.style( 'dice', 'display', 'block' );

          if ( notif.args.path && notif.args.path.length > 0 )
          {
            var slides = [];
            dojo.removeClass( "assam" );
            for(var i=0; i<notif.args.path.length; i++)
            {
              point = notif.args.path[i];
              var anim = this.slideToObject( 'assam', 'square_' + point.x + '_' + point.y, 250 );
              slides.push(anim);
            }
          }
          var chain = dojo.fx.chain(slides);
          chain.onEnd = function() {
            dojo.addClass( "assam", "direction_" + notif.args.assam.direction );
          }
          chain.play();
          this.assamPosition = {
            x: notif.args.assam.x,
            y: notif.args.assam.y,
            direction: notif.args.assam.direction
          };
        },

        notif_payTaxes: function( notif )
        {
          var tax_dec = dojo.place( this.format_block( 'jstpl_tax_dec', {
              amount: notif.args.taxesCost
          } ) , 'money_' + notif.args.playerId );
          var tax_inc = dojo.place( this.format_block( 'jstpl_tax_inc', {
              amount: notif.args.taxesCost
          } ) , 'money_' + notif.args.playerTaxesId );
          this.fadeOutAndDestroy( tax_dec, 2000 );
          this.fadeOutAndDestroy( tax_inc, 2000 );
          dojo.byId('money_' + notif.args.playerId + '_count').innerHTML = notif.args.playerMoney;
          dojo.byId('money_' + notif.args.playerTaxesId + '_count').innerHTML = notif.args.playerTaxesMoney;
        },

        notif_carpetPlaced: function( notif )
        {
          if( this.playersNumber == 2 )
          {
            // Update next carpet
            if ( notif.args.nextCarpet == 0 )
            {
              // remove next carpet
              dojo.destroy('next_carpet_' + notif.args.playerId);
            }
            else
            {
              var current_next_carpet = dojo.query( '#player_board_' + notif.args.playerId + ' .carpet_info_' + notif.args.nextCarpet )[0];

              dojo.destroy('next_carpet_' + notif.args.playerId);
              dojo.place( this.format_block( 'jstpl_next_carpet', {
                id: notif.args.playerId
              } ) , current_next_carpet );
            }
          }

          dojo.place( this.format_block( 'jstpl_carpet', {
              carpet_id: notif.args.carpet_id,
              carpet_type: notif.args.carpet_type,
              carpet_orientation: notif.args.carpet_orientation
          } ) , 'board' );
          dojo.query('#carpet_' + notif.args.carpet_id).style({
            zIndex: 10 + parseInt(notif.args.carpet_id)
          });
          this.placeOnObject( 'carpet_' + notif.args.carpet_id, 'overall_player_board_'+ notif.args.playerId );
          this.slideToObjectPos( 'carpet_' + notif.args.carpet_id, 'square_' + notif.args.x + '_' + notif.args.y, 0, 0 ).play();

          // Update carpet counter for active player
          dojo.byId('carpet_' + notif.args.carpet_type + '_count').innerHTML = notif.args.carpets_left;
        },

        notif_updateScores: function( notif )
        {
          // Update scores
          if ( notif.args.scores && notif.args.scores.length > 0 )
          {
            for(var i=0; i<notif.args.scores.length; i++)
            {
              var player_id = notif.args.scores[i].player_id;
              var score = notif.args.scores[i].score;
              this.scoreCtrl[ player_id ].setValue( score );
            }
          }
        },

        notif_playerEliminatedInfos: function( notif )
        {
          // Reset carpets for this player and disable panel
          dojo.byId('carpet_' + notif.args.carpetType + '_count').innerHTML = '0';
          if( this.playersNumber == 2 )
          {
            var carpet_type_bis = parseInt(notif.args.carpetType) + 1;
            dojo.byId('carpet_' + carpet_type_bis + '_count').innerHTML = '0';
            dojo.destroy('next_carpet_' + notif.args.playerId);
          }

          this.disablePlayerPanel( notif.args.playerId );
        },

        /*
        Example:

        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );

            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

            // TODO: play the card in the user interface.
        },

        */
   });
});
