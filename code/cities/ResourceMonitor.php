<?php

require_once "code/buildings/buildings.php";
require_once "code/cities/city.php";
require_once "code/db/DbAlts.php";
require_once "code/db/DbCastle.php";
require_once "code/db/DbGoals.php";
require_once "code/timers/Timer.php";
require_once "code/timers/TimerType.php";

class ResourceMonitor {
	
  var $m_city;
  var $m_cr;
  var $m_cs;
  var $m_dbalt;
  
  public function __construct($city, $cr) {
	$this->m_city      = $city;
   $this->m_cr        = $cr;
   $this->m_dbalt     = new DbAlts($this->m_cr->getDbconnect(),
         $this->m_cr->getServer(), $this->m_cr->getUser(), $this->m_city);
  }
  
  public function process($cs) {
     $this->m_cs = $cs;
     $this->m_cr->getDbc()->setGold($this->m_city->getGoldAmt());
     $this->m_cr->getDbc()->setFood($this->m_city->getFoodAmt());
     $this->m_cr->getDbc()->setWood($this->m_city->getWoodAmt());
     $this->m_cr->getDbc()->setStone($this->m_city->getStoneAmt());
     $this->m_cr->getDbc()->setIron($this->m_city->getIronAmt());
     $this->setDump($cs);
     $this->sendDumpHit($cs);
  }
  
  protected function setDump($cs) {
     // if isAnAlt then
     if ($this->m_dbalt->playerExists()) {
        $timer = new Timer($this->m_cr->getDbconnect(),
              $this->m_cr->getServer(), $this->m_cr->getUser(), TimerType::SETDUMP);
              
        // if time to set dump then
        if ($this->m_dbalt->isDumpSet() && $timer->hasExpired($this->m_cr->getCtime())) {
           $this->addKeepResources($cs,"g",$this->m_dbalt->getMaxGold());
           $this->addKeepResources($cs,"f",$this->m_dbalt->getMaxFood());
           $this->addKeepResources($cs,"w",$this->m_dbalt->getMaxWood());
           $this->addKeepResources($cs,"s",$this->m_dbalt->getMaxStone());
           $this->addKeepResources($cs,"i",$this->m_dbalt->getMaxIron());
           $timer->setExpiration($this->m_cr->getCtime(),(1000*60*60));
        }
     }
  }
  
  protected function addKeepResources($cs,$res,$max) {
     if ($max > 0) {
        $x = $this->m_dbalt->getDumpX();
        $y = $this->m_dbalt->getDumpY();
        //keepresource x,y f:max 100k
        $cs->addLine("keepresources " . $x . "," . $y . " " . $res . ":" . $max . " 50k cavalry");
        //$db, $server, $player, $cityname
        $goals = new DbGoals($this->m_cr->getDbconnect(),$this->m_cr->getServer(), 
              $this->m_cr->getUser(), $this->m_city->getName());
        $goals->purgeGoal("keepresources " . $x . "," . $y . " " . $res);
        $goals->insertGoal("keepresources " . $x . "," . $y . " " . $res . ":" . $max . " 50k cavalry");
     }
  }
  
  protected function sendDumpHit($cs) {
     $farmIdx = $this->m_cr->getDbc()->getFarmIdx();
     if ($farmIdx > 0) {
        $farmCastle = DbCastle::fromExisting($this->m_cr->getDbconnect(),$farmIdx);
        if ($farmCastle != NULL) {
           $farmX = $farmCastle->getX();
           $farmY = $farmCastle->getY();
           $armies = $this->m_city->getSelfArmies();
           $attackFarm = true;
           foreach ($armies as $army) {
              $tgtCoords = $army->targetCoords;
              if ($tgtCoords != NULL) {
                 $x = explode(",",$tgtCoords)[0];
                 $y = explode(",",$tgtCoords)[1];
                 if ($x == $farmX && $y == $farmY) {
                    $attackFarm = false;
                    break;
                 }
              }
           }
           if ($attackFarm) {
              $cs->addLine("attack ".$farmX.",".$farmY." any a:23k,w:1,p:1,sw:1,t:10k");
           }
        }
     }
  }
  

}