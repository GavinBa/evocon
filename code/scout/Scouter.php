<?php

require_once ("code/db/DbScout.php");

class Scouter {
   
   var $m_cr;
   var $m_dbscout;
   var $m_dbcastle;
   var $m_cs;

   /*
    - Is this city scouting someone.
    - If idle, send scout
    - record an active scout
    - check on scout report (in report buffer)
    - produce scout report
    - record city no longer scouting
    - provide status on scout (active, idle, done)
    - compute expected arrival of scout (for cleanup)
    - purge an active scout operation if overdue and not found
    */
   public function __construct($cr, $dbcastle, $cs) {
      $this->m_cr = $cr;
      $this->m_dbcastle = $dbcastle;
      $this->m_cs = $cs;
      $this->m_dbscout = new DbScout(
            $cr->getDbconnect(), 
            $cr->getDbc(), 
            $dbcastle->getId(), 
            $dbcastle->getX(), 
            $dbcastle->getY());
   }
   
   public static function fromExisting($cr, $cs) {
      $dbs = DbScout::fromExisting($cr->getDbconnect(), $cr->getDbc());
      $instance = new self($cr,DbCastle::fromExisting($cr->getDbconnect(),$dbs->getCastleId()),$cs);
      return $instance;
   }
   
   public static function isActiveScout($cr,$cs) {
      return ! DbScout::fromExisting($cr->getDbconnect(), $cr->getDbc())->isIdle();
   }
   
   public function setReportTime ($rt) {
      $this->m_dbscout->setReportTime($rt);
   }
   
   public function getReportTime () {
      return $this->m_dbscout->getReportTime();
   }
   
   public function getTroopStr() { return "s:1"; }
   
   public function canScout() {
      return $this->m_dbscout->isIdle();
   }
   
   public function sendScout() {
      $this->m_cs->addLine("if city.rallySpotAvailable() scout " . $this->m_dbcastle->getX() . "," . $this->m_dbcastle->getY());
      /* Update the time it was sent with the client time */
      $this->m_dbscout->setScoutTime($this->m_cr->getCtime());
      $this->m_dbscout->setState(SCOUT_PENDING);
      
   }
   
   public function getX() { return $this->m_dbscout->getX(); }
   public function getY() { return $this->m_dbscout->getY(); }
   
   public function isArrived($tt) {
      $sentTime = floatval($this->m_dbscout->getScoutTime());
      $currTime = floatval($this->m_cr->getCtime());
      
      return ($sentTime + ($tt * 1000) + 5000) <= $currTime;
   }
   
   public function setAttackTime($tt) {
      $this->m_dbscout->setAttackTime($tt);
   }
   
   public function getAttackTime() {
      return $this->m_dbscout->getAttackTime();
   }
   
   public static function complete($cr, $cs) {
      $dbs = DbScout::fromExisting($cr->getDbconnect(), $cr->getDbc());
      if ($dbs->hasTarget()) {
         if (!$dbs->delete()) {
            printf("Error on delete\n");
         }
      }
   }
   
}
?>