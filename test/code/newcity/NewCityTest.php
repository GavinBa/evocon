<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "StateProcessor.php";
require_once "code/cities/city.php";
require_once "code/db/DbCastle.php";
require_once "code/db/DbScout.php";
require_once "code/db/DbCity.php";
require_once "code/newcity/NewCity.php";
require_once "code/request/ClientRequest.php";
require_once "code/scout/Scouter.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class NewCityTest extends TestCase
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
   }
   
	public function testConstructor() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
	}
   
   public function testNewCity() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
      // reset newcity state
      $this->cr->getDbc()->setNewCity(0);
      $ns = $nc->process($this->cs,STATE_NEWCITY);
      $this->assertEquals($ns,STATE_NEWCITY_CANBUILD);
      $this->cr->getDbc()->setNewCity(1);
      $ns = $nc->process($this->cs,STATE_NEWCITY);
      $this->assertEquals($ns,STATE_SUSPEND);
   }
   
   public function testCanBuild() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
      // reset newcity state
      $this->cr->getDbc()->setNewCity(0);
      $this->assertEquals(0,$this->cr->getDbc()->getNewCity(0));
      $_POST["p2"] = '{ "result": 0 }';
      $ns = $nc->process($this->cs,STATE_NEWCITY_CANBUILD);
      $this->assertEquals($ns,STATE_IDLE);
      $_POST["p2"] = '{ "result": 1 }';
      $ns = $nc->process($this->cs,STATE_NEWCITY_CANBUILD);
      $this->assertEquals($ns,STATE_NEWCITY_FINDFLAT);
      $_POST["p2"] = NULL;
      $ns = $nc->process($this->cs,STATE_NEWCITY_CANBUILD);
      $this->assertEquals($ns,STATE_IDLE);
   }
   
   public function testFindFlat() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
      $ns = $nc->process($this->cs,STATE_NEWCITY_FINDFLAT);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(26,$lines);
      $this->assertEquals($ns,STATE_NEWCITY_FLATS);
   }
   
   public function testFlats() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
      $_POST["p2"] = NULL;
      $ns = $nc->process($this->cs,STATE_NEWCITY_FLATS);
      $this->assertEquals($ns,STATE_IDLE);
      $_POST["p2"] = '{}';
      $ns = $nc->process($this->cs,STATE_NEWCITY_FLATS);
      $this->assertEquals($ns,STATE_IDLE);
      $_POST["p2"] = '{ "fields" : []}';
      $ns = $nc->process($this->cs,STATE_NEWCITY_FLATS);
      $this->assertEquals($ns,STATE_IDLE);
      $_POST["p2"] = Defaults::$fields;
      $ns = $nc->process($this->cs,STATE_NEWCITY_FLATS);
      $this->assertEquals($ns,STATE_NEWCITY_BESTFLAT);
   }
   
   public function testBestFlat() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
      $_POST["p2"] = NULL;
      $ns = $nc->process($this->cs,STATE_NEWCITY_BESTFLAT);
      $this->assertEquals($ns,STATE_SUSPEND);
      $_POST["p2"] = '{}';
      $ns = $nc->process($this->cs,STATE_NEWCITY_BESTFLAT);
      $this->assertEquals($ns,STATE_SUSPEND);
      $_POST["p2"] = '{ "result": 123456, "isOwned": 1, "canAttack": 1, "user":"notme"}';
      $ns = $nc->process($this->cs,STATE_NEWCITY_BESTFLAT);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->assertEquals(2,$this->cr->getDbc()->getNewCity());
      $this->assertEquals(123456,$this->cr->getDbc()->getNewCityFieldId());
      // reset newcity state
      $this->cr->getDbc()->setNewCity(0);
      $this->assertEquals(0,$this->cr->getDbc()->getNewCity());
      $_POST["p2"] = '{ "result": 123456, "isOwned": 0, "canAttack": 1, "user":"notme"}';
      $ns = $nc->process($this->cs,STATE_NEWCITY_BESTFLAT);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->assertEquals(2,$this->cr->getDbc()->getNewCity());
   }
   
   public function testWaitOnFlat() {
      $nc = new NewCity($this->tc, $this->cr, $this->cs);
      $this->assertNotNull($nc);
      $this->cr->getDbc()->setNewCity(1);
      $ns = $nc->process($this->cs,STATE_NEWCITY);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->cr->getDbc()->setNewCity(2);
      $this->cr->getDbc()->setNewCityFieldId(352567);
      $ns = $nc->process($this->cs,STATE_NEWCITY);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->cr->getDbc()->setNewCityFieldId(351774);
      $ns = $nc->process($this->cs,STATE_NEWCITY);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->assertEquals(1,$this->cr->getDbc()->getNewCity());
      $this->cr->getDbc()->setNewCity(2);
      $this->cr->getDbc()->setNewCityFieldId(777777);
      $ns = $nc->process($this->cs,STATE_NEWCITY);
      $this->assertEquals($ns,STATE_SUSPEND);
      $this->assertEquals(0,$this->cr->getDbc()->getNewCity());
      $this->assertEquals(0,$this->cr->getDbc()->getNewCityFieldId());
   }
   
  
   protected function purgeFiles() {
      if ($this->cs->isOpen()) {
         $this->cs->endFile();
      }
      $this->cs->purge();
   }  

   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
      }
      $this->purgeFiles();
   }
}

?>