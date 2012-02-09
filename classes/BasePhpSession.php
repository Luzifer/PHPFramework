<?php

class BasePhpSession implements BaseSessionInterface {

  /**
   * 
   */
  public function __construct() {
    if(!isset($_COOKIE[ini_get('session.name')])) {
      session_start();
    }
  }

  public function set($key, $value) {
    $key = (string)$key;
    $_SESSION[$key] = $value;

    return $this; 
  }

  public function get($key) {
    $key = (string)$key;

    if($this->exist($key)) {
      return $_SESSION[$key];
    }

    throw new UndifinedIndexException($key);
  }

  public function exist($key) {
    $key = (string)$key;
    return isset($_SESSION[$key]);
  }

  public function clear($key) {
    $key = (string)$key;

    if($this->exist($key)) {
      unset($_SESSION[$key]);
      return $this;
    }

    throw new UndifinedIndexException($key);
  }

  public function clear_all() {
    session_unset();
    return $this; 
  }
}
