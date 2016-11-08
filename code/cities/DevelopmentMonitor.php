<?php

require_once "code/buildings/buildings.php";
require_once "code/cities/city.php";
require_once "code/cities/Development.php";
require_once "code/db/DbAlts.php";

class DevelopmentMonitor extends City {
	
  var $m_city;
  var $m_cr;
  var $m_dbc;
  var $m_buildings;
  var $m_cs;
  
  public function __construct($city, $cr, $dbc) {
	$this->m_city      = $city;
   $this->m_cr        = $cr;
   $this->m_dbc       = $dbc;
   $this->m_buildings = new Buildings($city);
   $this->evaluate();
  }
  
  public function isMonitoring() {
     return (Development::isUnderDevelopment($this->m_dbc->getDevelopment()));
  }
  
  public function process($cs) {
     $this->m_cs = $cs;
     
     if ($this->m_dbc->getDevelopment() == Development::HATCHLING) {
        $this->processHatchling();
     } else if ($this->m_dbc->getDevelopment() == Development::NESTLING) {
        $this->processNestling();
     } else if ($this->m_dbc->getDevelopment() == Development::FLEDGLING) {
        $this->processFledgling();
     }
  }
  
  protected function evaluate() {
     if ($this->m_buildings->getTownHallLevel() < 3) {
        $this->m_dbc->setDevelopment(Development::HATCHLING);
     } else if ($this->m_buildings->getTownHallLevel() < 5) {
        $this->m_dbc->setDevelopment(Development::NESTLING);
     } else if ($this->m_buildings->getTownHallLevel() <= 6) {
        $this->m_dbc->setDevelopment(Development::FLEDGLING);
     } else {
        $this->m_dbc->setDevelopment(Development::GROWN);
     }
  }
  
  protected function processHatchling() {
     $stage = $this->m_dbc->getDevStage();
     switch ($stage) {

        case 0:
           $this->m_cs->injectScript("client/goals/DevHatchlingStage01.txt");
           $this->m_dbc->setDevStage(1);
           break;
           
        case 1:
           // wait for town hall to be 3
           if ($this->m_buildings->getBuildingLevel("Town Hall") == 3) {
              $this->m_cs->addLine('completequests');
              $this->m_cs->addLine('useitem player.box.present.4');
              $this->m_dbc->setDevStage(2);
           } else {
              $this->m_cs->addEcho("waiting on townhall - currently at " . $this->m_buildings->getBuildingLevel("Town Hall"));
           }
           break;
           
           
           
        default:
           $this->m_cs->addEcho("Waiting on further stages - at stage " . $stage);
           break;
     }
  }
  
  protected function processFledgling() {
     $stage = $this->m_dbc->getDevStage();
     switch ($stage) {
        case 10:
           // just transitioned to fledgling
           $this->m_cs->addLine('completequests routine');
           $this->m_dbc->setDevStage(11);
           break;
           
        case 11:
           $this->m_cs->addEcho('Stage 11 - Upgrade barracks to 9 - and research');
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage08.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           break;
           
        default:
           break;
     }
  }
  
  protected function processNestling() {
     $stage = $this->m_dbc->getDevStage();
     switch ($stage) {
        case 1:
           // just transitioned to nestling - keep moving
           $this->m_cs->addLine('completequests routine');
           $this->m_cs->addLine('useitem player.box.present.4');
           $this->m_cs->addLine('completequests routine');
           $this->m_dbc->setDevStage(2);
           break;
              
        case 2:
           $a = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"];
           foreach ($a as $name) {
              if ($this->m_dbc->cityNameExistsExact($name) == false) {
                 $this->m_cs->addLine('renamecity ' . $name);
                 $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage02.txt" {time: date().time }');
                 $this->m_cr->getDbc()->updateName($name);
                 $this->m_dbc->setDevStage(3);
                 break;
              }
           }
           break;
           
        case 3:
           // make sure we have good starting goals...
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage02.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           $this->m_cs->addLine("completequests routine");
           $this->m_dbc->setResProfile(1);
           $this->m_dbc->setDevStage(4);
           break;
           
        case 4:
           $this->m_cs->addEcho("Nestling - Stage 4");
           $this->m_cs->addLine("completequests routine");
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage03.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           if ($this->m_buildings->getMinBuildingLevel("Barracks") >= 2) {
              $this->m_dbc->setDevStage(5);
           }
           break;
           
        case 5:
           // at this point see if we can join alliance
           $dba = new DbAlts($this->m_cr->getDbconnect(), $this->m_cr->getServer(), $this->m_cr->getUser(), $this->m_city);
           if ($dba->playerExists() && !$dba->hasApplied()) {
              $alliance = $dba->getAlliance();
              // apply
              $this->m_cs->addLine("command \"apply " . $alliance . "\"");
              $dba->setApplied(1);
           }
           $this->m_cs->addLine("completequests routine");
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage04.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           if ($this->m_buildings->getMinBuildingLevel("Beacon Tower") >= 3) {
              $this->m_dbc->setDevStage(6);
           }
           break;
           
        case 6:
           // chat in alliance
           $this->m_cs->addLine("alliancechat hello");
           $this->m_cs->addLine("completequests routine");
           $dba = new DbAlts($this->m_cr->getDbconnect(), $this->m_cr->getServer(), $this->m_cr->getUser(), $this->m_city);
           $this->m_cs->addLine("whisper " . $dba->getHost() . " I am here.");
           $this->m_dbc->setDevStage(7);
           break;
        
        case 7:
           $this->m_cs->addEcho('Stage 7 (exit => fo >= 4)');
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage05.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           if ($this->m_buildings->getMinBuildingLevel("Forge") >= 4) {
              $this->m_dbc->setDevStage(8);
           }
           $this->m_cs->addLine("hero = city.bestPoliticsHero()");
           $this->m_cs->addLine("if (ItemCount(\"hero.management.1\") == 2) execute \"useheroitem {hero.name} the wealth of nations\"");
           break;
           
        case 8:
           $this->m_cs->addEcho('Stage 8 (exit => m >= 3)');
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage05.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           if ($this->m_buildings->getMinBuildingLevel("Marketplace") >= 3) {
              $this->m_dbc->setDevStage(9);
           }
           break;
           
        case 9:
           $this->m_cs->addEcho('Stage 9 (exit => r >= 5)');
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage06.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           $this->m_cs->addLine("if (ItemCount(\"player.box.special.1\") > 0) execute \"useitem player.box.special.1\"");

           if ($this->m_buildings->getMinBuildingLevel("Rally Spot") >= 5) {
              $this->m_dbc->setDevStage(10);
           }
           break;

        case 10:
           $this->m_cs->addEcho('Stage 10 - Get res fields up');
           $this->m_cs->addLine('@get "http://192.168.1.77:8000/client/goals/DevGoalsStage07.txt" {time: date().time }');
           $this->m_cs->addLine('if $error == null goal $result');
           break;
           
        default:
           $this->m_cs->addEcho("Waiting on further stages - at stage " . $stage);
           break;
     }
     
  }

}