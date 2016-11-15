<?php

class ApplyMessage extends MessageProcessor {
   
   public function process($cr,$cs,$msg,$dbalt) {
     $cs->addLine("command \"accept " . $msg->from . "\"");
   }
   
}

?>