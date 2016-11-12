<?php
use PHPUnit\Framework\TestCase;

require_once "code/chat/ChatMonitor.php";
require_once "code/cities/city.php";
require_once "code/db/DbAlts.php";
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
   protected $dbAlts;
   protected $cm;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
      $this->dbAlts = new DbAlts($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cm = new ChatMonitor($this->cr);
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->dbAlts);
	   $this->assertTrue(true);
	}
   
   public function testProcess() {
      $this->assertNotNull($this->cm);
      $this->dbAlts->setHostTestOnly("notme");
      $this->cm->process($this->cs);
      $_POST["p9"] = NULL;
      $this->dbAlts->setHostTestOnly(Defaults::$player);
      $this->cm->process($this->cs);
      $_POST["p9"] = Defaults::$chatmsgs;
      $this->cm->process($this->cs);
      $applyMsg = '[{"chatType":0,"senderType":0,"from":"Imperator","message":"APPLY","time":1478731996923}]';
      $_POST["p9"] = $applyMsg;
      $this->cm->process($this->cs);
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