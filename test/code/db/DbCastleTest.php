<?php
use PHPUnit\Framework\TestCase;

require_once "code/cities/city.php";
require_once "code/db/dbCastle.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";
require_once "lib/db.php";


class DbAltsTest extends TestCase
{
   protected $tcJson;
   protected $tc;
   protected $dbc;
   protected $dbCity;
   protected $cr;
   protected $cs;
   protected $dbcastle;
   
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
      $this->dbcastle = new DbCastle($this->dbc,Defaults::$server,$this->tc->getX(),$this->tc->getY());
   }
   
	public function testConstructor() {
      $this->assertNotNull($this->dbcastle);
      $this->dbcastle->create();
      $this->assertTrue($this->dbcastle->exists());
	}
   
   public function testGetters() {
      $this->assertEquals(Defaults::$server,$this->dbcastle->getServer());
      $this->assertEquals($this->tc->getX(),$this->dbcastle->getX());
      $this->assertEquals($this->tc->getY(),$this->dbcastle->getY());
   }
   
   public function testRemove() {
      $this->assertNotNull($this->dbcastle);
      $this->dbcastle->create();
      $this->assertTrue($this->dbcastle->exists());
      $this->dbcastle->remove();
      $this->assertFalse($this->dbcastle->exists());
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