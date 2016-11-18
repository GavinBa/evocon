<?php

use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/db/DbTimer.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "code/timers/Timer.php";
require_once "code/timers/TimerType.php";
require_once "lib/db.php";


class TimerTest extends TestCase
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
      $timer = new Timer($this->dbc, $this->cr->getServer(),
                         $this->cr->getUser(), TimerType::TEST);
      $this->assertNotNull($timer);
      $this->assertTrue($timer->getDbTimerTestOnly()->hasTimer());
	}
   
   public function testExpiration() {
      $timer = new Timer($this->dbc, $this->cr->getServer(),
                         $this->cr->getUser(), TimerType::TEST);
      $this->assertNotNull($timer);
      $timer->setExpiration(10000000000000,1000);
      $this->assertFalse($timer->hasExpired(10000000000000));
      $this->assertTrue($timer->hasExpired(10000000002000));
      $timer->setExpiration(10000000000000,5000);
      $this->assertFalse($timer->hasExpired(10000000002000));
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
