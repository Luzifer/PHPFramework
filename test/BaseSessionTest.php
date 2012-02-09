<?php

require dirname(__file__) . '/../classes/Config.php';
require dirname(__file__) . '/../classes/BaseSession.php';
require dirname(__file__) . '/../classes/BasePhpSession.php';

class BaseSessionTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @expectedException BaseSessionException
   * @expectedExceptionMessage Session class is not defined
   */
  public function testConstructException() {
    $session = new BaseSession();
  }   
}
