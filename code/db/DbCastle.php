<?php


class DbCastle {
  var $m_server;
  var $m_db;
  var $m_id;
  var $m_x;
  var $m_y;
  var $m_err;
  
  public function __construct($db, $server, $x, $y) {
	$this->m_server = $server;
	$this->m_db = $db;
   $this->m_x = $x;
   $this->m_y = $y;
   $this->m_id = -1;
   $this->m_err = "";
  }
  
  public static function fromExisting($db, $castleid) {
     $instance = new self($db,0,0,0);
     $result = $db->query("SELECT server,x,y FROM castle WHERE _id=" . $castleid);
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $instance->m_id = $castleid;
        $instance->m_server = $row["server"];
        $instance->m_x = $row["x"];
        $instance->m_y = $row["y"];
     }
     return $instance;
  }
  
  public function create() {
	  /* Query on server, player, city name */
	  $result = $this->m_db->query("SELECT _id FROM castle where server=" . 
			$this->m_server . " AND x=" . $this->m_x . " AND y=" . $this->m_y);

         /* If not found then insert */
	  if (! $result || $result->num_rows == 0) {
        $this->m_db->query("INSERT INTO castle (server, x, y) VALUES ('" . 
            $this->m_server . "', '" . $this->m_x . "', '" . $this->m_y . "')");
     }
     if ($result) {
        $result->free();
     }
  }
  
  public function getServer() { return $this->m_server; }
  public function getX() { return $this->m_x; }
  public function getY() { return $this->m_y; }
  public function getError() { return $this->m_err; }
  
  public function getId() {
     if ($this->m_id == -1) {
        $result = $this->m_db->query("SELECT _id FROM castle WHERE server=" . 
            $this->m_server . " AND x=" . $this->m_x . 
            " AND y=" . $this->m_y);
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

  public function setPrestige($pres) {
     $result = $this->m_db->query("UPDATE castle SET prestige=" . $pres . " WHERE _id=" . $this->getId());
  }  
  
  public function getPrestige () {
     $prestige = 0;
     $result = $this->m_db->query("SELECT prestige FROM castle WHERE server=" .
         $this->m_server . " AND x=" . $this->m_x .
         " AND y=" . $this->m_y);
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $prestige = $row["prestige"];
     }
     if ($result) {
        $result->free();
     }
     return $prestige;
  }
  
  
}

?>
   