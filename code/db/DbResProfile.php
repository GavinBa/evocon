<?php



class DbResProfile {
  var $m_server;
  var $m_player;
  var $m_city;
  var $m_db;
  var $m_id;
  
   public function __construct($db, $rpi) {
      $this->m_db = $db;
      $this->m_id = $rpi;
   }
  
  
   public function getId()            { return $this->m_id; }  
   public function getBuyAmt()        { return $this->getValue("buyamt"); }
   public function getMaxWoodAmt()    { return $this->getValue("maxwoodamt"); }
   public function getMaxStoneAmt()   { return $this->getValue("maxstoneamt"); }
   public function getMaxIronamt()    { return $this->getValue("maxironamt"); }
   public function getMaxGoldAmt()    { return $this->getValue("maxgoldamt"); } 
   public function getMaxFoodAmt()    { return $this->getValue("maxfoodamt"); }
   public function getName()          { return $this->getValue("name"); }
   public function getAvgSpan()       { return $this->getValue("avgspan"); }
  
   protected function getValue($column) {
     $v = 0;
     $result = $this->m_db->query("SELECT " . $column . " FROM res_profiles WHERE _id=" . $this->getId());
     if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $v = $row[$column];
     }
     if ($result) {
        $result->free();
     }
	 return $v;
   }
  
}

?>
   