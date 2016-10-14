<?php
class Field {
	
  var $m_field;
  
  public function __construct($field) {
	$this->m_field = $field;
  }
  
  public function getUser() {
     if (isset($this->m_field->userName)) {
      return $this->m_field->userName;
     } else {
        return "nouser";
     }
  }
  
  public function getRelation() {
     if (isset($this->m_field->relation)) {
        return $this->m_field->relation;
     } else {
        return -1;
     }
  }
  
  public function isOwned() {
     return ($this->getUser() != null) ? 1 : 0;
  }
  
  public function canAttack() {
     return ($this->getRelation() >= 2) ? 1 : 0;
  }
  
  public function getCoords() {
     if (isset($this->getJson()->coords)) {
        return $this->getJson()->coords;
     } else {
        return "";
     }
  }
  
  public function getX() {
     $a = explode(",",$this->getCoords());
     if (count($a) == 2) {
        return $a[0];
     }
     return -1;
  }
  
  public function getY() {
     $a = explode(",",$this->getCoords());
     if (count($a) == 2) {
        return $a[1];
     }
     return -1;
  }
  
  public function getPrestige() {
     if (isset($this->getJson()->prestige)) {
        return $this->getJson()->prestige;
     } else {
        return 0;
     }
  }
  
  public function getJson() {
     return $this->m_field;
  }
  
  public function isCastle() {
     $result = false;
     if (isset($this->getJson()->flag)) {
        $result = $this->getJson()->flag != null;
     }
     return $result;
  }
  
}
?>