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
   * @return mixed
   * @throws UndifinedIndexException
   */
  public function get($key);

  /**
   * @param string $key
   * @return boolean
   */
  public function exist($key);

  /**
   * @param string $key
   * @return BaseSessionInterface
   * @throws UndifinedIndexException
   */
  public function clear($key);

  /**
   * @return BaseSessionInterface
   */
  public function clear_all();
}

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

class BaseSessionException extends Exception {}

class UndifinedIndexException extends Exception {
  
  /**
   * @param string $index
   */  
  public function __construct($index) {
    parent::__construct('Undifined index ' . $index);
  }
}
