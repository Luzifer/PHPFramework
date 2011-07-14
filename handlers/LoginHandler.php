<?php

class LoginHandler extends HttpHandler {
  
  function get($params) {
    $login = SimpleLoginHelper::getInstance();
    $login->requireGroup('samplegroup');
    
    tpl::set('title', 'Welcome to your new PHP-framework instance');
    tpl::set('user', $login->getUser());
    
    $this->do_layout('login');
  }
  
}