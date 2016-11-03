<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class CityTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   
   protected function setUp() {
      $this->tcJson = json_decode(Defaults::$defaultCityJson);
      $this->tc = new City($this->tcJson);
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->cs->startFile();
   }
   
	public function testConstructor() {
      printf("\nname=%s\n", $this->tc->getName());
      $this->assertNotNull($this->tc);
	   $this->assertTrue(true);
	}
   
   public function testUnderAttack() {
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr->getDbc());
      $v = $this->cr->getDbc()->isUnderAttack();
      $this->assertEquals(0,$v);
      $this->cr->getDbc()->setUnderAttack(1);
      $v = $this->cr->getDbc()->isUnderAttack();
      $this->assertEquals(1,$v);
      $this->cr->getDbc()->setUnderAttack(0);
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