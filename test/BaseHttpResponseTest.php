<?php

require dirname(__FILE__) . '/../classes/BaseHttpResponse.php';

class BaseHttpResponseTest extends PHPUnit_Framework_TestCase {
  
  public function testGetNotSet() {
    $response = new BaseHttpResponse(
        new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini')
      , dirname(__FILE__) . '/resources/'
    );
    $this->assertEquals(NULL, $response->get('thiskeycannotbeset', null));
  }
  
  public function testSetGet() {
    $response = new BaseHttpResponse(
        new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini')
      , dirname(__FILE__) . '/resources/'
    );
    $response->set('param1', '12345');
    $response->set('param2', '67890');
    $this->assertEquals('12345', $response->get('param1'));
    $this->assertEquals('67890', $response->get('param2'));
  }

  public function testRender() {
    $tplvar = '2134dfg';

    $response = new BaseHttpResponse(
      new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini')
      , dirname(__FILE__) . '/resources/'
    );
    $response->set('tplvar', $tplvar);

    $expect = file_get_contents(dirname(__FILE__) . '/resources/test_template.html');
    $expect = str_replace('{{ tplvar }}', $tplvar, $expect);

    $this->assertEquals($expect, $response->render('test_template'));
  }

  public function testDisplay() {
    $tplvar = '2134dfg';

    $response = new BaseHttpResponse(
      new ConfigIni(dirname(__FILE__) . '/resources/ConfigIniTest.ini')
      , dirname(__FILE__) . '/resources/'
    );
    $response->set('tplvar', $tplvar);

    $expect = file_get_contents(dirname(__FILE__) . '/resources/test_template.html');
    $expect = str_replace('{{ tplvar }}', $tplvar, $expect);

    $output = "";
    ob_start();
    $response->display('test_template');
    $output = ob_get_contents();
    ob_end_clean();

    $this->assertEquals($expect, $output);
  }
  
}
