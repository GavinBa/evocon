<?php

class City {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
  }

  public function getName() {
	  return $this->m_city->name;
  }  
  
  public function getNumFieldsOwned() {
     return count($this->m_city->castle->fieldsArray);
  }
  
  public function isUnderAttack() {
     return count($this->m_city->enemyArmies) > 0;
  }
  
  public function getJson() {
     return $this->m_city;
  }

}
?>
