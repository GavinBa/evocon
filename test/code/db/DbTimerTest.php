<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/db/DbTimer.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "code/timers/TimerType.php";
require_once "lib/db.php";


class DbTimerTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   protected $rm;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
   }
   
	public function testConstructor() {
      $timer = new DbTimer($this->dbc, $this->cr->getServer(),
                           $this->cr->getUser(), TimerType::TEST);
      $this->assertNotNull($timer);
	}
   
   public function testGetId() {
      $timer = new DbTimer($this->dbc, $this->cr->getServer(),
                           $this->cr->getUser(), TimerType::TEST);
      $this->assertNotNull($timer);
      $timer->deleteTimer();
      $this->assertEquals(-1,$timer->getId());
      $timer->createTimer();
      $this->assertGreaterThan(0,$timer->getId());
   }
   
   public function testHasTimer() {
      $timer = new DbTimer($this->dbc, $this->cr->getServer(),
                           $this->cr->getUser(), TimerType::TEST);
      $this->assertNotNull($timer);
      $timer->deleteTimer();
      $this->assertFalse($timer->hasTimer());
      $timer->createTimer();
      $this->assertTrue($timer->hasTimer());
   }
   
   public function testTimerVal() {
      $timer = new DbTimer($this->dbc, $this->cr->getServer(),
                           $this->cr->getUser(), TimerType::TEST);
      $this->assertNotNull($timer);
      $timer->createTimer();
      $timer->setTimerVal(10000000000);
      printf("timerval: %s\n", $timer->getTimerVal());
      $this->assertEquals(10000000000,$timer->getTimerVal());
      $timer->setTimerVal(10000000001);
      printf("timerval: %s\n", $timer->getTimerVal());
      $this->assertEquals(10000000001,$timer->getTimerVal());
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