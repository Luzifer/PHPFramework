<?php

class HttpHandler {
  protected $layout = 'internal/layout';
  
  protected function redirect($url, $code = 302) {
    if($code == 301) {
      header("HTTP/1.1 301 Moved Permanently");
    } else {
      header("HTTP/1.1 302 Moved Temporarily");
    }
    header('Location: ' . $url);
  }
  
  protected function do_layout($template) {
    tpl::set('_actiontpl', $template);
    tpl::load($this->layout);
  }
  
  public function get($params) { throw new HttpResponderException('Method GET not implemented for ' . get_class($this)); }
  public function head($params) { throw new HttpResponderException('Method HEAD not implemented for ' . get_class($this)); }
  public function post($params) { throw new HttpResponderException('Method POST not implemented for ' . get_class($this)); }
}

class HttpResponderException extends Exception {}