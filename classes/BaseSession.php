<?php

class BaseSession implements BaseSessionInterface {

  /**
   * @var BaseSessionInterface
   */
  private $session = null;

  /**
   * @param string $session_class
   * @throws BaseSessionException if session class is not defined
   * @throws BaseSessionException if session class is not a instance of BaseSessionInterface
   */
  public function __construct($session_class = '') {
    if(empty($session_class)) {
      try {
        $session_class = Config::get_instance()->get('session.class');
      } catch (ConfigException $e) {} 
    }

    if(!$session_class) {
      throw new BaseSessionException('Session class not defined');      
    }    
    
    if(class_exists($session_class, true)) {
      $this->session = new $session_class();
      if(!($this->session instanceof BaseSessionInterface)) {
        $this->session = null;
        throw new BaseSessionException($session_class . ' is not a instance of BaseSessionInterface');
      }
    } else {
      throw new BaseSessionException('Session class ' . $session_class . ' not found');
    }
  }

  /**
   * @param string $key
   * @param mixed $value
   * @return BaseSessionInterface
   */
  public function set($key, $value) {
    $key = (string)$key;

    return $this->session->set($key, $value); 
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function get($key) {
    $key = (string)$key;

    return $this->session->get($key);
  }

  /**
   * @param string $key
   * @return boolean
   */
  public function exist($key) {
    $key = (string)$key;

    return $this->session->exist($key);
  }

  /**
   * @param string $key
   * @return BaseSessionInterface
   */
  public function clear($key) {
    $key = (string)$key;    

    return $this->session->clear($key);
  }

  /**
   * @return BaseSessionInterface
   */
  public function clear_all() {
    return $this->session->clear_all();
  }
}
