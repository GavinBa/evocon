<?php

require_once "code/scout/Scouter.php";

class AttackDeadCity {
   var $m_city;
   var $m_dcr;  // dead city report
   var $m_cr;
   
   public function __construct($city,$cr,$report) {
      if (! is_a($report, "DeadCityReport")) {
         throw new Exception();
      }
      $this->m_dcr = $report;
      $this->m_city = $city;
      $this->m_cr = $cr;
   }
   
   public function attack ($cs) {
      if ($this->m_dcr->canAttack()) {
         $cs->addLine("echo 'sending attack ...'");
         $scouter = Scouter::fromExisting($this->m_cr,$cs);
         $a = [ "x" => $scouter->getX(), 
                "y" => $scouter->getY(), 
                "tr" => "a:20k,w:1,s:1,p:1,sw:1,t:500", 
                "trainer" => $this->m_city->getJson()->trainingHeroName ];
         $cs->injectScriptVars("client/scripts/AttackDeadCity.txt",$a);
      } else {
         $cs->addLine("echo 'unable to attack'");
      }
   }
}
?>