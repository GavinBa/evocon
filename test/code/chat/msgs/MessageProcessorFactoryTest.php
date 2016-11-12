<?php
use PHPUnit\Framework\TestCase;

require_once "code/chat/msgs/MessageProcessorFactory.php";
require_once "code/cities/city.php";
require_once "code/db/DbAlts.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class MessageProcessorFactoryTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   protected $dbAlts;
   protected $cm;
   
   protected function setUp() {
      $this->tc = new City(json_decode(Defaults::$defaultCityJson));
      printf("\nConnecting to database.\n");
      $this->dbc = db_connectDB();
      $this->cr = new ClientRequest($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
      $this->cs = new ClientScript($this->cr);
      $this->assertNotNull($this->cs);
      $this->cs->startFile();
      $this->dbAlts = new DbAlts($this->dbc,Defaults::$server,Defaults::$player,$this->tc);
   }
   
   public function testIsValidMsg() {
      $this->assertFalse(MessageProcessorFactory::isValidMsg("invalid"));
      $this->assertNotFalse(MessageProcessorFactory::isValidMsg("IDLE"));
      $this->assertFalse(MessageProcessorFactory::isValidMsg(75));
      $this->assertFalse(MessageProcessorFactory::isValidMsg(NULL));
   }
   
   public function testGetProcessor() {
      $this->assertFalse(MessageProcessorFactory::getProcessor("invalid"));
      $x = MessageProcessorFactory::getProcessor("IDLE");
      print_r($x);
      $this->assertTrue((MessageProcessorFactory::getProcessor("IDLE") instanceof MessageProcessor));
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