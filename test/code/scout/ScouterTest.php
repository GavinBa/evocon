<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "code/cities/city.php";
require_once "code/db/DbCastle.php";
require_once "code/db/DbScout.php";
require_once "code/db/DbCity.php";
require_once "code/request/ClientRequest.php";
require_once "code/scout/Scouter.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class ScouterTest extends TestCase
{
   protected $cr;
   protected $tc;
   protected $dbc;
   protected $cs;
   protected $dbcastle;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->cs->startFile();
      $this->dbcastle = new DbCastle($this->dbc,Defaults::$server,801,801);
      $this->dbcastle->create();
      $this->assertEquals($this->dbcastle->getX(),801);
      $this->assertEquals($this->dbcastle->getY(),801);
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr);
      $this->assertNotNull($this->cs);
      printf("dbcastleid=%d\n", $this->dbcastle->getId());
      $scouter = new Scouter($this->cr,$this->dbcastle,$this->cs);
      $this->assertNotNull($scouter);
      $this->assertEquals(801,$scouter->getX());
	}
   public function testFromExisting() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $this->assertNotNull($scouter);
      $this->assertEquals(801,$scouter->getX());
      $this->assertEquals(801,$scouter->getY());
   }
   
   public function testGetTroopStr() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $this->assertNotNull($scouter);
      $ts = $scouter->getTroopStr();
      $this->assertGreaterThan(0,strlen($ts));
   }
   
   public function testCanScout() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $dbscout = DbScout::fromExisting($this->dbc,$this->cr->getDbc());
      $dbscout->setState(SCOUT_IDLE);
      $this->assertTrue($scouter->canScout());
      $this->assertNotNull($dbscout);
      $this->assertTrue($dbscout->isIdle());
      $dbscout->setState(SCOUT_OVERDUE);
      $this->assertFalse($dbscout->isIdle());
      $dbscout->setState(SCOUT_IDLE);
      $this->assertTrue($dbscout->isIdle());
   }
   
   public function testSendScout() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $dbscout = DbScout::fromExisting($this->dbc,$this->cr->getDbc());
      $this->assertNotNull($scouter);
      $scouter->sendScout();
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(3,$lines);
      $this->assertEquals($this->cr->getCtime(),$dbscout->getScoutTime());
   }
   
   public function testIsArrived() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $dbscout = DbScout::fromExisting($this->dbc,$this->cr->getDbc());
      $this->assertNotNull($scouter);
      $dbscout->setScoutTime("100000");
      $this->cr->setCtime("110000");
      $this->assertFalse($scouter->isArrived(7));
      $this->assertTrue($scouter->isArrived(2));
   }
   
   public function testAttackTime() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $dbscout = DbScout::fromExisting($this->dbc,$this->cr->getDbc());
      $this->assertNotNull($scouter);
      $scouter->setAttackTime("10000");
      $this->assertEquals(10000,$scouter->getAttackTime());
   }
   
   public function testComplete() {
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $dbscout = DbScout::fromExisting($this->dbc,$this->cr->getDbc());
      $this->assertNotNull($scouter);
      $scouter->complete($this->cr,$this->cs);
//      $dbscout = DbScout::fromExisting($this->dbc,$this->cr->getDbc());
//      $this->assertEquals(SCOUT_NOTARGET,$dbscout->getState());
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