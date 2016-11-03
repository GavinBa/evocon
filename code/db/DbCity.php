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
     if ($result) {
        $result->free();
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
        if ($result) {
           $result->free();
        }
     }
     return $this->m_id;
  }
  
  public function cityNameExists($name) {
     $exists = false;
     $result = $this->m_db->query("SELECT id FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '%" . $this->m_player .
         "%' AND name LIKE '%" . $name . "%'");
     if ($result->num_rows > 0) {
        $exists = true;
     }
     if ($result) {
        $result->free();
     }
     return $exists;
  }
  
  public function getTotalCityCount() {
     $cnt = 0;
     $result = $this->m_db->query("SELECT id FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '%" . $this->m_player .
         "%'");
     $cnt = $result->num_rows;
     if ($result) {
        $result->free();
     }
     return $cnt;
  }
  
  public function isLowestIdForPlayer() {
     $myid = $this->getId();
     return ($myid == $this->getLowestIdForPlayer());
  }
  
  public function getLowestIdForPlayer() {
     $lowid = -1;
     $result = $this->m_db->query("SELECT id FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '" . $this->m_player .
         "' ORDER BY id");
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lowid = $row["id"];
     }
     if ($result) {
        $result->free();
     }
     return $lowid;
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
     if ($result) {
        $result->free();
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
     if ($result) {
        $result->free();
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
     if ($result) {
        $result->free();
     }
     return $newcity;
  }
  
  // Check if any city is currently spawning a new city.
  public function isAnyCitySpawning() {
     $newcity = false;
     $result = $this->m_db->query("SELECT newcity FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '" . $this->m_player .
         "' AND newcity > 0");
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newcity = true;
     }
     if ($result) {
        $result->free();
     }
     return $newcity;
  }
  
  public function setNewCity ($newcity) {
     $result = $this->m_db->query("UPDATE city SET newcity=" . $newcity . " WHERE id=" . $this->getId());
  }
  
  public function getNewCityFieldId() {
     $fid = 0;
     $result = $this->m_db->query("SELECT newcityfid FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '" . $this->m_player .
         "' AND name LIKE '" . $this->m_city->getName() . "'");
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fid = $row["newcityfid"];
     }
     if ($result) {
        $result->free();
     }
     return $fid;
  }
  
  public function updateName($newname) {
     $result = $this->m_db->query("UPDATE city SET name='" . $newname . "' WHERE id=" . $this->getId());
  }
  
  public function setNewCityFieldId ($fid) {
     $result = $this->m_db->query("UPDATE city SET newcityfid=" . $fid . " WHERE id=" . $this->getId());
  }
  
  public function isUnderAttack() {
     $ua = false;
     $result = $this->m_db->query("SELECT underAttack FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ua = ($row["underAttack"] > 0);
     }
     if ($result) {
        $result->free();
     }
     return $ua;
  }
  
  public function setUnderAttack($ua) {
     $result = $this->m_db->query("UPDATE city SET underAttack='" . $ua . "' WHERE id=" . $this->getId());
  }
  
  public function getResProfile() {
     $rpi = 0;
     $result = $this->m_db->query("SELECT resprofile_idx FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rpi = ($row["resprofile_idx"] > 0);
     }
     if ($result) {
        $result->free();
     }
     return $rpi;
  }
  
  public function setResProfile($rpi) {
     $result = $this->m_db->query("UPDATE city SET resprofile_idx='" . $rpi . "' WHERE id=" . $this->getId());
  }
  
}

?>
   