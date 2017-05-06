<?php

require_once "StateProcessor.php";
require_once "states.php";
require_once "lib/util.php";
require_once "code/reports/ReportBuffer.php";
require_once "code/db/DbReportBuffer.php";
require_once "code/timers/Timer.php";
require_once "code/timers/TimerType.php";

class StateWarDefense extends StateProcessor {
   
  var $m_city;
  var $m_cr;
  var $m_timer;
  
  
  public function __construct($cr, $city) {
	  $this->m_city = $city;
     $this->m_cr = $cr;
  }
  
  public function process($cs,$state) {
     
     /* Verify that we are under attack - if not then stand down. */
     if (! $this->m_city->isUnderAttack()) {
        $this->m_cr->getDbc()->setUnderAttack(0);
        $cs->addLine("config wartown:0");
        return STATE_SUSPEND;
     }
     
     // While under attack send help message periodically
     $this->m_timer = new Timer(
           $this->m_cr->getDbconnect(),
           $this->m_cr->getServer(), 
           $this->m_cr->getUser(), 
           TimerType::ATTACK_ALERT);

     if ($this->m_timer->hasExpired($this->m_cr->getCtime())) {
        $cs->addEcho("Timer expired");
        $cs->addLine("whisper Agastus 'Help - under attack'");
        $this->m_timer->setExpiration($this->m_cr->getCtime(),30000);
     }
     
        
     switch ($state) {

         case STATE_WAR:
            $result = STATE_WAR_CARRYLOAD;
            
            $cs->addLine("config wartown:1");
            
            // figure out carrying load
            $a = [ "troops" => "t:*,wo:*,c:*"];
            $cs->injectScriptVars("client/scripts/GetCarryingLoad.txt",$a);
            break;
            
         case STATE_WAR_CARRYLOAD:
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if ($p2 && isset($p2json->result)) {
               $cload = $p2json->result;
               
               // prepare to send valuables elsewhere (iron,wood)
               // for the rest figure out current prices and prioritize
               
            }
            
            $result = STATE_SUSPEND;
            break;
            
         default:
            $result = STATE_SUSPEND;
            break;
     }
            
     return $result;
  }
  
}

?>