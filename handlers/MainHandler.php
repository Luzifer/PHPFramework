<?php

class MainHandler extends HttpHandler {
  
  function get($params) {
    tpl::set('title', 'Welcome to your new PHP-framework instance');
    tpl::set('cachedisabled', c::get('twig.debug'));
    $this->do_layout('index');
  }
  
}