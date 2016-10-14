<?php

require_once "code/db/DbCastle.php";
require_once "code/db/DbNeighbors.php";

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
            $a = [ "x" => $this->m_city->getJson()->x, "y" => $this->m_city->getJson()->y, "d" => 10];
            $cs->injectScriptVars("client/scripts/FindCastles.txt",$a);
            $result = STATE_DEADCITIES_CASTLES;
            break;
            
         case STATE_DEADCITIES_CASTLES:
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if (isset($p2json->fields)) {
               $cs->addLine("echo 'Found ".count($p2json->fields)." castles.'");
               foreach ($p2json->fields as $field) {
                  $f = new Field($field);
                  $pres = $f->getPrestige();
                  if ($pres > 0 && $pres < 3000000) {
                     if ($f->isCastle() && $f->canAttack()) {
                        $castle = new DbCastle($this->m_cr->getDbconnect(),$this->m_cr->getServer(),$f->getX(),$f->getY());
                        $castle->create();
                        $castle->setPrestige($pres);
                        
                        // check time of last evaluation
                        $nb = new DbNeighbors($this->m_cr->getDbConnect(), $this->m_cr->getServer(), 
                                              $this->m_cr->getUser(), $this->m_city, $castle->getId());
                        $nb->create();
                        if ($nb->getLastCheck($cs) == 0) {
                           $nb->setLastCheck((string)$this->m_cr->getCtime());
                        }
                        
                        $lastCheck = $nb->getLastCheck($cs);
                        $f1 = floatval($lastCheck);
                        $f2 = $this->m_cr->getCtime();
                        
                        if ($f2-$f1 > 600000) {
                           $cs->addLine("echo '".($f2-$f1)."  since last check'");
                           $cs->addLine("echo 'before=".$nb->getLastPres()." after=".$pres."'");
                           if ($nb->getLastPres() == $pres) {
                              $cs->addLine("echo 'Scouting ".$f->getX().",".$f->getY()."'");
//                              $cs->addLine("scout ".$f->getX().",".$f->getY());
                              break;
                           }
                           $nb->setLastCheck((string)$this->m_cr->getCtime());
                           $nb->setLastPres($pres);
                        }
                        
                        // if past due then
                        //    get last prestige
                        //    get current prestige
                        //    if same then
                        //       dead city
                        //       scout it
                        //    endif
                        // endif
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
