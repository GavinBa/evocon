<?php

require_once "code/db/DbCastle.php";
require_once "code/db/DbNeighbors.php";
require_once "code/scout/Scouter.php";
require_once "code/reports/DeadCityReport.php";
require_once "code/attack/AttackDeadCity.php";

class DeadCities extends StateProcessor {

   var $m_city;
   var $m_cr;

  
   public function __construct($city,$cr) {
	   $this->m_city = $city;
      $this->m_cr = $cr;
   }   

   public function process($cs,$state) {
      
      switch ($state) {
         case STATE_DEADCITIES:
            $cs->addLine("echo 'Searching for dead cities nearby...'");
            $a = [ "x" => $this->m_city->getJson()->x, "y" => $this->m_city->getJson()->y, "d" => 15];
            $cs->injectScriptVars("client/scripts/FindCastles.txt",$a);
            $result = STATE_DEADCITIES_CASTLES;
            break;
            
         case STATE_DEADCITIES_CASTLES:
            $scoutSent = false;
            $result = STATE_IDLE;

            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if (isset($p2json->fields)) {
               $cs->addLine("echo 'Found ".count($p2json->fields)." castles.'");
               
               foreach ($p2json->fields as $field) {
                  $f = new Field($field);
                  $pres = $f->getPrestige();
                  if ($this->isCandidate($f)) {
                     
                     $castle = $this->addTargetCastle($f);
                     
                     // check time of last evaluation
                     $nb = $this->addNeighbor($castle);
                     
                     $lastCheck = $nb->getLastCheck();
                     $f1 = floatval($lastCheck);
                     $f2 = floatval($this->m_cr->getCtime());
                     
                     if ($this->timeToCheckPres($f2,$f1)) {
                        $cs->addLine("echo '".($f2-$f1)."  since last check'");
                        $cs->addLine("echo 'before=".$nb->getLastPres()." after=".$pres."'");
                        if ($nb->getLastPres() == $pres) {
                           $cs->addLine("echo 'Scouting ".$f->getX().",".$f->getY()."'");
                           $scouter = new Scouter($this->m_cr, $castle, $cs);
                           if ($scouter->canScout()) {
                              $scouter->sendScout();
                              $scoutSent = true;
                              $a = [ "x" => $scouter->getX(), "y" => $scouter->getY(), "troops" => $scouter->getTroopStr()];
                              $cs->injectScriptVars("client/scripts/GetTravelTime.txt",$a);
                           }
                        }
                        $nb->setLastCheck((string)$this->m_cr->getCtime());
                        $nb->setLastPres($pres);
                        
                        if ($scoutSent) {
                           $result = STATE_DEADCITIES_SCOUTING;
                           break;
                        }
                     }
                  }
               }
            }
            break;
            
         case STATE_DEADCITIES_WAITSCOUT:
            $scouter = Scouter::fromExisting($this->m_cr,$cs);
            if ($scouter->isArrived($scouter->getAttackTime())) {
                  /* Find the report in the reports buffer and return the xml */
                  $a = [ "x" => $scouter->getX(), "y" => $scouter->getY()];
                  $cs->injectScriptVars("client/scripts/GetScoutReportOfCity.txt", $a);
                  $result = STATE_DEADCITIES_REPORT;
            }
            else {
               $result = STATE_DEADCITIES_WAITSCOUT;
            }
            break;

         case STATE_DEADCITIES_SCOUTING:
         
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            $scouter = Scouter::fromExisting($this->m_cr,$cs);

            /* First time entering this state should have attack time. */            
            if (isset($p2json->attack)) {
               $attackTime = $p2json->attack;
               $scouter->setAttackTime($attackTime);
            } else {
               $attackTime = $scouter->getAttackTime();
            }

            if ($scouter->isArrived($attackTime)) {
               /* Find the report in the reports buffer and return the xml */
               $a = [ "x" => $scouter->getX(), "y" => $scouter->getY()];
               $cs->injectScriptVars("client/scripts/GetScoutReportOfCity.txt", $a);
               $result = STATE_DEADCITIES_REPORT;
            } else {
               $result = STATE_DEADCITIES_SCOUTING;
            }
            break;
            
         case STATE_DEADCITIES_REPORT:
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if (!isset($p2json->report) || $p2json->report == "none") {
               $cs->addLine("echo 'not found yet'");
//               $result = STATE_DEADCITIES_SCOUTING;
               $result = STATE_IDLE;
            } else {
               $sr = new DeadCityReport($p2json->report);
               $cs->addEcho("Food=" . $sr->getFood());
               $cs->addEcho("Workers=" . $sr->getWorkers());
               $cs->addEcho("Loyalty=" . $sr->getLoyalty());
               $cs->addEcho("report canAttack=" . $sr->canAttack());
               $adc = new AttackDeadCity($this->m_city,$this->m_cr,$sr);
               $adc->attack($cs);
               $result = STATE_IDLE;
            }
            break;
            
         default:
            $result = STATE_IDLE;
            break;
      }
      
      return $result;
   }
   
   protected function isCandidate($f) {
      return ($f->isCastle() && $f->canAttack() && 
              $f->getPrestige() > 0 && $f->getPrestige() < 5000000);
   }
   
   protected function addTargetCastle($f) {
      $castle = new DbCastle($this->m_cr->getDbconnect(),
                             $this->m_cr->getServer(),
                             $f->getX(),$f->getY());
      $castle->create();
      $castle->setPrestige($f->getPrestige());
      return $castle;
   }
   
   protected function addNeighbor($castle) {
      $nb = new DbNeighbors($this->m_cr->getDbConnect(), $this->m_cr->getServer(), 
                            $this->m_cr->getUser(), $this->m_city, $castle->getId());
      $nb->create();
      if ($nb->getLastCheck() == 0) {
         $nb->setLastCheck((string)$this->m_cr->getCtime());
      }
      return $nb;
   }
   
   protected function timeToCheckPres($currTime,$lastTime) {
      return ($currTime - $lastTime) > 1800000;
   }
   
}

?>
