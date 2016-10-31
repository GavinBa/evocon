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
require_once "code/request/ClientRequest.php";
require_once "code/reports/ReportBuffer.php";
require_once "code/scout/Scouter.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class DeadCitiesTest extends TestCase
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
      $dc = new DeadCities($this->tc,$this->cr);
      $this->assertNotNull($dc);
	}
   
   public function testProcessInitial() {
      $dbs = DbScout::fromExisting($this->dbc, new DbCity($this->dbc, Defaults::$server, Defaults::$player, $this->tc));
      $dbs->setState(SCOUT_NOTARGET);
      $dc = new DeadCities($this->tc,$this->cr);
      $ns = $dc->process($this->cs, STATE_DEADCITIES);
      $this->assertEquals($ns,STATE_DEADCITIES_SEARCH);
   }
   
   public function testProcessSearch() {
      $dc = new DeadCities($this->tc,$this->cr);
      $this->assertNotNull($dc);
      $ns = $dc->process($this->cs, STATE_DEADCITIES_SEARCH);
      $this->assertEquals($ns,STATE_DEADCITIES_CASTLES);
      $this->cs->endFile();
      $fp = $this->cs->getFullPath();
      printf("Checking on file %s\n", $fp);
      $lines = file($fp);
      $this->assertCount(26,$lines);
   }
   
   public function testProcessCastles() {
      // reset scouting state
      $dbs = DbScout::fromExisting($this->dbc, new DbCity($this->dbc, Defaults::$server, Defaults::$player, $this->tc));
      $dbs->setState(SCOUT_IDLE);
      
      /* Verify a null response is handled correctly. */
      $dc = new DeadCities($this->tc,$this->cr);
      $this->assertNotNull($dc);
      $_POST["p2"] = "{}";
      $ns = $dc->process($this->cs, STATE_DEADCITIES_CASTLES);
      $this->assertEquals($ns, STATE_IDLE);
      
      // not a candidate (flag null)
      $_POST["p2"] = '{"fields":[{"honor":0,"lastUpdated":1477179845566,"flag":null,"canLoot":false,"furlough":false,"changeface":0,"coords":"810,810","userName":null,"canScout":true,"zoneName":"BAVARIA","canOccupy":true,"id":340560,"npc":true,"canTrans":false,"allianceName":null,"state":1,"playerLogoUrl":null,"canSend":false,"name":"Barbarians city","relation":2,"prestige":2500000}]}';
      $ns = $dc->process($this->cs, STATE_DEADCITIES_CASTLES);
      $this->assertEquals($ns, STATE_IDLE);
      // not a candidate (relation < 2)
      $_POST["p2"] = '{"fields":[{"honor":0,"lastUpdated":1477179845566,"flag":"Test","canLoot":false,"furlough":false,"changeface":0,"coords":"810,810","userName":null,"canScout":true,"zoneName":"BAVARIA","canOccupy":true,"id":340560,"npc":true,"canTrans":false,"allianceName":null,"state":1,"playerLogoUrl":null,"canSend":false,"name":"Barbarians city","relation":1,"prestige":2500000}]}';
      $ns = $dc->process($this->cs, STATE_DEADCITIES_CASTLES);
      $this->assertEquals($ns, STATE_IDLE);
      // not a candidate (prestige too high)
      $_POST["p2"] = '{"fields":[{"honor":0,"lastUpdated":1477179845566,"flag":"Test","canLoot":false,"furlough":false,"changeface":0,"coords":"810,810","userName":null,"canScout":true,"zoneName":"BAVARIA","canOccupy":true,"id":340560,"npc":true,"canTrans":false,"allianceName":null,"state":1,"playerLogoUrl":null,"canSend":false,"name":"Barbarians city","relation":2,"prestige":7500000}]}';
      $ns = $dc->process($this->cs, STATE_DEADCITIES_CASTLES);
      $this->assertEquals($ns, STATE_IDLE);
      // good candidate - just added neighbor
      $_POST["p2"] = '{"fields":[{"honor":0,"lastUpdated":1477179845566,"flag":"Test","canLoot":false,"furlough":false,"changeface":0,"coords":"810,810","userName":null,"canScout":true,"zoneName":"BAVARIA","canOccupy":true,"id":340560,"npc":true,"canTrans":false,"allianceName":null,"state":1,"playerLogoUrl":null,"canSend":false,"name":"Barbarians city","relation":2,"prestige":2500000}]}';
      $ns = $dc->process($this->cs, STATE_DEADCITIES_CASTLES);
      $this->assertEquals($ns, STATE_IDLE);
      // get castle
      $c = new DbCastle($this->dbc,Defaults::$server, 810, 810);
      $this->assertNotNull($c);
      $this->assertGreaterThan(0, $c->getId());
      printf("New neighbor added: %d\n", $c->getId());
      // good candidate - neighbor past due for prestige check
      $dbn = new DbNeighbors($this->dbc, Defaults::$server, 
                            Defaults::$player, $this->tc, $c->getId());
      $this->cr->setCtime((string)($dbn->getLastCheck() + 2800000));
      $_POST["p2"] = '{"fields":[{"honor":0,"lastUpdated":1477179845566,"flag":"Test","canLoot":false,"furlough":false,"changeface":0,"coords":"810,810","userName":null,"canScout":true,"zoneName":"BAVARIA","canOccupy":true,"id":340560,"npc":true,"canTrans":false,"allianceName":null,"state":1,"playerLogoUrl":null,"canSend":false,"name":"Barbarians city","relation":2,"prestige":2500000}], "canScout" : "true"}';
      $ns = $dc->process($this->cs, STATE_DEADCITIES_CASTLES);
      $this->assertEquals($ns, STATE_DEADCITIES_SCOUTING);
   }
   
   public function testProcessScouting() {
      
      /* Test the "has not arrived case" */

      /*    reset the expected report time */
      $scouter = Scouter::fromExisting($this->cr,$this->cs);
      $scouter->setReportTime(0);
      
      $dc = new DeadCities($this->tc,$this->cr);
      $this->assertNotNull($dc);
      $_POST["p2"] = '{"transit": 0, "attack": 15}';
      $ns = $dc->process($this->cs, STATE_DEADCITIES_SCOUTING);
      $this->assertEquals($ns, STATE_DEADCITIES_SCOUTING);
      
      /* Test the scout has arrived with (reportTime = 0) */
      $_POST["p2"] = "{}";
      $dbs = DbScout::fromExisting($this->dbc, new DbCity($this->dbc, Defaults::$server, Defaults::$player, $this->tc));
      $scouter->setReportTime(0);
      $this->assertNotNull($dbs);
      $dbs->setScoutTime($this->cr->getCtime());
      $this->cr->setCtime($this->cr->getCtime() + 25000);
      $dbs->setReportTime(0);
      $dc = new DeadCities($this->tc,$this->cr);
      $ns = $dc->process($this->cs, STATE_DEADCITIES_SCOUTING);
      $this->assertEquals($ns, STATE_SUSPEND);
      $this->assertGreaterThan(0, $scouter->getReportTime());
      
      /* Test the report time is now fresh for our expected scout */
      
      // update the client request to indicate 10 seconds later
      $this->cr->setCtime($this->cr->getCtime() + 10000);
      
      // add a report update which will be after expected time.
      $rb = new ReportBuffer($this->dbc,$this->tc,$this->cr);
      $rb->add(Defaults::$reportBufferOneLine);
      
      $dc = new DeadCities($this->tc,$this->cr);
      $ns = $dc->process($this->cs, STATE_DEADCITIES_SCOUTING);
      $this->assertEquals($ns, STATE_DEADCITIES_REPORT);
      
      $rb = new ReportBuffer($this->dbc,$this->tc,$this->cr);
      $this->assertNotNull($rb->getLastReport(810,810));
      
   }

   protected function tearDown() {
      if ($this->dbc) {
         db_disconnectDB($this->dbc);
         printf("Database closed.\n");
         if ($this->cs->isOpen()) {
            $this->cs->endFile();
         }
//         $this->cs->purge();
      }
   }
}

?>