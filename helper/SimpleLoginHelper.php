<?php

class SimpleLoginHelper {
  
  private static $instance = null;
  private $user = null;
  private $systemname = 'PHPFrameworkLogin';
  
  private function __construct() {}
  
  public static function getInstance() {
    if(self::$instance == null) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }
  
  public function setSystemName($name) { $this->systemname = $name; }
  
  public function requireUser($user) {
    $this->fetchUser();
    if($this->user != $user) {
      $this->sendLogin();
    }
    return true;
  }
  
  public function requireGroup($group) {
    $this->fetchUser();
    $group_db = Config::getInstance()->getSection('user_groups');
    if(!array_key_exists($group, $group_db)) {
      throw new Exception('FATAL: Group ' . $group . ' has not been defined in settings.ini!');
    }
    $members = str::split($group_db[$group], ', ');
    if(a::contains($members, $this->user)) {
      return true;
    } else {
      $this->sendLogin();
    }
  }
  
  
  private function fetchUser() {
    if(array_key_exists('PHP_AUTH_USER', $_SERVER)) {
      $this->user = $_SERVER['PHP_AUTH_USER'];
      $this->checkLogin();
    } else {
      $this->user = null;
    }
  }
  
  private function checkLogin() {
    $user_db = Config::getInstance()->getSection('user_accounts');
    if(array_key_exists($this->user, $user_db)) {
      $pwd = $user_db[$this->user];
      if($pwd == sha1($_SERVER['PHP_AUTH_PW'])) {
        return true;
      } else {
        $this->sendLogin();
      }
    } else {
      $this->sendLogin();
    }
  }
  
  private function sendLogin() {
    header('WWW-Authenticate: Basic realm="' . $this->systemname . '"');
    header('HTTP/1.0 401 Unauthorized');
    die();
  }
  
}

