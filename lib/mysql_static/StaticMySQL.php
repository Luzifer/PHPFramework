<?php

/**
 * MySQL database wrapper class
 */
class StaticMySQL {

  private static $instance;

  public static function getInstance($config, $connection_target = 'default') { 
    if(!self::$instance) {
      self::$instance = new MySQL($config, $connection_target);
      self::$instance->connect();
    }
    return self::$instance; 
  }  
}
