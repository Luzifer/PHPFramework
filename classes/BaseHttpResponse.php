<?php

require_once(dirname(__FILE__) . '/../lib/Twig/Autoloader.php');

class BaseHttpResponse {
  
  private $template_vars = array();
  private $headers = array();
  private $config = null;
  private $template_directory = null;

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
    $template = $this->get_template_environment($template_name);
    $template->display($this->template_vars);
  }

  private function get_template_environment($template_name) {
    Twig_Autoloader::register();
    $loader = new Twig_Loader_Filesystem($this->template_directory);
    $twig = new Twig_Environment($loader);
    $template = $twig->loadTemplate($template_name . '.html');

    return $template;
  }
  
}
