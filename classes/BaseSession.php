<?php
interface BaseSessionInterface {
  
  /**
   * @param string $key
   * @param mixed $value
   * @return BaseSessionInterface
   */
  public function set($key, $value);

  /**
   * @param string $key
   * @throws BaseSessionUndifinedException
   * @return mixed
   */
  public function get($key);

  /**
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

  /**
   * 
   * @var BaseSessionInterface
   */
  private $session = null;

  /**
   * 
   * @param string $session_class
   * @throws BaseSessionException if session class is not defined
   * @throws BaseSessionException if session class is not a instance of BaseSessionInterface
   */
  public function __construct($session_class = '') {
    if(empty($session_class)) {
      // @todo Install the config class
      // $session_class = Config::getInstance()->get('session.class');
    }

    if(!$session_class) {
      throw new BaseSessionException('Session class is not defined');      
    }    
    
    $this->session = new $session_class();
    if(!($this->session instanceof BaseSessionInterface)) {
      $this->sesion = null;
      throw new BaseSessionException($session_class . ' is not a instance of BaseSessionInterface');
    }
  }

  public function set($key, $value) {
    $key = (string)$key;

    return $this->session->set($key, $value); 
  }

  public function get($key) {
    $key = (string)$key;

    return $this->session->get($key);
  }

  public function exist($key) {
    $key = (string)$key;

    return $this->session->exist($key);
  }

  public function clear($key) {
    $key = (string)$key;    

    return $this->session->clear($key);
  }

  public function clear_all() {
    return $this->session->clear_all();
  }
}

class BaseSessionException extends Exception {}

class UndifinedIndexException extends Exception {
  
  public function __construct($key) {
    parent::__construct('Undifined index ' .$key);
  }
}
