<?php

class PHPF_MasterTest extends PHPUnit_Framework_TestCase {
  
  public static function setUpBeforeClass() {
    include_once(dirname(__file__) . '/../helper/autoloader.php');
    spl_autoload_register('AutoLoader::auto_load');
    echo "Test";
  }
  
}