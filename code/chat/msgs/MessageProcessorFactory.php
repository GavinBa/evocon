<?php

require_once "code/chat/msgs/MessageProcessor.php";
require_once "code/chat/msgs/ApplyMessage.php";
require_once "code/chat/msgs/IdleMessage.php";
require_once "code/chat/msgs/CmdMessage.php";

class MessageProcessorFactory {
   
   private static $MSGS = array (
      "idle"     => "IdleMessage",
      "apply"    => "ApplyMessage",
      "hub"      => "HubMessage",
      "cmd"      => "CmdMessage"
   );
   
   
   public static function getMsg($msg) {
      $lcmsg = strtolower($msg->message);
      return explode(":",$lcmsg)[0];
   }

   public static function isValidMsg($msg) {
      return array_key_exists (self::getMsg($msg), self::$MSGS);
   }
   
   public static function getProcessor($msg) {
      $mp = false;
      if (self::isValidMsg($msg)) {
         $lcmsg = self::getMsg($msg);
         $mpname = self::$MSGS[$lcmsg];
         if (is_string($mpname)) {
            $mp = new $mpname();
         }
      }
      return $mp;
   }
}

?>
