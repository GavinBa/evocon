<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/db/DbAlts.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class DbAltsTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   protected $dbAlts;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
      $this->dbAlts = new DbAlts($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->dbAlts);
	   $this->assertTrue(true);
	}
   
   public function testPlayerExists() {
      $this->assertNotNull($this->dbAlts);
      $this->assertTrue($this->dbAlts->playerExists());
      $dba = new DbAlts($this->dbc,Defaults::$server,"blah",$this->tc);
      $this->assertNotNull($dba);
      $this->assertFalse($dba->playerExists());
   }   
   
   public function testGetHost() {
      $this->assertNotNull($this->dbAlts);
      $this->assertSame($this->dbAlts->getHost(),"Imperator");
      $this->assertNotSame($this->dbAlts->getHost(),"Impy");
   }

   public function testGetAlliance() {
      $this->assertNotNull($this->dbAlts);
      $this->assertSame($this->dbAlts->getAlliance(),"Imps");
      $this->assertNotSame($this->dbAlts->getAlliance(),"Impy");
   }
   
   public function testHasApplied() {
      $this->assertNotNull($this->dbAlts);
      $this->assertFalse($this->dbAlts->hasApplied());
      $this->dbAlts->setApplied(1);
      $this->assertTrue($this->dbAlts->hasApplied());
      $this->dbAlts->setApplied(0);
      $this->assertFalse($this->dbAlts->hasApplied());
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