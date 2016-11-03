<?php

require_once "code/reports/ScoutReport.php";

class DeadCityReport extends ScoutReport {

   public function canAttack() {
      if ($this->nogoLoyalty()) {
         return false;
      }
      if ($this->nogoTroops()) {
         return false;
      }
      if ($this->nogoResources()) {
         return false;
      }
      if ($this->nogoForts()) {
         return false;
      }
      if ($this->nogoCapture()) {
         return false;
      }
      return true;
   }
   
   protected function nogoCapture() {
      return ($this->getLoyalty() <= 5);
   }
   
   protected function nogoLoyalty() {
      return ($this->getLoyalty() >= 90);
   }
   
   protected function nogoTroops() {
      if ($this->getArchers() >= 20000) {
         return true;
      }
      return false;
   }
   
   protected function nogoResources() {
      if ($this->getWood() > 5000000) {
         return false;
      }
      if ($this->getFood() > 50000000) {
         return false;
      }
      if ($this->getIron() > 3000000) {
         return false;
      }
      if ($this->getGold() > 5000000) {
         return false;
      }
      return true;
   }
   
   protected function nogoForts() {
      return ($this->nogoAts());
   }
   
   protected function nogoAts() {
      if ($this->getAts() > 5000) {
         return true;
      }
      return false;
   }
}
?>