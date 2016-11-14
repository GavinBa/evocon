<?php

require_once "StateProcessor.php";
require_once "states.php";
require_once "lib/util.php";
require_once "code/db/DbCastle.php";
require_once "code/fields/Field.php";
require_once "code/heroes/heroes.php";
require_once "code/reports/ReportBuffer.php";
require_once "code/db/DbReportBuffer.php";

class StateMonitor extends StateProcessor {
   
  var $m_city;
  var $m_cr;
  
  
  public function __construct($cr, $city) {
	  $this->m_city = $city;
     $this->m_cr = $cr;
  }
  
  public function process($cs,$state) {
     
     switch ($state) {

         case STATE_MONITOR:
         
            // Check if city position has changed from stored position.
            $this->checkPosition();
            
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
            $result = STATE_MONITOR_REPORT_BUFFER;
            break;
            
         case STATE_MONITOR_REPORT_BUFFER:
            $dbcity = new DbCity($this->m_cr->getDbconnect(), $this->m_cr->getServer(), $this->m_cr->getUser(), $this->m_city);
            if ($dbcity->isLowestIdForPlayer()) {
               $rb = new ReportBuffer($this->m_cr->getDbconnect(), $this->m_city, $this->m_cr);
               $rb->retrieve($cs);
               $result = STATE_MONITOR_STORE_REPORT_BUFFER;
            } else {
               $result = STATE_IDLE;
            }
            break;
            
         case STATE_MONITOR_STORE_REPORT_BUFFER:
            $ps = util_setParam("p2", 0);
            if ($ps && is_string($ps)) {
               $rb = new ReportBuffer($this->m_cr->getDbconnect(), $this->m_city, $this->m_cr);
               $rb->add($ps);
            }
            $result = STATE_IDLE;
            break;
         
         default:
            $result = STATE_IDLE;
            break;
     }
            
     return $result;
  }
  
   protected function checkPosition() {
      // get my city from castle db
      $dbcity = $this->m_cr->getDbc();
      $idx = $dbcity->getCastleIdx();
      $dbc = DbCastle::fromExisting($this->m_cr->getDbconnect(),$idx);
      
      // if it exists and the castle moved then remove current castle entry
      if ($dbc->exists()) {
         if ($dbc->getX() != $this->m_city->getX() || 
             $dbc->getY() != $this->m_city->getY()) {
            $dbc->remove();
         }
      }
      
      // if there is no castle entry then create one
      if (!$dbc->exists()) {
         // create a new entry
         $dbc = new DbCastle($this->m_cr->getDbconnect(),
                            $this->m_cr->getServer(),
                            $this->m_city->getX(),
                            $this->m_city->getY());
         $dbc->create();
         
         // update the city with the new index
         $dbcity->setCastleIdx($dbc->getId());
      }
  }
  
}

?>