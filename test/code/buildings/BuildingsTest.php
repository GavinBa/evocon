<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "StateProcessor.php";
require_once "code/buildings/buildings.php";
require_once "code/cities/city.php";
require_once "code/db/DbCity.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class BuildingsTest extends TestCase
{
   protected $cr;
   protected $cs;
   protected $tc;
   protected $dbc;
   protected $ib;
   protected $buildings;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
      $this->buildings = new Buildings($this->tc);
   }
   
   public function testConstructor() {
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr);
      $this->assertNotNull($this->cs);
      $this->assertNotNull($this->dbc);
      $this->buildings = new Buildings($this->tc);
      $this->assertNotNull($this->buildings);
   }
   
   public function testGetInnLevel() {
      $this->assertNotNull($this->buildings);
      $this->assertGreaterThan(0,$this->buildings->getInnLevel());
      printf("Inn: %d\n", $this->buildings->getInnLevel());
   }
   
   public function testGetRallyLevel() {
      $this->assertNotNull($this->buildings);
      $this->assertGreaterThan(0,$this->buildings->getRallyLevel());
      printf("Rally: %d\n", $this->buildings->getRallyLevel());
   }
   
   public function testTownHallLevel() {
      $this->assertNotNull($this->buildings);
      $this->assertGreaterThan(0,$this->buildings->getTownHallLevel());
      printf("Townhall: %d\n", $this->buildings->getTownHallLevel());
   }
   
   public function testGetBuildingLevel() {
      $this->assertNotNull($this->buildings);
      $this->assertGreaterThan(0,$this->buildings->getBuildingLevel("Stable"));
      printf("Stable: %d\n", $this->buildings->getBuildingLevel("Stable"));
      $this->assertEquals(0,$this->buildings->getBuildingLevel("Blah"));
      $this->assertGreaterThan(0,$this->buildings->getMinBuildingLevel("Sawmill"));
      printf("Min Sawmill: %d\n", $this->buildings->getMinBuildingLevel("Sawmill"));
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