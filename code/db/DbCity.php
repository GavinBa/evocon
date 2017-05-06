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

  public function cityNameExistsExact($name) {
     $exists = false;
     $result = $this->m_db->query("SELECT id FROM city WHERE server=" .
         $this->m_server . " AND player LIKE '%" . $this->m_player .
         "%' AND name = '" . $name . "'");
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

  public function getName() {
     $name = "";
     $result = $this->m_db->query("SELECT name FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row["name"];
     }
     if ($result) {
        $result->free();
     }
     return $name;
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
  
  public function getDevelopment() {
     $dev = 0;
     $result = $this->m_db->query("SELECT development FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dev = $row["development"];
     }
     if ($result) {
        $result->free();
     }
     return $dev;
  }
  
  public function setDevelopment($dev) {
     $result = $this->m_db->query("UPDATE city SET development='" . $dev . "' WHERE id=" . $this->getId());
  }

  public function getDevStage() {
     $stage = 0;
     $result = $this->m_db->query("SELECT devstage FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stage = $row["devstage"];
     }
     if ($result) {
        $result->free();
     }
     return $stage;
  }
  
  public function setDevStage($stage) {
     $result = $this->m_db->query("UPDATE city SET devstage='" . $stage . "' WHERE id=" . $this->getId());
  }
  
  public function getGold() {
     $res = 0;
     $result = $this->m_db->query("SELECT gold FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $res = $row["gold"];
     }
     if ($result) {
        $result->free();
     }
     return $res;
  }
  
  public function setGold($gold) {
     $result = $this->m_db->query("UPDATE city SET gold='" . $gold . "' WHERE id=" . $this->getId());
  }

  public function getFood() {
     $res = 0;
     $result = $this->m_db->query("SELECT food FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $res = $row["food"];
     }
     if ($result) {
        $result->free();
     }
     return $res;
  }
  
  public function setFood($food) {
     $result = $this->m_db->query("UPDATE city SET food='" . $food . "' WHERE id=" . $this->getId());
  }

  public function getWood() {
     $res = 0;
     $result = $this->m_db->query("SELECT wood FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $res = $row["wood"];
     }
     if ($result) {
        $result->free();
     }
     return $res;
  }
  
  public function setWood($wood) {
     $result = $this->m_db->query("UPDATE city SET wood='" . $wood . "' WHERE id=" . $this->getId());
  }

  public function getStone() {
     $res = 0;
     $result = $this->m_db->query("SELECT stone FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $res = $row["stone"];
     }
     if ($result) {
        $result->free();
     }
     return $res;
  }
  
  public function setStone($stone) {
     $result = $this->m_db->query("UPDATE city SET stone='" . $stone . "' WHERE id=" . $this->getId());
  }

  public function getIron() {
     $res = 0;
     $result = $this->m_db->query("SELECT iron FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $res = $row["iron"];
     }
     if ($result) {
        $result->free();
     }
     return $res;
  }
  
  public function setIron($iron) {
     $result = $this->m_db->query("UPDATE city SET iron='" . $iron . "' WHERE id=" . $this->getId());
  }

  public function getCastleIdx() {
     $idx = 0;
     $result = $this->m_db->query("SELECT castle_idx FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $idx = $row["castle_idx"];
     }
     if ($result) {
        $result->free();
     }
     return $idx;
  }
  
  public function setCastleIdx($idx) {
     $result = $this->m_db->query("UPDATE city SET castle_idx='" . $idx . "' WHERE id=" . $this->getId());
  }
  
  public function getFarmIdx() {
     $idx = 0;
     $result = $this->m_db->query("SELECT farm_idx FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $idx = $row["farm_idx"];
     }
     if ($result) {
        $result->free();
     }
     return $idx;
  }

  public function getRunManualScript() {
     $rms = false;
     $result = $this->m_db->query("SELECT runManualScript FROM city WHERE id=" . $this->getId());
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rms = ($row["runManualScript"] > 0);
     }
     if ($result) {
        $result->free();
     }
     return $rms;
     
  }
  
  public function setRunManualScript($v) {
     $result = $this->m_db->query("UPDATE city SET runManualScript='" . $v . "' WHERE id=" . $this->getId());
  }
  
}

?>
   