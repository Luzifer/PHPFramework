<?php

require_once dirname(__FILE__) . '/../classes/BaseExceptionVisualizer.php';

class BaseExceptionVisualizerTest extends PHPUnit_Framework_TestCase {

  public function testSetterGetter() {
    BaseExceptionVisualizer::set_display_template('test');
    $this->assertEquals('test', BaseExceptionVisualizer::get_display_template());
  }

}
