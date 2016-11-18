<?php



class DbTimer {
  var $m_server;
  var $m_player;
  var $m_db;
  var $m_id;
  var $m_type;
  
  public function __construct($db, $server, $player, $type) {
    $this->m_server = $server;
    $this->m_player = $player;
    $this->m_db     = $db;
    $this->m_type   = $type;
    $this->m_id     = -1;
  }
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT _id FROM timer WHERE server=" . 
            $this->m_server . " AND player LIKE '" . $this->m_player . "' AND " .
            "timertype = " . $this->m_type);
         
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $this->m_id = $row["_id"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $this->m_id;
  }
  
  public function hasTimer() {
     return ($this->getId() != -1);
  }
  
  public function createTimer() {
     if (!$this->hasTimer()) {
         $this->m_db->query("INSERT INTO timer (server, player, timertype) VALUES ('" . 
            $this->m_server . "', '" . $this->m_player . "', '" . $this->m_type . "')");
    }
  }
  
  public function getTimerVal() {
     $val = 0;
     if ($this->hasTimer()) {
        $result = $this->m_db->query("SELECT timerval FROM timer WHERE _id = " . 
              $this->getId());
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["timerval"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  
  public function setTimerVal($val) {
     $result = $this->m_db->query("UPDATE timer SET timerval='" . $val . "' WHERE _id=" . $this->getId());
  }
  
  public function deleteTimer() {
     if ($this->hasTimer()) {
        $result = $this->m_db->query("DELETE FROM timer WHERE _id=" . $this->getId());
        $this->m_id = -1;
     }
  }
  
  

}

?>
   