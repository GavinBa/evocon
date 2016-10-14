<?php

require_once "states.php";


class DbCity {
  var $m_server;
  var $m_player;
  var $m_city;
  var $m_db;
  var $m_id;
  
  public function __construct($db, $server, $player, $city) {
	$this->m_city   = $city;
	$this->m_server = $server;
	$this->m_player = $player;
	$this->m_db = $db;
   $this->m_id = -1;
  }
  
  public function create() {
	  /* Query on server, player, city name */
	  $result = $this->m_db->query("SELECT name FROM city where server=" . 
			$this->m_server . " AND player LIKE '%" . $this->m_player . "%' AND name LIKE '%" . $this->m_city->getName() . "%'");

         /* If not found then insert */
	  if (! $result || $result->num_rows == 0) {
        $this->m_db->query("INSERT INTO city (server, player, name) VALUES ('" . 
            $this->m_server . "', '" . $this->m_player . "', '" . $this->m_city->getName() . "')");
     }
  }
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT id FROM city WHERE server=" . 
            $this->m_server . " AND player LIKE '" . $this->m_player . 
            "' AND name LIKE '" . $this->m_city->getName() . "'");
         
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $this->m_id = $row["id"];
        }
     }
     return $this->m_id;
  }
  
  public function setState ($state) {
     $result = $this->m_db->query("UPDATE city SET state=" . $state . " WHERE id=" . $this->getId());
  }
  
  public function getState () {
     $state = STATE_IDLE;
     $result = $this->m_db->query("SELECT state FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '%" . $this->m_player .
         "%' AND name LIKE '%" . $this->m_city->getName() . "%'");
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $state = $row["state"];
     }
     return $state;
  }
  
  public function getProcessSlice () {
     $pslice = SLICE_IDLE;
     $result = $this->m_db->query("SELECT pslice FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '" . $this->m_player .
         "' AND name LIKE '" . $this->m_city->getName() . "'");
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pslice = $row["pslice"];
     }
     return $pslice;
  }
  
  public function setProcessSlice ($pslice) {
     $result = $this->m_db->query("UPDATE city SET pslice=" . $pslice . " WHERE id=" . $this->getId());
  }
  
  public function getNewCity() {
     $newcity = 0;
     $result = $this->m_db->query("SELECT newcity FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '" . $this->m_player .
         "' AND name LIKE '" . $this->m_city->getName() . "'");
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newcity = $row["newcity"];
     }
     return $newcity;
  }
  
  public function setNewCity ($newcity) {
     $result = $this->m_db->query("UPDATE city SET newcity=" . $newcity . " WHERE id=" . $this->getId());
  }
  
}

?>
   