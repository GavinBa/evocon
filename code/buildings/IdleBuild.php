<?php

class IdleBuild extends StateProcessor {

   var $m_city;

   private static $builds = array (
      array("Feasting Hall", "fh", 9),
      array("Academy",       "a", 9),
      array("Cottage",       "c", 9),
      array("Town Hall",     "t", 9),
      array("Marketplace",   "m", 9),
      array("Barracks",      "b", 9)
   );
   
   public function __construct($city) {
	   $this->m_city = $city;
   }   

   public function process($cs,$state) {
      /* Check if there is no building going on */
      if (count($this->m_city->getJson()->castle->buildingQueuesArray) == 0) {
         $cs->addLine("echo 'Building IDLE'");
         foreach (self::$builds as list($name,$abbr,$lvl)) {
            if ($this->buildTo($cs,$name,$abbr,$lvl) == STATE_IDLE) {
               $cs->addLine("echo 'Started build of " . $name . " to level " . $lvl . "'");
               return STATE_IDLE;
            }
         }
      }
      return STATE_IDLE;
   }
   
   protected function buildTo($cs,$bname,$babbr,$lvl) {
      $cnt = $this->getBuildingTotal($bname);
      if ($cnt == 1) {
         $actual = $this->getBuildingLevel($bname);
         if ($actual > 0 && $actual < $lvl) {
            $cs->addLine("build ".$babbr.":".$lvl);
            return STATE_IDLE;
         }
      } else {
         $actual = $this->getMinBuildingLevel($bname);
         if ($actual > 0 && $actual < $lvl) {
            $cnt = $this->getBuildingTotal($bname);
            $cs->addLine("build ".$babbr.":".$lvl.":".$cnt);
            return STATE_IDLE;
         }
      }
      return STATE_IDLEBUILDS;
   }
   
   protected function getBuildingLevel($bname) {
      foreach ($this->m_city->getJson()->castle->buildingsArray as $b) {
         if ($b->name == $bname) {
            return $b->level;
         }
      }
      return -1;
   }

   protected function getMinBuildingLevel($bname) {
      $result = -1;
      foreach ($this->m_city->getJson()->castle->buildingsArray as $b) {
         if ($b->name == $bname) {
            if ($result == -1) {
               $result = $b->level;
            } else if ($result > $b->level) {
               $result = $b->level;
            }
         }
      }
      return $result;
   }
   
   protected function getBuildingTotal($bname) {
      $total = 0;
      foreach ($this->m_city->getJson()->castle->buildingsArray as $b) {
         if ($b->name == $bname) {
            $total = $total + 1;
         }
      }
      return $total;
   }
   
}

?>
