<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/request/ClientRequest.php";
require_once "code/research/Research.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class ResearchTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   protected $research;
   
   protected function setUp() {
      $json = json_decode(Defaults::$defaultCityJson);
      $json->x = 820;
      $json->y = 820;
      $this->tc = new City($json);
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
      $this->research = new Research($this->tc);
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->research);
	}
   
   public function testArchery() {
      printf("Archery: %d\n", $this->research->getArcheryLevel());
   }   

   public function testResearchLevels() {
      printf("Archery:           %d\n", $this->research->getArcheryLevel());
      printf("HorsebackRiding:   %d\n", $this->research->getHorsebackRidingLevel());
      printf("MilitaryTradition: %d\n", $this->research->getMilitaryTraditionLevel());
      printf("Masonry:           %d\n", $this->research->getMasonryLevel());
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