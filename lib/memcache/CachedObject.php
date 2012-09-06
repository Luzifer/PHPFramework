<?php

class CachedObject {
  protected $timeout = 86400;
  protected $cache_key = false;
  protected $original_values = array();
  protected $current_values = array();
  protected $changed_values = array();
  protected $ignored_values = array();
  protected $memcache = null;
  protected $db = null;
  protected $options = array();
  protected $code_version = 1;
  protected $delete = false;

  public function __construct($options = array()) {
    // it's ugly, but I have no other option
    global $config;
    $this->options = $options;

    $this->db = StaticMySQL::getInstance($config);

    $this->memcache = new MCache($config);
    $this->load();
  }

  public function __destruct() {
    $this->save();
  }

  protected function load($loadFromDB = true) {
    try {
      $this->loadFromCache();
    } catch(CacheNotFoundException $ex) {
      if(!isset($this->options['load_from_db']) || $this->options['load_from_db'] === true) {
        $this->loadFromDB();
        $this->original_values = $this->current_values;
      }
    }
    $this->current_values['cache_version'] = $this->code_version;
    $this->ignored_values[] = 'cache_version';
    $this->saveToCache();
  }

  public function loadFromDB() {
    throw new CacheNotImplementedException();
  }

  protected function loadFromCache() {
    $key = $this->getCacheKey();
    $data = $this->memcache->get($key);
    if($data === false) {
      throw new CacheNotFoundException; 
    } else {
      if(!is_array($data)) {
        throw new CacheNotFoundException;
      }

      if(!empty($data['cache_version']) && $data['cache_version'] < $this->code_version) {
        throw new CacheNotFoundException;
      }
      $this->original_values = $data;
      $this->current_values = $data;
      $this->ignored_values[] = 'cache_version';
    }
  }

  public function save() {
    $this->changed_vales = array();
    foreach($this->current_values as $key => $value) {
      if((!isset($this->original_values[$key]) && isset($this->current_values[$key])) || $value != $this->original_values[$key]) {
        if(!in_array($key, $this->ignored_values)) {
          $this->changed_values[$key] = $value;
        }
      }
    }

    if(count($this->changed_values) > 0 || $this->delete === true) {
      $this->saveToDB();
      $this->saveToCache();
      $this->original_values = $this->current_values;
      $this->changed_values = array();
    }
  }

  protected function saveToCache() {
    $this->memcache->set($this->getCacheKey(), $this->current_values, $this->timeout);
  }

  protected function getCacheKey() {
    return $this->getClass() . '('. $this->getId() .')';
  }

  protected function getClass() {
    throw new CacheNotImplementedException();
  }

  protected function getId() {
    throw new CacheNotImplementedException();
  }

  public function invalidate() {
    $this->memcache->delete($this->getCacheKey());
  }

  public function delete() {
    $this->delete = true;
  }

  public function set($key, $value) {
    $this->current_values[$key] = $value;
  }

  public function get($key, $default = false) {
    if(isset($this->current_values[$key])) {
      return $this->current_values[$key];
    } else {
      return $default;
    }
  }
}

class CacheNotImplementedException extends Exception {}
class CacheNotFoundException extends Exception {}
class CacheObjectNotFoundException extends Exception {}
