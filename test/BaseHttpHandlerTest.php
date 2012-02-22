<?php

require_once dirname(__FILE__) . '/../classes/BaseHttpHandler.php';
require_once dirname(__FILE__) . '/../classes/ConfigIni.php';

class BaseHttpHandlerTest extends PHPUnit_Framework_TestCase {
  private static $config = null;

  public static function setUpBeforeClass() {
    self::$config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini');
  }
  
  public function testGet() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null, self::$config);
    $handler->get(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testPost() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null, self::$config);
    $handler->post(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testHead() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null, self::$config);
    $handler->head(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testCLI() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null, self::$config);
    $handler->cli(null);
    
    $this->fail('No exception has been thrown.');
  }
  
}
