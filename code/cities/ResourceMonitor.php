<?php

require_once "code/buildings/buildings.php";
require_once "code/cities/city.php";

class ResourceMonitor {
	
  var $m_city;
  var $m_cr;
  var $m_cs;
  
  public function __construct($city, $cr) {
	$this->m_city      = $city;
   $this->m_cr        = $cr;
  }
  
  public function process($cs) {
     $this->m_cs = $cs;
     $this->m_cr->getDbc()->setGold($this->m_city->getGoldAmt());
     $this->m_cr->getDbc()->setFood($this->m_city->getFoodAmt());
     $this->m_cr->getDbc()->setWood($this->m_city->getWoodAmt());
     $this->m_cr->getDbc()->setStone($this->m_city->getStoneAmt());
     $this->m_cr->getDbc()->setIron($this->m_city->getIronAmt());
  }
  

}