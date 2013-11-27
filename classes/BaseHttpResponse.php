<?php

require_once(dirname(__FILE__) . '/../lib/twig/lib/Twig/Autoloader.php');

class BaseHttpResponse {
  
  private $template_vars = array();
  private $headers = array();
  private $config = null;
  private $template_directory = null;
  private $output_filters = array();
  private $output_functions = array();

  public function __construct($config, $template_directory) {
    $this->config = $config;
    $this->template_directory = $template_directory;
  }
  
  /**
   * Sets a header to the specified value for delivery when the page is rendered
   * 
   * @param string $header_name Name of the header not including the colon
   * @param string $header_value Values of the header to send
   */
  public function header($header_name, $header_value) {
    $this->headers[$header_name] = $header_value;
  }
  
  /**
   * Returns the value of a template value previously set
   * 
   * @param string $template_variable_name Name of the template variable
   * @param mixed $default_value Value to be returned when the template variable was not set previously
   * @return mixed
   */
  public function get($template_variable_name, $default_value = null) {
    if(array_key_exists($template_variable_name, $this->template_vars)) {
      return $this->template_vars[$template_variable_name];
    }
    return $default_value;
  }
  
  /**
   * Sets a template value for later use in twig template while rendering
   * 
   * @param string $template_variable_name Name of the template variable
   * @param mixed $template_variable_value Value of the template variable to set to
   */
  public function set($template_variable_name, $template_variable_value) {
    $this->template_vars[$template_variable_name] = $template_variable_value;
  }

  /**
   * Renders the template with the previously defined variables and returns the rendered version
   *
   * @param string $template_name Name of the template in the template directory without extension
   * @return string
   */
  public function render($template_name) {
    $template = $this->get_template_environment($template_name);
    return $template->render($this->template_vars);
  }

  /**
   * Renders the template with the previously defined variables and sends the result to stdout
   *
   * @param string $template_name Name of the template in the template directory without extension
   */
  public function display($template_name) {
    $this->send_headers();
    $template = $this->get_template_environment($template_name);
    $template->display($this->template_vars);
  }

  /**
   * Sends the headers if not already done and puts the content
   * to output stream
   *
   * @param string $content Content to send to browser
   */
  public function write($content) {
    if(!headers_sent()) {
      $this->send_headers();
    }

    echo $content;
  }

  /**
   * Sends an json encoded object to the browser using correct content type
   *
   * @param mixed $object Object (most likely an array) to json encode
   * @param null|string $callback If set to string answer will be sent as JSONP output with this function
   */
  public function json_output($object, $callback = null) {
    if($callback !== null) {
      $ctype = 'text/javascript';
      $output = $callback . '(' . json_encode($object) . ');';
    } else {
      $ctype = 'application/json';
      $output = json_encode($object);
    }
    $this->header('Content-Type', $ctype);
    $this->send_headers();

    die($output);
  }

  /**
   * Sets the location header including the HTTP status header for redirects
   *
   * @param string $target The target to use in location header
   * @param int $http_code The HTTP code to use (301 = Moved Permanent, 302 = Moved Temporary, 303 = See Other)
   */
  public function redirect($target, $http_code = 302) {
    $this->header('Location', $target);
    switch($http_code) {
      case 301:
        header("HTTP/1.1 301 Moved Permanently");
        break;
      case 302:
        header("HTTP/1.1 302 Moved Temporarily");
        break;
      case 303:
        header("HTTP/1.1 303 See Other");
        break;
    }
    $this->send_headers();
  }

  /**
   * Adds a filter to use in the template
   *
   * @param $name string Name of the filter to use in the template
   * @param $function string Name of the function to execute for the value from the template
   */
  public function add_output_filter($name, $function) {
    $this->output_filters[$name] = $function;
  }

  /**
   * Adds a function to use in the template
   *
   * @param $name string Name of the function to use in the template
   * @param $function string Name of the function to execute for the value from the template
   */
  public function add_output_function($name, $function) {
    $this->output_functions[$name] = $function;
  }

  private function send_headers() {
    foreach($this->headers as $header => $value) {
      header($header . ': ' . $value);
    }
  }

  private function get_template_environment($template_name) {
    Twig_Autoloader::register();
    $loader = new Twig_Loader_Filesystem($this->template_directory);
    $twig = new Twig_Environment($loader);

    if(!empty($this->output_functions)) {
      foreach($this->output_functions as $key => $value) {
        if(is_array($value)) {
          $twig->addFunction($key, new Twig_Function_Function($value[0] .'::'. $value[1]));
        } else {
          $twig->addFunction($key, new Twig_Function_Function($value));
        }
      }
    }

    if(!empty($this->output_filters)) {
      foreach($this->output_filters as $key => $value) {
        if(is_array($value)) {
          $twig->addFilter($key, new Twig_Filter_Function($value[0] .'::'. $value[1]));
        } else {
          $twig->addFilter($key, new Twig_Filter_Function($value));
        }
      }
    }

    $template = $twig->loadTemplate($template_name . '.html');

    return $template;
  }
  
}
