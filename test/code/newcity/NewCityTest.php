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