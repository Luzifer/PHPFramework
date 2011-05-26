<?php

class Config {
  private static $instance = null;
  private static $config = null;
  private static $env = null;
  
  private function __construct() {
    $this->config = parse_ini_file(dirname(__file__) . '/../config/settings.ini', true);
    $this->env = apache_getenv('APPLICATION_ENV');
  }
  
  public static function getInstance() {
    if(self::$instance == null) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  
  public function get($varname, $default = null) {
    $section = "config_" . $this->env;
    $value = $default;
    
    if(array_key_exists($varname, $this->config[$section])) {
      $value = $this->config[$section][$varname];
    } elseif(array_key_exists($varname, $this->config['config'])) {
      $value = $this->config['config'][$varname];
    }
    
    return $value;
  }
  
  public function getSection($section) {
    if(array_key_exists($section, $this->config)) {
      return $this->config[$section];
    } else {
      throw new ConfigException('Config section \'' . $section . '\' not found.');
    }
  }
  
}

class ConfigException extends Exception {}