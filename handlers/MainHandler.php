<?php

class MainHandler extends HttpHandler {
  
  function get($params) {
    tpl::set('title', 'Welcome to your new PHP-framework instance');
    $this->do_layout('index');
  }
  
}