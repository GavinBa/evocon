<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/cities/ResourceMonitor.php";
require_once "code/db/DbAlts.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "code/timers/Timer.php";
require_once "code/timers/TimerType.php";
require_once "lib/db.php";


class ResourceMonitorTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   protected $rm;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
      $this->cr->setCtime(5000000);
      $this->rm = new ResourceMonitor($this->tc,$this->cr);
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->rm);
	}
   
   public function testProcess() {
      $this->assertNotNull($this->rm);
      $this->assertNotNull($this->cs);
      $amt = (int) round($this->tc->getGoldAmt());
      printf("goldamt: %d\n", $amt);
      $this->rm->process($this->cs);
      $this->assertEquals($amt,$this->cr->getDbc()->getGold());
   }
   
   public function testSetDump() {
      $this->assertNotNull($this->rm);
      $dbalt = new DbAlts($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->assertTrue($dbalt->playerExists());
      // reset alt timer
      $timer = new Timer($this->cr->getDbconnect(),
                         $this->cr->getServer(), $this->cr->getUser(), 
                         TimerType::SETDUMP);
      $timer->setExpiration(0,0);
      $this->assertTrue($timer->hasExpired($this->cr->getCtime()));
      $dbalt->updateColTestOnly("dumpx",50);
      $dbalt->updateColTestOnly("dumpy",50);
      $this->rm->process($this->cs);
      $this->assertFalse($timer->hasExpired($this->cr->getCtime()));
      $this->rm->process($this->cs);
   }
   
   
   public function testDumpHit() {
      $farmIdx = $this->cr->getDbc()->getFarmIdx();
      $this->assertGreaterThan(0,$farmIdx);
      $farmCastle = DbCastle::fromExisting($this->cr->getDbconnect(),$farmIdx);
      $this->assertNotNull($farmCastle);
      $this->rm->process($this->cs);
      
      $farmX = $farmCastle->getX();
      $farmY = $farmCastle->getY();
      $cityJson = Defaults::$defaultCityJson;
      $cityJsonDecoded = json_decode($cityJson);
      $cityJsonDecoded->selfArmies[0]->targetCoords = "$farmX,$farmY";
      $this->tc = new City($cityJsonDecoded);
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->rm = new ResourceMonitor($this->tc,$this->cr);
      $this->rm->process($this->cs);
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