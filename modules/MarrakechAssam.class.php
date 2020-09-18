<?php

class MarrakechAssam extends APP_GameClass
{
  public static function init(){
    self::DbQuery("INSERT INTO assam (x,y,dir) VALUES (4,4,2)");
  }

  /*
   * Return Assam state as associative array : x,y and dir
   */
  public static function get() {
    $assam = self::getObjectFromDB("SELECT * FROM assam LIMIT 1");
    $assam = array_map('intval', $assam);
    return $assam;
  }


  /*
   * Set the new position/direction of Assam and notify frontend
   */
  public static function set($state){
    $assam = self::get();
    self::DbQuery("UPDATE assam SET x = {$state['x']}, y = {$state['y']}, dir = {$state['dir']}");

    if($assam['dir'] != $state['dir']){
      NotificationManager::rotateAssam($state);
    }

    if($assam['x'] != $state['x'] || $assam['y'] != $state['y']){
      NotificationManager::moveAssam($state);
    }
  }


  /*
   * Rotate Assam : +1 for right, -1 for left, 0 for skip
   */
  public static function rotate($d){
    $assam = self::get();
    self::set([
      'x' => $assam['x'],
      'y' => $assam['y'],
      'dir' => ($assam['dir'] + $d + 4) % 4,
    ]);
  }



  /*
   * Possible direction for Assam and corresponding deltas
   */
  public static $deltas = [
    NORTH => ['x' =>  0, 'y' => -1],
    EAST  => ['x' =>  1, 'y' =>  0],
    SOUTH => ['x' =>  0, 'y' =>  1],
    WEST  => ['x' => -1, 'y' =>  0],
  ];


  // Representation of the board edges (R : turn right, L : turn left, C : corner (keep moving))
  public static $board = [
    'CRLRLRLRC',
    'L_______L',
    'R_______R',
    'L_______L',
    'R_______R',
    'L_______L',
    'R_______R',
    'L_______L',
    'CRLRLRLRC',
  ];
  public static function getCaseRotation(){
    $assam = self::get();
    return self::$board[$assam['y']][8 - $assam['x']];
  }



  /*
   * moveForward : move Assam one step forward
   */
  public static function moveForward(){
    $assam = self::get();
    self::set([
      'x' => $assam['x'] + self::$deltas[$assam['dir']]['x'],
      'y' => $assam['y'] + self::$deltas[$assam['dir']]['y'],
      'dir' => $assam['dir'],
    ]);
  }

  /*
   * moveOneSetp : first make a move forward, then handle the turnarounds at the edges of the board
   */
  public static function moveOneStep(){
    // (Rotate) and move one step forward, until Assam is back *inside* the board
    $rotation = null;
    do {
      if(!is_null($rotation))
        self::rotate($rotation == 'R'? 1 : -1);
      self::moveForward();

      $caseRotation = self::getCaseRotation();
      if(is_null($rotation))
        $rotation = $caseRotation;
    }
    while($caseRotation != '_');
  }


  /*
   * move : move $roll steps
   */
  public static function move($roll){
    for ($i = 0; $i < $roll; $i++)
      self::moveOneStep();
  }
}
