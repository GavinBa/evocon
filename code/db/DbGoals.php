<?php


class DbGoals {
  var $m_server;
  var $m_player;
  var $m_cityname;
  var $m_db;
  var $m_id;
  
  public function __construct($db, $server, $player, $cityname) {
	$this->m_cityname   = $cityname;
	$this->m_server = $server;
	$this->m_player = $player;
	$this->m_db = $db;
   $this->m_id = -1;
  }
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT _id FROM goals WHERE server=" . 
            $this->m_server . " AND user LIKE '" . $this->m_player . "' AND city='".$this->m_cityname."'");
         
        if ($result && $result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $this->m_id = $row["_id"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $this->m_id;
  }
  
  public function purgeGoal($goal) {
     $result = $this->m_db->query("DELETE FROM goals WHERE server=" .
           $this->m_server . " AND user='" . $this->m_player . "' AND city='".
           $this->m_cityname . "' AND goal LIKE '%" . $goal . "%'");
  }
  
  public function insertGoal($goal) {
     $result = $this->m_db->query("INSERT INTO goals (server,user,city,goal) VALUES('".
           $this->m_server."', '".$this->m_player."', '".$this->m_cityname."', '".$goal."')");     
  }
  
  public function replaceGoal($goal) {
     $this->purgeGoal($goal);
     $this->insertGoal($goal);
  }
  
  public function getGoals() {
     $goals = array();
     $idx = 0;
     $result = $this->m_db->query("SELECT goal FROM goals WHERE server=" . 
            $this->m_server . " AND user='" . $this->m_player . "' AND city='".$this->m_cityname."'");
     if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
           $goals[$idx++] = $row["goal"];
        }
     }
     if ($result) {
        $result->free();
     }
     return $goals;
  }
  
  
}

?>
   