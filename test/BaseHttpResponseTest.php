<?php

require dirname(__file__) . '/../classes/BaseHttpResponse.php';

class BaseHttpResponseTest extends PHPUnit_Framework_TestCase {
  
  public function testGetNotSet() {
    $response = new BaseHttpResponse();
    $this->assertEquals(NULL, $response->get('thiskeycannotbeset', null));
  }
  
  public function testSetGet() {
    $response = new BaseHttpResponse();
    $response->set('param1', '12345');
    $response->set('param2', '67890');
    $this->assertEquals('12345', $response->get('param1'));
    $this->assertEquals('67890', $response->get('param2'));
  }
  
}