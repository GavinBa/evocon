<?php
use PHPUnit\Framework\TestCase;

require "code/cities/city.php";


class TestCity extends TestCase
{
   protected $tcJson;
   protected $tc;
   
   protected function setUp() {
      $this->tcJson = json_decode(Defaults::$defaultCityJson);
      $this->tc = new City($this->tcJson);
   }
   
	public function testConstructor() {
      printf("name=%s\n", $this->tc->getName());
      $this->assertNotNull($this->tc);
	   $this->assertTrue(true);
	}
}