<?php

class Buildings {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
  }
  
  public function getInnLevel() {
	  foreach($this->m_city->getJson()->buildings as $myBuilding) {
		  if ($myBuilding->name == "Inn") {
			  return $myBuilding->level;
		  }
	  }
	  return 0;
  }
}
?>
