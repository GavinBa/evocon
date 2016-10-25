<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require "code/cities/city.php";
require "code/db/DbCity.php";
require "code/db/DbReportBuffer.php";
require "code/request/ClientRequest.php";
require_once "lib/db.php";


class DbReportBufferTest extends TestCase
{
   protected $cr;
   protected $rpt;
   protected $dbRptBuffer;
   protected $tc;
   protected $dbc;
   
   protected function setUp() {
      $this->rpt = json_decode(Defaults::$reportBuffer);
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,Defaults::$defaultCityJson);
	   $this->dbRptBuffer = new DbReportBuffer($this->dbc,$this->cr);
   }
   
	public function testConstructor() {
      printf("name=%s\n", $this->tc->getName());
      $this->assertNotNull($this->tc);
	   $this->assertTrue(true);
	}
   
   public function testAddLine() {
      $this->assertNotNull($this->dbRptBuffer);
      $this->dbRptBuffer->addLine(Defaults::$reportBufferOneLine);
      $this->assertNotNull($this->dbRptBuffer->getLastReport());
      $rpt = $this->dbRptBuffer->getLastReport();
      $this->assertEquals($rpt,Defaults::$reportBufferOneLine);
      $this->assertGreaterThan(0,$this->dbRptBuffer->getNumReports());
   }
   
   public function testReset() {
      $this->assertNotNull($this->dbRptBuffer);
      $this->dbRptBuffer->addLine(Defaults::$reportBufferOneLine);
      $this->assertGreaterThan(0,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->reset();
      $this->assertEquals(0,$this->dbRptBuffer->getNumReports());
   }
   
   public function testAddAll() {
      $this->assertNotNull($this->dbRptBuffer);
      $this->dbRptBuffer->reset();
      $this->assertEquals(0,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->addAll(Defaults::$reportBufferOneLine);
      $this->assertEquals(1,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->reset();
      $this->assertEquals(0,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->addAll(Defaults::$reportBuffer);
      $this->assertGreaterThan(1,$this->dbRptBuffer->getNumReports());
   }
   
   public function testGetReport() {
      $this->assertNotNull($this->dbRptBuffer);
      $this->dbRptBuffer->reset();
      $this->assertEquals(0,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->addAll(Defaults::$reportBuffer);
      $this->assertGreaterThan(1,$this->dbRptBuffer->getNumReports());
      $rpt = $this->dbRptBuffer->getReport(568,419);
      $this->assertNotNull($rpt);
      printf("rpt = %s\n", $rpt);
   }

   public function testGetMostRecentReport() {
      $this->assertNotNull($this->dbRptBuffer);
      $this->dbRptBuffer->reset();
      $this->assertEquals(0,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->addAll(Defaults::$reportBuffer);
      $this->assertGreaterThan(1,$this->dbRptBuffer->getNumReports());
      $rpt = $this->dbRptBuffer->getMostRecentReport(568,419);
      $this->assertNotNull($rpt);
      printf("rpt = %s\n", $rpt);
   }
   
   public function testGetTimeOfLastUpdate() {
      $this->assertNotNull($this->dbRptBuffer);
      $this->dbRptBuffer->reset();
      $this->assertEquals(0,$this->dbRptBuffer->getNumReports());
      $this->dbRptBuffer->addAll(Defaults::$reportBuffer);
      $t = $this->dbRptBuffer->getTimeOfLastUpdate();
      printf("t=%s\n", $t);
   }
   
   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
      }
   }
}

?>