<?php

require_once "code/chat/msgs/MessageProcessorFactory.php";
require_once "code/db/DbAlts.php";
require_once "code/timers/Timer.php";
require_once "code/timers/TimerType.php";


class ChatMonitor {

  protected $m_cr;
  protected $m_dba;
  protected $m_timer;
  
  public function __construct($cr) {
     $this->m_cr = $cr;
     $this->m_dba = new DbAlts($cr->getDbconnect(),$cr->getServer(),$cr->getUser(),$cr->getCity());
     $this->m_timer = new Timer(
           $this->m_cr->getDbconnect(),
           $this->m_cr->getServer(), 
           $this->m_cr->getUser(), 
           TimerType::MONITOR_CHAT);

  }
  
  public function process($cs) {
      $p9 = util_setParam("p9", false);
      if ($p9 && is_string($p9)) {
         $p9json = json_decode($p9);
         if (is_array($p9json)) {
            foreach ($p9json as $chatmsg) {
               $cs->addEcho("Msg from " . $chatmsg->from . " : " . $chatmsg->message);
               if (MessageProcessorFactory::isValidMsg($chatmsg)) {
                  $mp = MessageProcessorFactory::getProcessor($chatmsg);
                  if ($mp) {
                     $mp->process($this->m_cr,$cs,$chatmsg,$this->m_dba);
                  }
               }
            }
         }
      }
      
      $this->checkTimer($cs);
  }
  
  
  public function checkTimer($cs) {
     $cs->addEcho("checking timer");
     if ($this->m_cr->getDbc()->isLowestIdForPlayer()) {
        if ($this->m_timer->hasExpired($this->m_cr->getCtime())) {
           $cs->addEcho("Timer expired");
           $cs->addEcho("Monitoring chat...");
           $cs->injectScript("client/scripts/GetChatMsgs.txt");
           $this->m_timer->setExpiration($this->m_cr->getCtime(),15000);
        }
     }
  }
  
  
  
}

?>
   