<?php

/**
  Implements a accessor to the settings.ini configuration
  file. The file is parsed according to the currently set
  apache environment to support for example different database
  configurations for live-, development- and testingserver.
  
  @author Knut Ahlers <knut@ahlers.me>
*/
class Config {
  private static $instance = null;
  private static $config = null;
  private static $env = null;
  
  private function __construct() {
    $this->config = parse_ini_file(dirname(__file__) . '/../config/settings.ini', true);
    $this->env = apache_getenv('APPLICATION_ENV');
  }
  
  /**
    Retrieve an instance of the configuration class with loaded
    configuration file stored to memory to prevent multiple access
    to the settings file.
    
    @returns Singleton instance of Config class
  */
  public static function getInstance() {
    if(self::$instance == null) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  
  /**
    Configuration is read from the current environment section, if not
    existent from the general config and if not present in the whole 
    config from the passed default value.
    
    @param varname (string) The key to retrieve from the settings file
    @param default (string) A default value to return when the setting is not present
    @returns The configuration value for the key passed. 
  */
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
  
  /**
    Accessor for the settings file to retrieve a section defined
    outside of the standard config section
    
    @param section (string) Name of the section to return
    @returns Array of key-value pairs in the passed section
    @throws ConfigException when section has not been found
  */
  public function getSection($section) {
    if(array_key_exists($section, $this->config)) {
      return $this->config[$section];
    } else {
      throw new ConfigException('Config section \'' . $section . '\' not found.');
    }
  }
  
}

class ConfigException extends Exception {}