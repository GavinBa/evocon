<?php

require_once "StateProcessor.php";
require_once "states.php";
require_once "lib/util.php";
require_once "code/reports/ReportBuffer.php";
require_once "code/db/DbReportBuffer.php";

class StateWarDefense extends StateProcessor {
   
  var $m_city;
  var $m_cr;
  
  
  public function __construct($cr, $city) {
	  $this->m_city = $city;
     $this->m_cr = $cr;
  }
  
  public function process($cs,$state) {
     
     /* Verify that we are under attack - if not then stand down. */
     if (! $this->m_city->isUnderAttack()) {
        $this->m_cr->getDbc()->setUnderAttack(0);
        return STATE_SUSPEND;
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