<?php
interface BaseSessionInterface {
  
  /**
   * 
   * @param string $key
   * @param mixed $value
   * @return BaseSessionInterface
   */
  public function set($key, $value);

  /**
   * 
   * @param string $key
   * @throws BaseSessionUndifinedException
   * @return mixed
   */
  public function get($key);

  /**
   * 
   * @param string $key
   * @return boolean
   */
  public function exist($key);

  /**
   * @param string $key
   * @throws BaseSessionUndifinedException 
   * @return BaseSessionInterface
   */
  public function clear($key);

  /**
   * @return BaseSessionInterface
   */
  public function clear_all();
}

class BaseSession implements BaseSessionInterface {

  private $session = null;

  public function __construct() {
    $session_class = Config::getInterface('session.class');
    if(!$session_class) {
      
    }    
  }
  
  public function set($key, $values) {
 
  }

  public function get($key) {

  }

  public function exist($key) {

  }

  public function clear($key) {

  }

  public function clear_all() {

  }
}

class BaseSessionException extends Exception {}

class BaseSessionUndifinedException extends BaseSessionException {}
