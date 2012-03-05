<?php

class BasePhpSession implements BaseSessionInterface {

  /**
   * @param boolean $testing for phpunit
   */
  public function __construct($testing = false) {
    if(!$testing && !isset($_COOKIE[session_name()])) {
      session_start();
    }
  }

  /**
   * @param string $key
   * @param mixed $value
   * @return BasePhpSession
   */
  public function set($key, $value) {
    $key = (string)$key;
    $_SESSION[$key] = $value;

    return $this; 
  }

  /**
   * @param string $key
   * @return mixed
   * @throws BaseSessionUndefinedIndexException if $key not in $_SESSION
   */
  public function get($key) {
    $key = (string)$key;

    if($this->exist($key)) {
      return $_SESSION[$key];
    }

    throw new BaseSessionUndefinedIndexException($key);
  }

  /**
   * @param string $key
   * @return boolean
   */
  public function exist($key) {
    $key = (string)$key;
    return isset($_SESSION[$key]);
  }

  /**
   * @param string $key
   * @return BasePhpSession
   * @throws BaseSessionUndefinedIndexException if $key not in $_SESSION
   */ 
  public function clear($key) {
    $key = (string)$key;

    if($this->exist($key)) {
      unset($_SESSION[$key]);
      return $this;
    }

    throw new BaseSessionUndefinedIndexException($key);
  }

  /**
   * @return BasePhpSession
   */
  public function clear_all() {
    session_unset();
    return $this; 
  }
}
