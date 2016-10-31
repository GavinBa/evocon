<?php

class City {
	
  var $m_city;
  
  public function __construct($city) {
	$this->m_city = $city;
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
  
  public function getNumMarchingArmies() {
     return count($this->m_city->selfArmies);
  }
  
  public function getSelfArmies() {
     return $this->m_city->selfArmies();
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

}
?>
