<?php

require_once "code/db/DbResProfile.php";

class City {
	
  var $m_city;
  var $m_dbconnect     = NULL;
  var $m_resprofile = NULL;
  
  public function __construct($city) {
	$this->m_city = $city;
  }

  public function setResProfile($rp) {
     $this->m_resprofile = $rp;
  }
  
  public function getName() {
	  return $this->m_city->name;
  }  
  
  public function setNameTestOnly($s) {
     $this->m_city->name = $s;
  }
  
  public function getNumFieldsOwned() {
     return count($this->m_city->castle->fieldsArray);
  }
  
  public function isUnderAttack() {
     return count($this->m_city->enemyArmies) > 0;
  }
  
  public function getRallyLevel() {
     return 0;
  }
  
  public function getJson() {
     return $this->m_city;
  }
  
  public function getFields() {
     return $this->m_city->castle->fieldsArray;
  }
  
  public function hasField($fid) {
     foreach ($this->getFields() as $field) {
        if ($field->id == $fid) {
           return true;
        }
     }
     return false;
  }
  
  public function getX() {
     return $this->m_city->x;
  }
  
  public function getY() {
     return $this->m_city->y;
  }
  
  public function getNumMarchingArmies() {
     return count($this->m_city->selfArmies);
  }
  
  public function getSelfArmies() {
     return $this->m_city->selfArmies;
  }
  
  public function getEnemyArmies() {
     return $this->m_city->enemyArmies();
  }
  
  public function isArmyFieldId($fid) {
     foreach ($this->m_city->selfArmies as $army) {
        if ($army->targetFieldId == $fid) {
           return true;
        }
     }
     return false;
  }
  public function isArmyFieldIdDirection($fid,$direction) {
     foreach ($this->m_city->selfArmies as $army) {
        if ($army->targetFieldId == $fid && $army->direction == $direction) {
           return true;
        }
     }
     return false;
  }
  
  //TODO replace with db call
  public function getBuyAmt() {
     $v = 20000;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getBuyAmt();
     }
     return $v;
  }
  
  public function getFoodAmt() {
     return $this->m_city->resource->food->amount;
  }
  public function getWoodAmt() {
     return $this->m_city->resource->wood->amount;
  }
  public function getIronAmt() {
     return $this->m_city->resource->iron->amount;
  }
  public function getStoneAmt() {
     return $this->m_city->resource->stone->amount;
  }
  public function getGoldAmt() {
     return $this->m_city->resource->gold;
  }
  
  public function getReservedWoodAmt() {
     return $this->m_city->reservedResource->wood;
  }
  public function getReservedStoneAmt() {
     return $this->m_city->reservedResource->stone;
  }
  public function getReservedIronAmt() {
     return $this->m_city->reservedResource->iron;
  }
  public function getReservedFoodAmt() {
     return $this->m_city->reservedResource->food;
  }
  public function getReservedGoldAmt() {
     return $this->m_city->reservedResource->gold;
  }
  
  public function getAvgSpan() {
     $v = 200;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getAvgSpan();
     }
     return $v;
  }
  
  public function getFoodRate() {
     return $this->m_city->troop->foodConsumeRate;
  }
  
  //TODO replace with db call
  public function getMaxWoodAmt() {
     $v = 2000000;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getMaxWoodAmt();
     }
     return $v;
  }

  //TODO replace with db call
  public function getMaxStoneAmt() {
     $v = 2000000;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getMaxStoneAmt();
     }
     return $v;
  }
  
  //TODO replace with db call
  public function getMaxIronAmt() {
     $v = 2000000;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getMaxIronAmt();
     }
     return $v;
  }
  
  public function getMaxGoldAmt() {
     $v = 5000000;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getMaxGoldAmt();
     }
     return $v;
  }
  
  public function getMaxFoodAmt() {
     $v = 10000000;
     if ($this->m_resprofile != NULL) {
        $v = $this->m_resprofile->getMaxFoodAmt();
     }
     return $v;
  }

}
?>
