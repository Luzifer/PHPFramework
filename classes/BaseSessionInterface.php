<?php

interface BaseSessionInterface {

  /**
   * @param IConfigReader $config
   */
  public function __construct($config);

  /**
   * @param string $key
   * @param mixed $value 
   * @return BaseSessionInterface
   */
  public function set($key, $value);

  /**
   * @param string $key
   * @param mixed $default Default value to return when key is not found
   * @return mixed
   */
  public function get($key, $default = null);

  /**
   * @param string $key
   * @return boolean
   */
  public function exist($key);

  /**
   * @param string $key
   * @return BaseSessionInterface
   * @throws BaseSessionUndefinedIndexException
   */
  public function clear($key);

  /**
   * @return BaseSessionInterface
   */
  public function clear_all();
}

class BaseSessionException extends Exception {}

class BaseSessionUndefinedIndexException extends Exception {

  /**
   * @param string $index
   */
  public function __construct($index) {
    parent::__construct('Undefined session index ' . $index);
  }
}

