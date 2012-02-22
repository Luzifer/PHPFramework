<?php

require_once(dirname(__FILE__) . '/../classes/ConfigIni.php');

class ConfigIniTest extends PHPUnit_Framework_TestCase {

  public function testInvalidConfigFile() {
    $this->setExpectedException('ConfigIniSectionNotFoundException');

    $config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTestInvalid.ini');
    $config->get('test');
  }

  public function testNotExistingConfigFile() {
    $this->setExpectedException('ConfigIniFileNotFoundException');

    $config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTestNotExisting.ini');
  }

  public function testExistingGet() {
    $config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini');

    $this->assertEquals('bar', $config->get('key1', null));
    $this->assertEquals('1', $config->get('key2', null));
    $this->assertEquals('http://blog.knut.me/', $config->get('key3', null));
  }

  public function testNotExistingGet() {
    $config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini');

    $this->assertEquals(null, $config->get('key512', null));
    $this->assertEquals(513, $config->get('key513', 513));
  }

  public function testExistingGetSection() {
    $config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini');
    $val = $config->getSection('section1');

    $this->assertInternalType('array', $val);
    $this->assertEquals('PHPFramework', $val['project']);
  }

  public function testNotExistingGetSection() {
    $this->setExpectedException('ConfigIniSectionNotFoundException');
    $config = new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini');

    $config->getSection('me_is_not_in_there');
  }

}
