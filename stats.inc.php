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
 * stats.inc.php
 *
 * Marrakech game statistics description
 *
 */

$stats_type = [
  "table" => [
    "table_turns_number" => [
      "id" => 10,
      "name" => totranslate("Number of turns"),
      "type" => "int"
    ],

    "table_largest_carpet_zone" => [
      "id" => 13,
      "name" => totranslate("Largest carpet zone"),
      "type" => "int"
    ],

    "table_highest_taxes_collected" => [
      "id" => 14,
      "name" => totranslate("Highest taxes collected"),
      "type" => "int"
    ],
  ],


  "player" => [
    "player_turns_number" => [
      "id" => 10,
      "name" => totranslate("Number of turns"),
      "type" => "int"
    ],

    "player_money_paid" => [
      "id" => 11,
      "name" => totranslate("Money paid"),
      "type" => "int"
    ],

    "player_money_earned" => [
      "id" => 12,
      "name" => totranslate("Money earned"),
      "type" => "int"
    ],

    "player_largest_carpet_zone" => [
      "id" => 13,
      "name" => totranslate("Largest carpet zone"),
      "type" => "int"
    ],

    "player_highest_taxes_collected" => [
      "id" => 14,
      "name" => totranslate("Highest taxes collected"),
      "type" => "int"
    ],
  ]
];
