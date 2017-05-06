<?php

class CmdMessage extends MessageProcessor {
   
   public function process($cr,$cs,$msg,$dbalt) {
      $cmdmsg = strtolower($msg->message);
      $cmd = explode(":",$cmdmsg)[1];
      $cs->addLine($cmd);
   }
   
}

?>