<?php

class BasePhpSession implements BaseSessionInterface {

  /**
   * @param boolean $testing 
   */
  public function __construct($testing = false) {
    if(!$testing && !isset($_COOKIE[ini_get('session.name')])) {
      session_start();
    }
  }

  /**
   * @param string $key
   * @param midex $value
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
   * @throws UndifinedIndexException if $key not in $_SESSION
   */
  public function get($key) {
    $key = (string)$key;

    if($this->exist($key)) {
      return $_SESSION[$key];
    }

    throw new UndifinedIndexException($key);
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
   * @throws UndifinedIndexException if $key not in $_SESSION
   */ 
  public function clear($key) {
    $key = (string)$key;

    if($this->exist($key)) {
      unset($_SESSION[$key]);
      return $this;
    }

    throw new UndifinedIndexException($key);
  }

  /**
   * @return BasePhpSession
   */
  public function clear_all() {
    session_unset();
    return $this; 
  }
}
