<?php

require_once "states.php";


class DbAlts {
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
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT _id FROM alts WHERE server=" . 
            $this->m_server . " AND player LIKE '" . $this->m_player . "'");
         
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
  
  public function playerExists() {
     $exists = false;
     $result = $this->m_db->query("SELECT _id FROM alts WHERE server=" .
         $this->m_server . " AND player LIKE '%" . $this->m_player .
         "%'");
     if ($result->num_rows > 0) {
        $exists = true;
     }
     if ($result) {
        $result->free();
     }
     return $exists;
  }
  
  public function getHost() {
     $host = NULL;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT host FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $host = $row["host"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $host;
  }
  
  public function isHost() {
     $ishost = false;
     $result = $this->m_db->query("SELECT _id FROM alts WHERE host='" . $this->m_player . "'");
     if ($result && $result->num_rows > 0) {
        $ishost = true;
     }
     if ($result) {
        $result->free();
     }
     return $ishost;
  }
  
  public function getAlliance() {
     $alliance = NULL;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT alliance FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $alliance = $row["alliance"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $alliance;
  }
  
  public function hasApplied() {
     $applied = false;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT applied FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $i = $row["applied"];
           $applied = ($i > 0);
        }
        if ($result) {
           $result->free();
        }
     }
     return $applied;
  }
  
  public function setApplied($applied) {
     $result = $this->m_db->query("UPDATE alts SET applied='" . $applied . "' WHERE _id=" . $this->getId());
  }

  public function setHostTestOnly($host) {
     $result = $this->m_db->query("UPDATE alts SET host='" . $host . "' WHERE _id=" . $this->getId());
  }
  
  public function isDumpSet() {
     return ($this->getDumpX() != 0 && $this->getDumpY() != 0);
  }
  
  public function getDumpX() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT dumpx FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["dumpx"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  public function getDumpY() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT dumpy FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["dumpy"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  public function getMaxGold() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT maxgold FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["maxgold"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  public function getMaxFood() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT maxfood FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["maxfood"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  public function getMaxWood() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT maxwood FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["maxwood"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  public function getMaxStone() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT maxstone FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["maxstone"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  public function getMaxIron() {
     $val = 0;
     if ($this->playerExists()) {
        $result = $this->m_db->query("SELECT maxiron FROM alts WHERE server=" .
            $this->m_server . " AND player LIKE '%" . $this->m_player .
            "%'");
        if ($result->num_rows > 0) {
           $row = $result->fetch_assoc();
           $val = $row["maxiron"];
        }
        if ($result) {
           $result->free();
        }
     }
     return $val;
  }
  
  public function updateColTestOnly($col,$val) {
     $result = $this->m_db->query("UPDATE alts SET " . $col . "='" . $val . "' WHERE _id=" . $this->getId());
  }
  
}

?>
   