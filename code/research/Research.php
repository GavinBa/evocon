<?php

class Research {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
  }
  
  public function getArcheryLevel() {
     return $this->getResearchLevel("14");
  }
  
  public function getHorsebackRidingLevel() {
     return $this->getResearchLevel("13");
  }
  
  public function getMilitaryTraditionLevel() {
     return $this->getResearchLevel("9");
  }
  
  public function getMasonryLevel() {
     return $this->getResearchLevel("3");
  }
  
  public function getResearchLevel($rStr) {
     return $this->m_city->getJson()->researches->$rStr->level;
  }

  
}
?>
