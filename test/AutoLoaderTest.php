<?php

include_once(dirname(__file__) . '/../helper/autoloader.php');

class AutoLoaderTest extends PHPUnit_Framework_TestCase {
 
  public function testAutoloadFile() {
    AutoLoader::auto_load('Config');
    $this->assertTrue(class_exists('Config'));
  }
  
  public function testAutoloadFileLowercase() {
    AutoLoader::auto_load('simpleloginhelper');
    $this->assertTrue(class_exists('SimpleLoginHelper'));
  }
  
}