<?php

include_once('PHPF_MasterTest.php');

class HttpHandlerTest extends PHPF_MasterTest {
  
  public function testGet() {
    $this->setExpectedException('HttpResponderException');
    
    $handler = new HttpHandler();
    $handler->get(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testPost() {
    $this->setExpectedException('HttpResponderException');
    
    $handler = new HttpHandler();
    $handler->post(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testHead() {
    $this->setExpectedException('HttpResponderException');
    
    $handler = new HttpHandler();
    $handler->head(null);
    
    $this->fail('No exception has been thrown.');
  }
  
  public function testCLI() {
    $this->setExpectedException('HttpResponderException');
    
    $handler = new HttpHandler();
    $handler->cli(null);
    
    $this->fail('No exception has been thrown.');
  }
  
}
