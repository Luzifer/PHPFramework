<?php

class Error404 extends HttpHandler {
  
  public function get($params) { $this->handle(); }
  public function post($params) { $this->handle(); }
  public function head($params) { $this->handle(); }
  
  private function handle() {
    header('HTTP/1.1 404 Not Found');
    tpl::load("internal/error404");
    return false;
  }
  
}
