<?php

define ("SCOUT_IDLE",          0);
define ("SCOUT_PENDING",       1);
define ("SCOUT_FOUND",         2);
define ("SCOUT_OVERDUE",       3);
define ("SCOUT_REPORT_READY",  4);
define ("SCOUT_NOTARGET",      5);

class DbScout {
   
  var $m_db;
  var $m_dbcity;
  var $m_castleid;
  var $m_x;
  var $m_y;
  var $m_state;

  var $m_id;
  var $m_err;
  
  public function __construct($db, $dbcity, $castleid, $x, $y) {
	$this->m_db = $db;
   $this->m_dbcity = $dbcity;
   $this->m_castleid = $castleid;
   $this->m_x = $x;
   $this->m_y = $y;
   $this->m_id = -1;
   $this->m_err = "";
   $this->m_state = SCOUT_IDLE;
   
   $this->create();
  }
  
  public static function fromExisting($db, $dbcity) {

	  $result = $db->query("SELECT _id,castleid,state,x,y FROM scout where cityid=" . 
			$dbcity->getId());
         
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $instance = new self($db,$dbcity,$row["castleid"],$row["x"],$row["y"]);
        $instance->m_state = $row["state"];
     } else {
        $instance = new self($db,$dbcity,0,-2,-3);
        $instance->setState(SCOUT_NOTARGET);
        $instance->m_state = $instance->getState();
     }

     if ($result) {
        $result->free();
     }
     return $instance;
     
  }
  
  public function create() {
	  /* Query on server, player, city name */
	  $result = $this->m_db->query("SELECT _id FROM scout where cityid=" . 
			$this->m_dbcity->getId());

         /* If not found then insert */
	  if (! $result || $result->num_rows == 0) {
        $this->m_db->query("INSERT INTO scout (cityid,state,castleid,x,y) VALUES ('" . 
            $this->m_dbcity->getId() . "', '" . 
            SCOUT_IDLE . "', '" .
            $this->m_castleid . "', '" .
            $this->m_x . "', '" . $this->m_y . "')");
     } else {
        $uresult = $this->m_db->query("UPDATE scout SET castleid=".$this->m_castleid.",x=".$this->m_x.",y=".$this->m_y." WHERE cityid=".$this->m_dbcity->getId());
        if (!$uresult) {
           printf("___ERROR UPdating: %s\n",$this->m_db->error);
        }
     }
     if ($result) {
        $result->free();
     }
  }
  
  public function delete() {
	  $result = $this->m_db->query("DELETE FROM scout WHERE cityid=" . $this->m_dbcity->getId());
     return $result;
  }
  
  public function isIdle() {
     return ($this->getState() == SCOUT_IDLE || $this->getState() == SCOUT_NOTARGET);
  }
  
  public function hasTarget() {
     return $this->getState() != SCOUT_NOTARGET;
  }
  
  public function getState() {
     $state = SCOUT_IDLE;
	  $result = $this->m_db->query("SELECT state FROM scout where cityid=" . 
			$this->m_dbcity->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $state = $row["state"];
     }
     if ($result) {
        $result->free();
     }
     return $state;
  }
  
  public function setState($newstate) {
     $result = $this->m_db->query("UPDATE scout SET state=" . $newstate . " WHERE _id=" . $this->getId());
  }
 
  public function getCastleId() {
     return $this->m_castleid;
  }
  
  public function getX() {
     return $this->m_x;
  }
  
  public function getY() {
     return $this->m_y;
  }
  
  public function getError() { return $this->m_err; }
  
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT _id FROM scout WHERE cityid=" . $this->m_dbcity->getId());
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

  public function setScoutTime($scoutTime) {
     $result = $this->m_db->query("UPDATE scout SET scoutTime='" . $scoutTime . "' WHERE _id=" . $this->getId());
  }  
  
  public function getScoutTime () {
     $scoutTime = 0;
     $result = $this->m_db->query("SELECT scoutTime FROM scout WHERE _id=" . $this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $scoutTime = $row["scoutTime"];
     }
     if ($result) {
        $result->free();
     }
     return $scoutTime;
  }
  
  public function setReportTime ($rt) {
     $result = $this->m_db->query("UPDATE scout SET reportTime='" . $rt . "' WHERE _id=" . $this->getId());
  }
  
  public function getReportTime() {
     $rptTime = 0;
     $result = $this->m_db->query("SELECT reportTime FROM scout WHERE _id=" . $this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rptTime = $row["reportTime"];
     }
     if ($result) {
        $result->free();
     }
     return $rptTime;
  }

  public function setAttackTime($attackTime) {
     $result = $this->m_db->query("UPDATE scout SET attackTime='" . $attackTime . "' WHERE _id=" . $this->getId());
  }  
  
  public function getAttackTime () {
     $attackTime = 0;
     $result = $this->m_db->query("SELECT attackTime FROM scout WHERE _id=" . $this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attackTime = $row["attackTime"];
     }
     if ($result) {
        $result->free();
     }
     return $attackTime;
  }
  
  
}

?>
   