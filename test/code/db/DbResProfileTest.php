<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "code/cities/city.php";
require_once "code/db/DbCity.php";
require_once "code/db/DbResProfile.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class DbResProfileTest extends TestCase
{
   protected $cr;
   protected $tc;
   protected $dbc;
   protected $cs;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->cs->startFile();
      $this->tc->setResProfile(new DbResProfile($this->dbc, $this->cr->getDbc()->getResProfile()));
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr);
      $this->assertNotNull($this->cs);
      $dbrp = new DbResProfile($this->dbc, 1);
      $this->assertNotNull($dbrp);
	}
   
   public function testGetId() {
      $dbrp = new DbResProfile($this->dbc, 1);
      $this->assertNotNull($dbrp);
      $this->assertEquals(1, $dbrp->getId());
   }
   
   public function getBadId() {
      $dbrp = new DbResProfile($this->dbc, 0);
      $this->assertNotNull($dbrp);
      $this->assertEquals(0, $dbrp->getMaxGoldAmt());
   }
   
   public function testGetName() {
      $dbrp = new DbResProfile($this->dbc, 1);
      $this->assertNotNull($dbrp);
      $this->assertNotNull($dbrp->getName());
      printf("ResProfile: %s\n", $dbrp->getName());
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