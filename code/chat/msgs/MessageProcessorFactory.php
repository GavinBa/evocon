<?php

require_once "code/chat/msgs/MessageProcessor.php";
require_once "code/chat/msgs/ApplyMessage.php";
require_once "code/chat/msgs/IdleMessage.php";

class MessageProcessorFactory {
   
   private static $MSGS = array (
      "idle"     => "IdleMessage",
      "apply"    => "ApplyMessage",
      "hub"      => "HubMessage"
   );
   
   public static function isValidMsg($msg) {
      $lcmsg = strtolower($msg->message);
      return array_key_exists ($lcmsg, self::$MSGS);
   }
   
   public static function getProcessor($msg) {
      $lcmsg = strtolower($msg->message);
      $mp = false;
      if (self::isValidMsg($msg)) {
         $mpname = self::$MSGS[$lcmsg];
         if (is_string($mpname)) {
            $mp = new $mpname();
         }
      }
      return $mp;
   }
}

?>
