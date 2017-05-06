<?php

require_once ("code/db/DbTimer.php");

class Timer {
   
   private $m_db;
   private $m_server;
   private $m_player;
   private $m_type;
   private $m_dbtimer;

   public function __construct($db,$server,$player,$type) {
      $this->m_server = $server;
      $this->m_player = $player;
      $this->m_type   = $type;
      
      $this->m_dbtimer = new DbTimer($db,$server,$player,$type);
      if (!$this->m_dbtimer->hasTimer()) {
         $this->m_dbtimer->createTimer();
      }
   }
   
   public function hasExpired($ctime) {
      $val = $this->m_dbtimer->getTimerVal();
      $expTime = floatval($val);
      $curTime = floatval($ctime);
      //number_format($float,0,'.','');
      return ($expTime < $curTime);
   }
   
   public function setExpiration($ctime,$offset_in_millis) {
      $f1 = floatval($ctime);
      $f2 = floatval($offset_in_millis);
      $expTime = ($f1 + $f2);
      $expStr  = number_format($expTime,0,'.','');
      $this->m_dbtimer->setTimerVal($expStr);
   }
   
   public function getDbTimerTestOnly() {
      return $this->m_dbtimer;
   }
   
   public function isActive($ctime) {
      $f1 = floatval($ctime);
      $f2 = floatval($this->m_dbtimer->getTimerVal());
      return ($f1 < $f2);
   }
   
   public function cancel() {
      $this->m_dbtimer->setTimerVal(0);
   }
   
}

?>