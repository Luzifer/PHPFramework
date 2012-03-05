<?php

require dirname(__FILE__) . '/../classes/BaseHttpRequest.php';

class BaseHttpRequestTest extends PHPUnit_Framework_TestCase {
  
  public static function setUpBeforeClass() {
    $_GET = array(
        'param1' => '1234'
      , 'param2' => '3467'
    );
    $_POST = array(
        'param3' => '76543'
      , 'param4' => '2345'
    );
  }
  
  public function testUnsupportedRequestMethod() {
    $this->setExpectedException('MethodNotSupportedException');
    
    $request = new BaseHttpRequest('FOOBAR');
    $request->get('param1');
    
    $this->fail('Using get with unupported method has to throw an exception.');
  }
  
  public function testGetParameter() {
    $request = new BaseHttpRequest('get');
    $this->assertEquals('1234', $request->get('param1', false, false));
  }
  
  public function testGetParameterWithStrictEnabledNotExisting() {
    $request = new BaseHttpRequest('get');
    $this->assertEquals(null, $request->get('param3', null, true));
  }
  
  public function testGetPOSTParameter() {
    $request = new BaseHttpRequest('post');
    $this->assertEquals(null, $request->get('param6', null));
    $this->assertEquals('2345', $request->get('param4'));
  }
  
}
