<?php

require_once "code/db/DbAlts.php";


class ChatMonitor {

  protected $m_cr;
  protected $m_dba;
  
  public function __construct($cr) {
     $this->m_cr = $cr;
     $this->m_dba = new DbAlts($cr->getDbconnect(),$cr->getServer(),$cr->getUser(),$cr->getCity());
  }
  
  public function process($cs) {
     if ($this->m_dba->isHost()) {
         $p9 = util_setParam("p9", false);
         if ($p9 && is_string($p9)) {
            $p9json = json_decode($p9);
            if (is_array($p9json)) {
               foreach ($p9json as $chatmsg) {
                  $cs->addEcho("Msg from " . $chatmsg->from . " : " . $chatmsg->message);
                  $msgLower = strtolower($chatmsg->message);
                  if ($msgLower == "apply") {
                     $this->processApply($cs,$chatmsg);
                  }
               }
            }
         }
         
         // Only need to do this on one city.
         if ($this->m_cr->getDbc()->isLowestIdForPlayer()) {
            $cs->addEcho("Monitoring chat...");
            $cs->injectScript("client/scripts/GetChatMsgs.txt");
         }
     }
     
     
  }
  
  protected function processApply($cs,$msg) {
     $cs->addLine("command \"accept " . $msg->from . "\"");
  }
  
  
}

?>
   