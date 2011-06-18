<?php

class MainHandler extends HttpHandler {
  
  function get($params) {
    tpl::set('title', 'Welcome to your new PHP-framework instance');
    tpl::set('hascache', is_dir(c::get('twig.root' . '/cache')));
    $this->do_layout('index');
  }
  
}