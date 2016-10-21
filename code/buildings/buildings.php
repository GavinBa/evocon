<?php

class Buildings {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
  }
  
  public function getInnLevel() {
     return $this->getBuildingLevel("Inn");
  }
  
  public function getRallyLevel() {
     return $this->getBuildingLevel("Rally Spot");
  }
  
  public function getBuildingLevel($bStr) {
	  foreach($this->m_city->getJson()->buildings as $myBuilding) {
		  if ($myBuilding->name == $bStr) {
			  return $myBuilding->level;
		  }
	  }
	  return 0;
  }
}
?>
