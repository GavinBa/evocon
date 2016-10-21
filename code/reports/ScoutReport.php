<?php

class ScoutReport {
   
   var $m_report;
   var $m_doc;
   var $m_reportData;
   
   var $m_workers        = 0;
   var $m_warriors       = 0;
   var $m_scouts         = 0;
   var $m_pike           = 0;
   var $m_swords         = 0;
   var $m_archer         = 0;
   var $m_transporter    = 0;
   var $m_cavalry        = 0;
   var $m_cataphract     = 0;
   var $m_ballista       = 0;
   var $m_rams           = 0;
   var $m_catapult       = 0;
   
   var $m_traps          = 0;
   var $m_abatis         = 0;
   var $m_ats            = 0;
   var $m_logs           = 0;
   var $m_trebs          = 0;
   
   
   public function __construct($report) {
      $this->m_report = $report;
      $this->m_doc = new DOMDocument;
      $this->m_reportData = @simplexml_load_string($report);
      $this->parseTroops();
   }
   
   public function getLoyalty() { return $this->m_reportData[0]->scoutReport->scoutInfo->attributes()["support"]; }
   public function getFood()    { return $this->m_reportData[0]->scoutReport->scoutInfo->resource->food; }
   public function getWood()    { return $this->m_reportData[0]->scoutReport->scoutInfo->resource->wood; }
   public function getIron()    { return $this->m_reportData[0]->scoutReport->scoutInfo->resource->iron; }
   public function getStone()   { return $this->m_reportData[0]->scoutReport->scoutInfo->resource->stone; }
   
   public function getWorkers() { return $this->m_workers; }
   public function getWarriors() { return $this->m_warriors; }
   public function getScouts() { return $this->m_scouts; }
   public function getPike() { return $this->m_pike; }
   public function getSwords() { return $this->m_swords; }
   public function getArchers() { return $this->m_archer; }
   public function getTransporters() { return $this->m_transporter; }
   public function getCavalry() { return $this->m_cavalry; }
   public function getCataphract() { return $this->m_cataphract; }
   public function getBallista() { return $this->m_ballista; }
   public function getRams() { return $this->m_rams; }
   public function getCatapult() { return $this->m_catapult; }
   
   public function getTraps() { return $this->m_traps; }
   public function getAbatis() { return $this->m_abatis; }
   public function getAts() { return $this->m_ats; }
   public function getLogs() { return $this->m_logs; }
   public function getTrebs() { return $this->m_trebs; }
   

   protected function parseFortifications() {
      $node = $this->m_reportData[0]->scoutReport->scoutInfo->fortifications;
      foreach ($node->fortificationsType as $fort) {
         $typeid = $fort->attributes()["typeId"];
         $count = $fort->attributes()["count"];
         switch ($typeid) {
            case 14: // traps
               $this->m_traps = $count;
               break;
            case 15: // abatis
               $this->m_abatis = $count;
               break;
            case 16: // archer towers
               $this->m_ats = $count;
               break;
            case 17: // rolling logs
               $this->m_logs = $count;
               break;
            case 18: // trebuchet
               $this->m_trebs = $count;
               break;
            default:
               break;
         }
      }
   }
   protected function parseTroops() {
      $node = $this->m_reportData[0]->scoutReport->scoutInfo->troops;
      if (isset($node->troopStrType)) {
         foreach ($node->troopStrType as $troop) {
            $typeid = $troop->attributes()["typeId"];
            $count = $troop->attributes()["count"];
            switch ($typeid) {
               case 2: // workers
                  $this->m_workers = $count;
                  break;
               case 3: // warriors
                  $this->m_warriors = $count;
                  break;
               case 4: // scouts
                  $this->m_scouts = $count;
                  break;
               case 5: // pike
                  $this->m_pike = $count;
                  break;
               case 6: // sword
                  $this->m_swords = $count;
                  break;
               case 7: // archer
                  $this->m_archer = $count;
                  break;
               case 8: // transporter
                  $this->m_transporter = $count;
                  break;
               case 9: // cavalry
                  $this->m_cavalry = $count;
                  break;
               case 10: // cataphract
                  $this->m_cataphract = $count;
                  break;
               case 11: // ballista
                  $this->m_ballista = $count;
                  break;
               case 12: // rams
                  $this->m_rams = $count;
                  break;
               case 13: // catapult
                  $this->m_catapult = $count;
                  break;
               default:
                  break;
            }
         }
      }
      
   }

      
   
}
?>
