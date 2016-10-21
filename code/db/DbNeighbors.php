<?php


class DbNeighbors {
  var $m_server;
  var $m_db;
  var $m_id;
  var $m_city;
  var $m_player;
  var $m_castleid;
  
  public function __construct($db, $server, $player, $city, $castleid) {
	$this->m_server = $server;
	$this->m_player = $player;
	$this->m_db = $db;
	$this->m_city = $city;
	$this->m_castleid = $castleid;
    $this->m_id = -1;
  }
  
  public function create() {
	  /* Query on server, player, city name */
	  $result = $this->m_db->query("SELECT _id FROM neighbors where server=" . 
			$this->m_server . " AND player LIKE '%" . $this->m_player . 
         "%' AND castleid=" . $this->m_castleid . " AND mycity LIKE '%" . 
         $this->m_city->getName() . "%'");

         /* If not found then insert */
	  if (! $result || $result->num_rows == 0) {
        $this->m_db->query("INSERT INTO neighbors (server, player, mycity, castleid) VALUES ('" . 
            $this->m_server . "', '" . $this->m_player . "', '" . $this->m_city->getName() . 
            "' , '" . $this->m_castleid  . "')");
     }
     
     if ($result) {
        $result->free();
     }
  }
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT _id FROM neighbors where server=" . 
			     $this->m_server . " AND player LIKE '%" . $this->m_player . 
              "%' AND castleid=" . $this->m_castleid . " AND mycity LIKE '%" . 
              $this->m_city->getName() . "%'");
         
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

  public function setDistance($d) {
     $result = $this->m_db->query("UPDATE neighbors SET d=" . $d . " WHERE _id=", $this->getId());
  }
  
  public function getDistance () {
     $dist = 0;
     $result = $this->m_db->query("SELECT distance FROM neighbors where _id=".$this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dist = $row["d"];
     }
     if ($result) {
        $result->free();
     }
     return $dist;
  }

  public function setLastCheck($lastCheck) {
     $result = $this->m_db->query("UPDATE neighbors SET lastCheck='" . $lastCheck . "' WHERE _id=".$this->getId());
  }
  
  public function getLastCheck () {
     $lastCheck = 0;
     $result = $this->m_db->query("SELECT lastCheck FROM neighbors WHERE _id=".$this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastCheck = $row["lastCheck"];
     }
     if ($result) {
        $result->free();
     }
     return $lastCheck;
  }
  
  public function setLastPres($lastPres) {
     $result = $this->m_db->query("UPDATE neighbors SET lastPres='" . $lastPres . "' WHERE _id=".$this->getId());
  }
  
  public function getLastPres () {
     $lastPres = 0;
     $result = $this->m_db->query("SELECT lastPres FROM neighbors WHERE _id=".$this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastPres = $row["lastPres"];
     }
     if ($result) {
        $result->free();
     }
     return $lastPres;
  }
  
}

?>
   