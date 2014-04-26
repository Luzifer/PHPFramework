<?php
/**
 * Created by IntelliJ IDEA.
 * User: luzifer
 * Date: 26.04.14
 * Time: 14:16
 */

class HerokuConfigStore implements IConfigReader {

  /**
   * @var IConfigReader
   */
  private $origin_reader = null;

  /**
   * @param IConfigReader $config_reader Filesystem config reader
   */
  public function __construct($config_reader) {
    $this->origin_reader = $config_reader;
  }

  public function get($config_key, $default = null) {
    if(array_key_exists($config_key, $_ENV)) {
      return $_ENV[$config_key];
    } else {
      return $this->origin_reader->get($config_key, $default);
    }
  }

  public function getSection($config_section_name) {
    $this->origin_reader->getSection($config_section_name);
  }

  public function set($config_key, $config_value) {
    if(array_key_exists($config_key, $_ENV)) {
      throw new HerokuConfigStoreException($config_key . ' is a variable from the read-only-storage at Heroku');
    } else {
      $this->origin_reader->set($config_key, $config_value);
    }
  }
}

class HerokuConfigStoreException extends Exception {}