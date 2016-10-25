<?php

class DbReportBuffer {
   
  var $m_db;
  var $m_cr;
  
  var $m_id;
  var $m_err;
  
  public function __construct($db,$cr) {
	  $this->m_db = $db;
     $this->m_cr = $cr;
  }
  
  public function addAll($rb) {
     $this->deleteAll();
     if (strpos($rb,PHP_EOL) !== false) {
        $rptLines = explode(PHP_EOL, $rb);
     } else {
        $rptLines = explode("\n",$rb);
     }
     foreach ($rptLines as $line) {
        $this->addLine($line);
     }
  }
  
  public function addLine($line) {
     $result = $this->m_db->query("INSERT INTO reportbuffer (report,time,player) VALUES ('" . 
         $this->m_db->real_escape_string($line) . "', '" . $this->m_cr->getCtime() . "', '" . $this->m_cr->getUser() . "')"); 
     if (!$result) {
        printf("Error: " . $this->m_db->error);
     }
  }
  
  public function getNumReports() {
     $num = 0;
	  $result = $this->m_db->query("SELECT COUNT(*) as cnt FROM reportbuffer WHERE player LIKE '%" . $this->m_cr->getUser() . "%'");
         
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        $num = $row["cnt"];
     }
     return $num;
  }
  
   // Gets the first report
   public function getReport($x,$y) {
	  $result = $this->m_db->query("SELECT id,report,time FROM reportbuffer WHERE report LIKE '%Scout Report%".$x.",".$y."%' AND player LIKE '%" . $this->m_cr->getUser() . "%'");
         
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row["report"];
     }
     return NULL;
   }
   
   public function getMostRecentReport($x,$y) {
	  $result = $this->m_db->query("SELECT id,report,time FROM reportbuffer WHERE report LIKE '%Scout Report%".$x.",".$y."%' AND player LIKE '%" . $this->m_cr->getUser() . "%' ORDER BY id DESC");
         
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row["report"];
     }
     return NULL;
   }
   
   public function getTimeOfLastUpdate() {
	  $result = $this->m_db->query("SELECT id,report,time FROM reportbuffer WHERE player LIKE '%" . $this->m_cr->getUser() . "%' ORDER BY id DESC");
         
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row["time"];
     }
     return NULL;
   }
  
  
  public function reset() {
     $this->deleteAll();
  }
  
  protected function deleteAll() {
     $result = $this->m_db->query("DELETE FROM reportbuffer WHERE player like '%" . $this->m_cr->getUser() . "%' ");
     if (!$result) {
        printf("Error: " . $this->m_db->error);
     }
  }
  
  public function getLastReport() {
	  $result = $this->m_db->query("SELECT id,report,time FROM reportbuffer WHERE player LIKE '%" . $this->m_cr->getUser() . "%' ORDER BY id DESC LIMIT 1");
         
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row["report"];
     }
     return NULL;
  }
  
  
  
}

?>
   