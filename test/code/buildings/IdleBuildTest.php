<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "StateProcessor.php";
require_once "code/buildings/IdleBuild.php";
require_once "code/cities/city.php";
require_once "code/db/DbCity.php";
require_once "code/db/DbReportBuffer.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class IdleBuildTest extends TestCase
{
   protected $cr;
   protected $cs;
   protected $tc;
   protected $dbc;
   protected $ib;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
   }
   
   public function testConstructor() {
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr);
      $this->assertNotNull($this->cs);
      $this->assertNotNull($this->dbc);
      $this->ib = new IdleBuild($this->tc);
      $this->assertNotNull($this->ib);
   }
   
   public function testProcessBuildingActive() {
      $myJson = json_decode(Defaults::$defaultCityJson);
      $myJson->castle->buildingQueuesArray = array( 1 => 0 );
      $this->ib = new IdleBuild(new City($myJson));
      $this->assertNotNull($this->ib);
      $ns = $this->ib->process($this->cs,STATE_IDLE);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(2,$lines);
   }

   public function testProcessBuildingInactive() {
      $myJson = json_decode(Defaults::$defaultCityJson);
      $myJson->castle->buildingQueuesArray = array();
      $this->ib = new IdleBuild(new City($myJson));
      $this->assertNotNull($this->ib);
      $ns = $this->ib->process($this->cs,STATE_IDLE);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(5,$lines);
   }
   
   
   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
      }
      if ($this->cs && $this->cs->isOpen()) {
         $this->cs->endFile();
         $this->cs->purge();
      }
   }
}

?>