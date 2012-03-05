<?php

require_once dirname(__FILE__) . '/../classes/BaseSessionInterface.php';
require_once dirname(__FILE__) . '/../classes/BasePHPSession.php';

class BasePHPSessionTest extends PHPUnit_Framework_TestCase {

  public function testSetAndGet() {
    $session = new BasePhpSession(true);
    $session->set('foo', 'bar');
    $session->set('foo2', 'bar2');  

    $this->assertEquals('bar', $session->get('foo'));
    $this->assertEquals('bar2', $session->get('foo2'));
  }

  /**
   * @expectedException BaseSessionUndefinedIndexException
   * @expectedExceptionMessage Undefined session index foo
   */
  public function testClear() {
    $session = new BasePhpSession(true);
    $session->clear('foo');
    
    echo $session->get('foo');
  }

  public function testClearAll() {
    $session = new BasePhpSession(true);
    $session->clear_all();
    
    $this->assertEquals(null, $session->get('foo2', null));
  }
}
