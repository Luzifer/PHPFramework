<?php

require_once dirname(__file__) . '/../classes/Config.php';
require_once dirname(__file__) . '/../classes/BaseSessionInterface.php';
require_once dirname(__file__) . '/../classes/BaseSession.php';

class BaseSessionTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @expectedException BaseSessionException
   * @expectedExceptionMessage Session class not defined
   */
  public function testConstructException() {
    $session = new BaseSession();
  }

  /**
   * @expectedException BaseSessionException
   * @expectedExceptionMessage Session class fail not found
   */
  public function testConstructExceptionClassNotFound() {
    $session = new BaseSession('fail');
  }  
}
