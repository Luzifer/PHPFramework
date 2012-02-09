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
   * @throws BaseSessionUndifinedIndexException
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
   * @throws BaseSessionUndifinedIndexException
   */
  public function clear($key);

  /**
   * @return BaseSessionInterface
   */
  public function clear_all();
}

class BaseSessionException extends Exception {}

class BaseSessionUndifinedIndexException extends Exception {

  /**
   * @param string $index
   */
  public function __construct($index) {
    parent::__construct('Undifined session index ' . $index);
  }
}

