<?php

require_once dirname(__file__) . '/../classes/Config.php';
require_once dirname(__file__) . '/../classes/BaseHttpHandler.php';

class BaseHttpHandlerTest extends PHPUnit_Framework_TestCase {
  
  public function testGet() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null);
    $handler->get(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testPost() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null);
    $handler->post(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testHead() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null);
    $handler->head(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testCLI() {
    $this->setExpectedException('MethodNotImplementedException');
    
    $handler = new BaseHttpHandler(null, null);
    $handler->cli(null);
    
    $this->fail('No exception has been thrown.');
  }
  
}
