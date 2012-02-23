<?php

class TestappMainHandler extends BaseHttpHandler {

  public function get($params) {
    // TODO: Write your own code

    $this->response->set('tplvar', 'test');
    $this->response->display('test_template');
  }

}
