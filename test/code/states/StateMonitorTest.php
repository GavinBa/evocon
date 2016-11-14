<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "code/cities/city.php";
require_once "code/db/DbCity.php";
require_once "code/db/DbReportBuffer.php";
require_once "code/request/ClientRequest.php";
require_once "code/states/StateMonitor.php";
require_once "code/script/ClientScript.php";
require_once "code/reports/ReportBuffer.php";

require_once "lib/db.php";


class StateMonitorTest extends TestCase
{
   protected $cr;
   protected $tc;
   protected $dbc;
   protected $cs;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->cs->startFile();
      printf("length of PHP_EOL: %s\n", strlen(PHP_EOL));
   }
   
	public function testConstructor() {
      printf("name=%s\n", $this->tc->getName());
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr);
      $this->assertNotNull($this->cs);
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
	}
   
   public function testProcessBaseState() {
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
      $ns = $sm->process($this->cs,STATE_MONITOR);
      $this->assertEquals($ns,STATE_MONITOR_FIELDS);
   }
   
   public function testProcessMonitorFields() {
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
      $ns = $sm->process($this->cs,STATE_MONITOR_FIELDS);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(27,$lines);
      $this->assertEquals($ns,STATE_MONITOR_FIELDS_RESULTS);
   }
   
   public function testProcessMonitorFieldsResults() {
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
      $_POST["p2"] = Defaults::$fields;
      $ns = $sm->process($this->cs,STATE_MONITOR_FIELDS_RESULTS);
      $this->assertEquals($ns,STATE_MONITOR_REPORT_BUFFER);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $numAttacks = 0;
      foreach ($lines as $line) {
         if (strpos($line,"rallySpot") !== false) {
            $numAttacks++;
         }
      }
      printf("Found %d attacks\n", $numAttacks);
      $this->assertGreaterThan(0,$numAttacks);
      $this->assertCount(25,$lines);
   }
   
   public function testProcessMonitorReportBuffer() {
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
      $ns = $sm->process($this->cs,STATE_MONITOR_REPORT_BUFFER);
      $this->assertEquals($ns,STATE_MONITOR_STORE_REPORT_BUFFER);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(2,$lines);
      $found = false;
      foreach ($lines as $line) {
         if (strpos($line,"reportLog.buffer") !== false) {
            $found = true;
         }
      }
      $this->assertTrue($found);
   }
   
   public function testProcessMonitorStoreReportBuffer() {
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
      $_POST["p2"] = Defaults::$reportBuffer;
      $ns = $sm->process($this->cs,STATE_MONITOR_STORE_REPORT_BUFFER);
      $this->assertEquals($ns,STATE_IDLE);
   }
   
   public function testCheckPosition() {
      $dbcity = $this->cr->getDbc();
      $idx = $dbcity->getCastleIdx();
      $this->assertGreaterThan(0,$idx);
      $beforeIdx = $idx;
      printf("json-x: %d, json-y: %d\n", $this->tc->getX(),$this->tc->getY());
      $sm = new StateMonitor($this->cr,$this->tc);
      $this->assertNotNull($sm);
      $sm->process($this->cs,STATE_MONITOR);
      $this->assertEquals($beforeIdx,$dbcity->getCastleIdx());
      $json = json_decode(Defaults::$defaultCityJson);
      $json->x = 850;
      $json->y = 850;
      $alteredTc = new City($json);
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$alteredTc);
      $sm = new StateMonitor($this->cr,$alteredTc);
      $sm->process($this->cs,STATE_MONITOR);
      $this->assertNotEquals($beforeIdx,$dbcity->getCastleIdx());
      $beforeIdx = $dbcity->getCastleIdx();
      $sm->process($this->cs,STATE_MONITOR);
      $this->assertEquals($beforeIdx,$dbcity->getCastleIdx());
   }
   
   
   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
         if ($this->cs->isOpen()) {
            $this->cs->endFile();
         }
         $this->cs->purge();
      }
   }
}

?>