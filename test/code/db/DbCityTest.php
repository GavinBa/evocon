<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/cities/Development.php";
require_once "code/db/DbCity.php";
require_once "lib/db.php";


class DbCityTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   
   protected function setUp() {
      $this->tcJson = json_decode(Defaults::$defaultCityJson);
      $this->tc = new City($this->tcJson);
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->dbCity = new DbCity($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->dbc);
      $this->assertNotNull($this->dbCity);
	   $this->assertTrue(true);
	}
   
   public function testGetId() {
      $id = $this->dbCity->getId();
      $this->assertGreaterThan(0,$id);
   }
   
   public function testIsLowestIdForPlayer() {
      printf("Lowest id: %d\n", $this->dbCity->getLowestIdForPlayer());
      $this->assertTrue($this->dbCity->isLowestIdForPlayer());
   }
   
   public function testSetState() {
      $teststate = 57;
      $this->dbCity->setState($teststate);
      $this->assertEquals($teststate,$this->dbCity->getState());
   }
   
   public function testSetProcessSlice() {
      $ps = 17;
      $this->dbCity->setProcessSlice($ps);
      $this->assertEquals($ps,$this->dbCity->getProcessSlice());
   }
   
   public function testSetNewCity() {
      $nc = 33;
      $this->dbCity->setNewCity($nc);
      $this->assertEquals($nc,$this->dbCity->getNewCity());
   }
   
   public function testAnyCitySpawning() {
      $nc = 0;      
      $this->dbCity->setNewCity($nc);
      $this->assertEquals($nc,$this->dbCity->getNewCity());
      $this->assertFalse($this->dbCity->isAnyCitySpawning());
      $nc = 1;      
      $this->dbCity->setNewCity($nc);
      $this->assertEquals($nc,$this->dbCity->getNewCity());
      $this->assertTrue($this->dbCity->isAnyCitySpawning());
   }
   
   public function testNewCityField() {
      $this->assertNotNull($this->dbCity);
      $fid = 0;      
      $this->dbCity->setNewCityFieldId($fid);
      $this->assertEquals($fid,$this->dbCity->getNewCityFieldId());
      $fid = 456123;      
      $this->dbCity->setNewCityFieldId($fid);
      $this->assertEquals($fid,$this->dbCity->getNewCityFieldId());
   }
   
   public function testDevelopment() {
      $this->assertNotNull($this->dbCity);
      $this->dbCity->setDevelopment("Grown");
      $dev = $this->dbCity->getDevelopment();
      $this->assertSame($dev,"Grown");
      $this->assertFalse(Development::isUnderDevelopment($dev));
      $this->dbCity->setDevelopment("Hatchling");
      $dev = $this->dbCity->getDevelopment();
      $this->assertTrue(Development::isHatchling($dev));
      $this->assertTrue(Development::isUnderDevelopment($dev));
      $this->dbCity->setDevelopment("Nestling");
      $dev = $this->dbCity->getDevelopment();
      $this->assertTrue(Development::isNestling($dev));
      $this->dbCity->setDevelopment("Fledgling");
      $dev = $this->dbCity->getDevelopment();
      $this->assertTrue(Development::isFledgling($dev));
      $this->assertFalse(Development::isGrown($dev));
   }
   
   public function testRes() {
      $this->assertNotNull($this->dbCity);
      $this->dbCity->setGold(10);
      $this->assertEquals(10,$this->dbCity->getGold());
      $this->dbCity->setFood(15);
      $this->assertEquals(15,$this->dbCity->getFood());
      $this->dbCity->setWood(20);
      $this->assertEquals(20,$this->dbCity->getWood());
      $this->dbCity->setStone(25);
      $this->assertEquals(25,$this->dbCity->getStone());
      $this->dbCity->setIron(30);
      $this->assertEquals(30,$this->dbCity->getIron());
   }
   
   public function testFarmIdx() {
      $this->assertNotNull($this->dbCity);
      $this->assertGreaterThanOrEqual(0,$this->dbCity->getFarmIdx());
   }
   
   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
      }
   }
}
?>