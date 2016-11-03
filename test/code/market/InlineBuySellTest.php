<?php
use PHPUnit\Framework\TestCase;

require_once "data/Defaults.php";
require_once "code/cities/city.php";
require_once "StateProcessor.php";
require_once "code/cities/DeadCities.php";
require_once "code/db/DbCastle.php";
require_once "code/db/DbScout.php";
require_once "code/db/DbCity.php";
require_once "code/db/DbReportBuffer.php";
require_once "code/fields/Field.php";
require_once "code/market/InlineBuySell.php";
require_once "code/request/ClientRequest.php";
require_once "code/reports/ReportBuffer.php";
require_once "code/scout/Scouter.php";
require_once "code/script/ClientScript.php";
require_once "code/war/StateWarDefense.php";
require_once "lib/db.php";


class InlineBuySellTest extends TestCase
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
      $this->tc->setResProfile(new DbResProfile($this->dbc, $this->cr->getDbc()->getResProfile()));
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->tc);
      $this->assertNotNull($this->cr);
      $this->assertNotNull($this->cs);
      $ibs = new InlineBuySell($this->cr,$this->tc);
      $this->assertNotNull($ibs);
	}
   
   public function testProcess() {
      $ibs = new InlineBuySell($this->cr,$this->tc);
      $this->assertNotNull($ibs);
      $this->tc->getJson()->reservedResource->gold = 1000000000;
      $ibs->process($this->cs);
   }
   
   public function testMediumCityProcess() {
      $ibs = new InlineBuySell($this->cr,$this->tc);
      $this->assertNotNull($ibs);
      $this->cr->getDbc()->setResProfile(2);
      $this->tc->setResProfile(new DbResProfile($this->dbc, $this->cr->getDbc()->getResProfile()));
      $ibs = new InlineBuySell($this->cr,$this->tc);
   }
   
   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
         if ($this->cs->isOpen()) {
            $this->cs->endFile();
         }
         $this->cs->dumpFileToStdout();
         $this->cs->purge();
      }
   }
}

?>