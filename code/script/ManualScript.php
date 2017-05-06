<?php

require_once "code/db/DbCity.php";

class ManualScript {

  var $m_server;
  var $m_player;
  var $m_city;
  var $m_db;
  var $m_dbc;
  var $m_id;

   public function __construct($db, $server, $player, $city) {
      $this->m_city   = $city;
      $this->m_server = $server;
      $this->m_player = $player;
      $this->m_db = $db;
      $this->m_dbc = new DbCity($db,$server,$player,$city);
   }
   
   public function checkForManual($cs) {
      if ($this->m_dbc->getRunManualScript()) {
         $this->m_dbc->setRunManualScript(0);
         $path = "data/scripts" . "/" . $this->m_server . "/" . $this->m_player;
         $path = $path . "/" . "mscript.txt";
         
         if (! file_exists($path)) {
            printf ("%s does not exist\n", $path);
            return;
         }
         
         $cs->injectScript($path);
         
      }
   }
   
   
  
}

