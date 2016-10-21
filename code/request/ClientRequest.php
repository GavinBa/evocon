<?php

require_once "code/db/DbCity.php";

class ClientRequest {
   
   var $m_dbconnect;
   var $m_dbc;
   var $m_server;
   var $m_user;
   var $m_city;
   var $m_cTime;
   var $m_ut;
   
   public function __construct($dbc, $server, $user, $city) {
      $this->m_dbconnect = $dbc;
      $this->m_dbc = new DbCity($dbc,$server,$user,$city);
      $this->m_server = $server;
      $this->m_user = $user;
      $this->m_city = $city;
      $this->m_cTime = 0;
      $this->m_ut = false;
   }
   
   public function getDbconnect() {
      return $this->m_dbconnect;
   }
   
   public function getDbc() {
      return $this->m_dbc;
   }
   
   public function getServer() {
      return $this->m_server;
   }
   
   public function getUser() {
      return $this->m_user;
   }
   
   public function getCity() {
      return $this->m_city;
   }
   
   public function getCtime() {
      return $this->m_cTime;
   }
   
   public function setCtime($ctime) {
      $this->m_cTime = $ctime;
   }
   
   public function isTest() {
      return $this->m_ut;
   }
   
   public function setUt($ut) {
      $this->m_ut = $ut;
   }

}
?>
