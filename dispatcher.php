<?php

require_once(dirname(__FILE__) . '/classes/BaseAutoloader.php');

/**
 * Main entrance class for the framework / application
 * 
 * @author Knut Ahlers
 */
class Dispatcher {
  private $config = null;
  private $application_directory = null;

  public function __construct($config, $application_directory) {
    if(BaseExceptionVisualizer::get_display_template() === null) {
      BaseExceptionVisualizer::set_display_template(dirname(__FILE__) . '/resources/exception_template.html');
    }
    set_exception_handler('BaseExceptionVisualizer::render_exception');

    $this->config = $config;
    $this->application_directory = realpath($application_directory);

    if(!is_dir($this->application_directory)) {
      throw new ApplicationPartMissingException('Application directory "' . $application_directory . '" does not exist.');
    }

    $routefile = rtrim($this->application_directory, '/') . '/routes.php';
    if(!file_exists($routefile)) {
      throw new ApplicationPartMissingException('Routes file "' . $routefile . '" does not exist.');
    }

    $template_dir = rtrim($this->application_directory, '/') . '/templates/';
    if(!is_dir($template_dir)) {
      throw new ApplicationPartMissingException('Template directory "' . $template_dir . '" does not exist.');
    }

    BaseAutoLoader::register_app_path($application_directory);


/*
    if(is_dir(realpath(dirname(__FILE__) . '/../../private/templates'))) {
      c::set('twig.root', realpath(dirname(__FILE__) . '/../../private/templates'));
    } else {
      c::set('twig.root', realpath(dirname(__FILE__) . '/../templates'));
    }
    c::set('twig.debug', $config->get('debug', 0) == 1);
    if(!is_dir(c::get('twig.root' . '/cache')) || !$config->get('templatecache', true)) {
      c::set('twig.cache', false);
    }
*/
  }

  public function dispatch($uri) {
    $routes = array();
    require_once(rtrim($this->application_directory, '/') . '/routes.php');
    
    $uri = preg_replace('/\?.*$/', '', $uri);
    
    $responder_class = null;
    $params = array();
    foreach($routes as $regex => $class) {
      if(preg_match($regex, $uri, $matches)) {
        $responder_class = $class;
        for($i = 1; $i < count($matches); $i++) { $params[] = $matches[$i]; }
        break;
      }
    }
    
    // If the defined class does not match PHP class guidelines throw an exception
    if(!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $responder_class)) {
      throw new InvalidHttpResponderException('Responder class does not match PHP class naming conventions');
    }
    
    // If the class does not exist throw an exception
    if(class_exists($responder_class, true)) {
      if(php_sapi_name() == 'cli') {
        $method = 'cli';
      } else {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
      }

      $responder = new $responder_class(
          new BaseHttpRequest($method)
        , new BaseHttpResponse($this->config, rtrim($this->application_directory, '/') . '/templates/')
        , $this->config
      );
      
      if(method_exists($responder, $method)) {
        $responder->$method($params);
      } else {
        throw new InvalidHttpResponderException('Method ' . $method . ' is not valid for ' . $responder_class);
      }
    } else {
      throw new InvalidHttpResponderException('Handler class ' . $responder_class . ' for uri ' . $uri . ' not found!');
    }
  }
  
}

class ApplicationPartMissingException extends Exception {}
class InvalidHttpResponderException extends Exception {}
