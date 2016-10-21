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
      return true;
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
      if ($this->getWood() < 10000000) {
         return true;
      }
      return false;
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