<?php

class KnutHandler extends HttpHandler {
  
  function get($params) {
    tpl::set('nummer', $params[0]);
    $this->do_layout('knut');
  }
  
  function post($params) {
    $this->do_layout('knut_post');
  }
  
}
