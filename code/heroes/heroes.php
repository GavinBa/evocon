<?php

class Heroes {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
  }

  public function getTotalSalary() {
	  return $this->m_city->getJson()->resource->herosSalary;
  }  
  
  public function getNumAvailable() {
     $result = 0;
     foreach ($this->m_city->getJson()->heroes as $hero) {
        if ($hero->isAvailable) {
           $result++;
        }
     }
     return $result;
  }

}
?>
