<?php

require_once dirname(__file__) . '/../classes/BaseSessionInterface.php';
require_once dirname(__file__) . '/../classes/BaseSession.php';

class BaseSessionTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @expectedException BaseSessionException
   * @expectedExceptionMessage Session class is not defined
   */
  public function testConstructException() {
    $session = new BaseSession();
  }   
}
