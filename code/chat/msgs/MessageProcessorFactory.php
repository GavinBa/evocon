<?php

require_once "code/chat/msgs/MessageProcessor.php";
require_once "code/chat/msgs/IdleMessage.php";

class MessageProcessorFactory {
   
   private static $MSGS = array (
      "IDLE" => "IdleMessage"
   );
   
   public static function isValidMsg($msg) {
      return array_key_exists ($msg, self::$MSGS);
   }
   
   public static function getProcessor($msg) {
      $mp = false;
      if (self::isValidMsg($msg)) {
         $mpname = self::$MSGS[$msg];
         if (is_string($mpname)) {
            $mp = new $mpname();
         }
      }
      return $mp;
   }
}

?>
