<?php

class ConfigIni implements IConfigReader {
  private $config = null;

  /**
   * @param string$config_file_path Path to the INI file to load for config
   */
  public function __construct($config_file_path) {
    if(!file_exists($config_file_path)) {
      throw new ConfigIniFileNotFoundException('Config file ' . $config_file_path . ' was not found.');
    }
    $this->config = parse_ini_file($config_file_path, true);
  }

  /**
   * Reads a config value from the configuration previously loaded if possible
   *
   * @param string $config_key Key to search for
   * @param mixed $default Return value if the $config_key was not found in "config" section
   * @return mixed
   * @throws ConfigIniSectionNotFoundException when section "config" does not exist
   */
  public function get($config_key, $default = null) {
    if(!array_key_exists('config', $this->config)) {
      throw new ConfigIniSectionNotFoundException('Default config section "config" was not found.');
    }

    if(array_key_exists($config_key, $this->config['config'])) {
      return $this->config['config'][$config_key];
    }

    return $default;
  }

  /**
   * @param string $config_section_name Name of the section to return
   * @return array
   * @throws ConfigIniSectionNotFoundException when section $config_section_name does not exist
   */
  public function getSection($config_section_name) {
    if(array_key_exists($config_section_name, $this->config)) {
      return $this->config[$config_section_name];
    } else {
      throw new ConfigIniSectionNotFoundException('Config section \'' . $config_section_name . '\' not found.');
    }
  }

}

class ConfigIniFileNotFoundException extends Exception {}
class ConfigIniSectionNotFoundException extends Exception {}
