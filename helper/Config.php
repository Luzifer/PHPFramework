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

/**
 * Implements a accessor to the settings.ini configuration
 * file. The file is parsed according to the currently set
 * apache environment to support for example different database
 * configurations for live-, development- and testingserver.
 * 
 * @author Knut Ahlers <knut@ahlers.me>
 */
class Config {
  private static $instance = null;
  private $config = null;
  private $env = null;
  
  private function __construct() {
    if(file_exists(dirname(__file__) . '/../config/settings_local.ini')) {
      $this->config = parse_ini_file(dirname(__file__) . '/../config/settings_local.ini', true);
    } elseif(file_exists(dirname(__file__) . '/../../private/config/settings_local.ini')) {
      $this->config = parse_ini_file(dirname(__file__) . '/../../private/config/settings_local.ini', true);
    } else {
      throw new ConfigException('Configuration file config/settings_local.ini was not found.');
    }
    if(php_sapi_name() == 'cli') {
      $this->env = 'cli';
    } else {
      $this->env = 'webserver';
    }
  }
  
  /**
   * Retrieve an instance of the configuration class with loaded
   * configuration file stored to memory to prevent multiple access
   * to the settings file.
   * 
   * @return Config Singleton instance of Config class
   */
  public static function getInstance() {
    if(self::$instance == null) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  
  /**
   * Configuration is read from the current environment section, if not
   * existent from the general config and if not present in the whole 
   * config from the passed default value.
   * 
   * @param string $varname The key to retrieve from the settings file
   * @param string $default A default value to return when the setting is not present
   * @return mixed The configuration value for the key passed. 
   */
  public function get($varname, $default = null) {
    $section = "config_" . $this->env;
    $value = $default;
    
    if(!array_key_exists($section, $this->config)) {
      $section = 'config';
    }
    
    if(array_key_exists($varname, $this->config[$section])) {
      $value = $this->config[$section][$varname];
    } elseif(array_key_exists($varname, $this->config['config'])) {
      $value = $this->config['config'][$varname];
    }
    
    return $value;
  }
  
  /**
   * Accessor for the settings file to retrieve a section defined
   * outside of the standard config section
   * 
   * @param string $section Name of the section to return
   * @return array Key-value pairs in the passed section
   * @throws ConfigException when section has not been found
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
