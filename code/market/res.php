<?php

class Market {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
  }
  
  public function getWood() {
    return $this->m_city->getJson()->resource->wood->amount;
  }
  public function getIron() {
	  return $this->m_city->getJson()->resource->iron->amount;
  }
  public function getFood() {
	  return $this->m_city->getJson()->resource->food->amount;
  }
  public function getStone() {
	  return $this->m_city->getJson()->resource->stone->amount;
  }
  public function getGold() {
	  return $this->m_city->getJson()->resource->gold;
  }

}
?>
