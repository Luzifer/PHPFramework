<?php

class TestappError404Handler extends BaseHttpHandler {

  public function get($params) {
    header('HTTP/1.0 404 Not Found');
    $this->response->display('404');
  }

}
