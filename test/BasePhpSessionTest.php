<?php

require dirname(__file__) . '/../classes/BaseSessionInterface.php';
require dirname(__file__) . '/../classes/BasePhpSession.php';

class BasePhpSessionTest extends PHPUnit_Framework_TestCase {

  public function testSetAndGet() {
    $session = new BasePhpSession(true);
    $session->set('foo', 'bar');
    $session->set('foo2', 'bar2');  

    $this->assertEquals('bar', $session->get('foo'));
    $this->assertEquals('bar2', $session->get('foo2'));
  }

  /**
   * @expectedException BaseSessionUndifinedIndexException
   * @expectedExceptionMessage Undifined session index foo
   */
  public function testClear() {
    $session = new BasePhpSession(true);
    $session->clear('foo');
    
    echo $session->get('foo');
  }

  /**
   * @expectedException BaseSessionUndifinedIndexException
   * @expectedExceptionMessage Undifined session index foo2
   */
  public function testClearAll() {
    $session = new BasePhpSession(true);
    $session->clear_all();
    
    echo $session->get('foo2');
  }
}
