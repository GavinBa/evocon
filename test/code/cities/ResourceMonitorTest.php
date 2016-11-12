<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/cities/ResourceMonitor.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class ChatMonitorTest extends TestCase
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