<?php

/*
 * Copyright (c) 2011 Knut Ahlers <knut@ahlers.me>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

require_once(dirname(__file__) . '/../lib/kirby/kirby.php');
require_once(dirname(__file__) . '/../lib/kirby/plugins/twig.php');
require_once(dirname(__file__) . '/autoloader.php');

class Dispatcher {
  private static $instance = null;
  private static $config = null;
  
  private function __construct() {
    spl_autoload_register('AutoLoader::auto_load');
    set_exception_handler('ErrorHandler::handle_error');
    
    $config = Config::getInstance();
    if($config->get('db.name', null) != null) {
      foreach(array('db.host', 'db.user', 'db.password', 'db.name') as $key) {
        c::set($key, $config->get($key, ''));
      }
    }
    
    if($config->get('debug', 0) == 1) {
      header('Cache-Control: no-cache');
    }
    
    c::set('twig.root', realpath(dirname(__file__) . '/../templates'));
    c::set('twig.debug', $config->get('debug', 0) == 1);
    if(!is_dir(c::get('twig.root' . '/cache')) || !$config->get('templatecache', true)) {
      c::set('twig.cache', false);
    }
  }
  
  static function getInstance() {
    if(self::$instance === null) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  
  public function dispatch($uri) {
    require_once(dirname(__file__) . '/../config/urls.php');
    
    $uri = preg_replace('/\?.*$/', '', $uri);
    
    $responder_class = null;
    $params = array();
    foreach($urlpattern as $regex => $class) {
      if(preg_match($regex, $uri, $matches)) {
        $responder_class = $class;
        for($i = 1; $i < count($matches); $i++) { $params[] = $matches[$i]; }
        break;
      }
    }
    
    // If its not possible to determine which class to load, just do an 404
    if($responder_class == null) {
      $responder_class = 'Error404';
    }
    
    // If the defined class does not match PHP class guidelines throw an exception
    if(!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $responder_class)) {
      throw new DispatcherException('No valid responder defined for /' . $module . '/' . $action);
    }
    
    // If the class does not exist throw an exception
    if(class_exists($responder_class, true)) {
      $responder = new $responder_class();
      
      if(php_sapi_name() == 'cli') {
        $method = 'cli';
      } else {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
      }
      
      if(method_exists($responder, $method)) {
        $responder->$method($params);
      } else {
        throw new DispatcherException('Method ' . $method . ' is not valid for ' . $responder_class);
      }
    } else {
      throw new DispatcherException('Handler class ' . $responder_class . ' for uri ' . $uri . ' not found!');
    }
  }
  
}

class DispatcherException extends Exception {}