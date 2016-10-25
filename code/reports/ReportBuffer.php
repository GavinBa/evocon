<?php

class ReportBuffer {
   
   private $m_city;
   private $m_cr;
   private $m_dbc;
   private $m_dbRb;
   
   public function __construct($dbc,$city,$cr) {
      $this->m_city = $city;
      $this->m_cr = $cr;
      $this->m_dbc = $dbc;
      $this->m_dbRb = new DbReportBuffer($this->m_dbc,$this->m_cr);
   }
   
   public function retrieve($cs) {
      $cs->addLine("p2v = Screen.reportLog.buffer");
   }
   
   public function add($rpt) {
      $this->m_dbRb->addAll($rpt);      
   }
   
   public function getLastReport($x,$y) {
      return $this->m_dbRb->getMostRecentReport($x,$y);
   }
   
   public function getUrlFromReport ($rpt) {
      $matches = array();
//http://battle197.evony.com/logfile/20161021/2a/0a/2a0a1e6b011473d3530a58d794ef66f9.xml
      if (preg_match('/^.*(http:\/\/battle.*xml).*$/', $rpt, $matches)) {
         return $matches[1];
      }  
      return NULL;      
   }
   
   public function getLastUpdate() {
      return $this->m_dbRb->getTimeOfLastUpdate();
   }
   

}

?>