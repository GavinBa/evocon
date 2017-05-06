<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/cities/Development.php";
require_once "code/cities/DevelopmentMonitor.php";
require_once "code/db/DbAlts.php";
require_once "code/db/DbCity.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";

require_once "lib/db.php";


class DevelopmentMonitorTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   
   protected function setUp() {
      $this->tcJson = json_decode(Defaults::$defaultCityJson);
      $this->tc = new City($this->tcJson);
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->cs->startFile();
      $this->dbCity = new DbCity($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      if ($this->dbCity->cityNameExists("A")) {
         $this->tc->setNameTestOnly("A");
         $this->dbCity = new DbCity($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
         $this->dbCity->updateName("W");
         $this->tc->setNameTestOnly("W");
         $this->dbCity = new DbCity($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      }
      $this->assertSame("W", $this->tc->getName());
   }
   
	public function testConstructor() {
       $this->assertNotNull($this->dbc);
       $this->assertNotNull($this->dbCity);
       $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
       printf("dev: %s\n", $this->dbCity->getDevelopment());
       $this->assertFalse($dm->isMonitoring());
       foreach($this->tcJson->buildings as $myBuilding) {
          if ($myBuilding->name == "Town Hall") {
             $myBuilding->level = 1;
             break;
          }
       }
      $this->tc = new City($this->tcJson);
      $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
      $this->assertTrue($dm->isMonitoring());
      $this->assertTrue(Development::isHatchling($this->dbCity->getDevelopment()));

      foreach($this->tcJson->buildings as $myBuilding) {
         if ($myBuilding->name == "Town Hall") {
            $myBuilding->level = 4;
            break;
         }
      }
      $this->tc = new City($this->tcJson);
      $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
      $this->assertTrue($dm->isMonitoring());
      $this->assertTrue(Development::isNestling($this->dbCity->getDevelopment()));

      foreach($this->tcJson->buildings as $myBuilding) {
         if ($myBuilding->name == "Town Hall") {
            $myBuilding->level = 5;
            break;
         }
      }
      $this->tc = new City($this->tcJson);
      $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
      $this->assertTrue($dm->isMonitoring());
      printf("%s\n",$this->dbCity->getDevelopment());
      $this->assertTrue(Development::isFledgling($this->dbCity->getDevelopment()));
      
	}
   
   public function testProcessHatchling() {
      foreach($this->tcJson->buildings as $myBuilding) {
         if ($myBuilding->name == "Town Hall") {
            $myBuilding->level = 1;
            break;
         }
      }
       $this->assertNotNull($this->dbc);
       $this->assertNotNull($this->dbCity);
       $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
       $this->assertTrue($dm->isMonitoring());
       $this->assertTrue(Development::isHatchling($this->dbCity->getDevelopment()));
       $this->dbCity->setDevStage(0);
       $this->assertEquals(0,$this->dbCity->getDevStage());
       $dm->process($this->cs);
   }
   
   public function testProcessNestling() {
      foreach($this->tcJson->buildings as $myBuilding) {
         if ($myBuilding->name == "Town Hall") {
            $myBuilding->level = 4;
            break;
         }
      }
       $this->assertNotNull($this->dbc);
       $this->assertNotNull($this->dbCity);
       $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
       $this->assertTrue($dm->isMonitoring());
       $this->assertTrue(Development::isNestling($this->dbCity->getDevelopment()));
       $this->dbCity->setDevStage(1);
       $this->assertEquals(1,$this->dbCity->getDevStage());
       
       $dm->process($this->cs);
       $this->assertEquals(2,$this->dbCity->getDevStage());
       
       $dm->process($this->cs);
       $this->assertEquals(3,$this->dbCity->getDevStage());
       $this->assertSame("A", $this->dbCity->getName());
       $this->tc->setNameTestOnly("A");
       $this->dbCity = new DbCity($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
       $this->dbCity->updateName("W");
       $this->tc->setNameTestOnly("W");
       $this->dbCity = new DbCity($this->dbc,Defaults::$server,Defaults::$player,$this->tc);

       $dm->process($this->cs);
       $this->assertEquals(4,$this->dbCity->getDevStage());
       
       $dm->process($this->cs);
       $this->assertEquals(5,$this->dbCity->getDevStage());

       $dm->process($this->cs);
       $this->assertEquals(6,$this->dbCity->getDevStage());
       $dba = new DbAlts($this->dbc, Defaults::$server,Defaults::$player,$this->tc);
       $this->assertTrue($dba->hasApplied());
       $dba->setApplied(0);

       $dm->process($this->cs);
       $this->assertEquals(7,$this->dbCity->getDevStage());

   }   
   
   public function testProcessFledging() {
      foreach($this->tcJson->buildings as $myBuilding) {
         if ($myBuilding->name == "Town Hall") {
            $myBuilding->level = 5;
            break;
         }
      }
       $this->assertNotNull($this->dbc);
       $this->assertNotNull($this->dbCity);
       $dm = new DevelopmentMonitor($this->tc, $this->cr, $this->dbCity);
       $this->assertTrue($dm->isMonitoring());
       $this->assertFalse(Development::isNestling($this->dbCity->getDevelopment()));
       $this->assertTrue(Development::isFledgling($this->dbCity->getDevelopment()));
       $this->dbCity->setDevStage(1);
       $this->assertEquals(1,$this->dbCity->getDevStage());
   }
   
   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
      }
      if ($this->cs->isOpen()) {
         $this->cs->endFile();
      }
      $this->cs->dumpFileToStdout();
      $this->cs->purge();
   }
}
?>