<?php

class Development {

   const HATCHLING = 'Hatchling';
   const NESTLING  = 'Nestling';
   const FLEDGLING = 'Fledgling';
   const GROWN     = 'Grown';
   
   public static function isUnderDevelopment($d) {
      return ($d != "Grown");
   }
   public static function isGrown($d) {
      return ($d == "Grown");
   }
   
   public static function isHatchling($d) {
      return ($d == "Hatchling");
   }   
   public static function isNestling($d) {
      return ($d == "Nestling");
   }   
   public static function isFledgling($d) {
      return ($d == "Fledgling");
   }   
}
?>