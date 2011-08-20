<?php

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