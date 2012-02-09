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

  public function set($key, $value) {
    return $this->session->set($key, $value); 
  }

  public function get($key) {
    return $this->session->get($key);
  }

  public function exist($key) {
    return $this->session->exist($key);
  }

  public function clear($key) {
    return $this->session->clear($key);
  }

  public function clear_all() {
    return $this->session->clear_all();
  }
}

class BaseSessionException extends Exception {}

class BaseSessionUndifinedException extends BaseSessionException {}
