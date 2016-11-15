<?php

class HubMessage extends MessageProcessor {
   
   public function process($cr,$cs,$msg,$dbalt) {
      $txtmsg = $msg->message;
      // hub:{x}:{y}
      $a = explode(":",$txtmsg);
      if (count($a) == 3) {
         //dumpresource 111,222 f:11000,g:44000 f:3000,g:9000
         $cs->addLine("dumpresource " . $a[1] . "," . $a[2] . " w:5m w:2m");
         $cs->addLine("dumpresource " . $a[1] . "," . $a[2] . " s:5m w:2m");
         $cs->addLine("dumpresource " . $a[1] . "," . $a[2] . " i:5m w:2m");
      }
   }
   
}

?>