<?php

require_once "StateProcessor.php";
require_once "states.php";
require_once "lib/util.php";
require_once "code/fields/Field.php";
require_once "code/heroes/heroes.php";

class StateMonitor extends StateProcessor {
   
  var $m_city;
  
  
  public function __construct($city) {
	$this->m_city = $city;
  }
  
  public function process($cs,$state) {
     
     switch ($state) {

         case STATE_MONITOR:
            $result = STATE_MONITOR_FIELDS;
            break;
            
         case STATE_MONITOR_FIELDS:
            $cs->injectScript("client/scripts/FindLevel10Flats.txt");
            $result = STATE_MONITOR_FIELDS_RESULTS;
            break;
            
         case STATE_MONITOR_FIELDS_RESULTS:
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            $h = new Heroes($this->m_city);
            $hCnt = 0;
            if (isset($p2json->fields)) {
               foreach ($p2json->fields as $field) {
                  if ($hCnt++ < $h->getNumAvailable()) {
                     $f = new Field($field);
                     $cs->addline("echo 'field=".$f->getCoords()." isOwned=".$f->isOwned()." canAttack=".$f->canAttack()."'");
                     if ($f->isOwned() == 1 && $f->canAttack() == 1) {
                        $a = [ "user" => $f->getUser(), "coords" => $field->coords];
                        $cs->injectScriptVars("client/scripts/AttackOccupiedFlat.txt", $a);
                     }
                  }
               }
            }
            $result = STATE_IDLE;
            break;
         
         default:
            $result = STATE_IDLE;
            break;
     }
            
     return $result;
  }
  
}

?>