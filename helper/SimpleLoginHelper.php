<?php

class SimpleLoginHelper {
  
  private static $instance = null;
  private $user = null;
  private $systemname = 'PHPFrameworkLogin';
  
  private function __construct() {}
  
  // Return an instance of the login handler. Here a singleton is
  // implemented to avoid using multiple concurrent login handlers.
  public static function getInstance() {
    if(self::$instance == null) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }
  
  // Returns the current logged in user or null when no user is
  // discovered. 
  // 
  // Attention: This method will return null until any user or
  // group is required using the functions requireUser or reuqireGroup!
  public function getUser() { return $this->user; }
  
  // Sets the name of the login realm used in HTTP-Auth dialog
  public function setSystemName($name) { $this->systemname = $name; }
  
  // Checks the logged in user and invokes a login dialog when the
  // user does not match the passed username. A check whether the 
  // user exists in config is NOT made.
  public function requireUser($user) {
    $this->fetchUser();
    if($this->user != $user) {
      $this->sendLogin();
    }
    return true;
  }
  

  // Same as inGroup except if the user is not member of the group a 
  // login dialog will be invoked.
  public function requireGroup($group) {
    $this->fetchUser();
    if($this->inGroup($group)) {
      return true;
    } else {
      $this->sendLogin();
    }
  }
  
  // Checks whether the current user is member of the passed group. If
  // the group is not existent in settings.ini an exception will be raised.
  public function inGroup($group) {
    if($this->user === null) {
      return false;
    }
    
    $members = $this->getGroupMembers($group);
    
    if(a::contains($members, $this->user)) {
      return true;
    } else {
      return false;
    }
  }
  
  
  
  private function getGroupMembers($group) {
    $group_db = Config::getInstance()->getSection('user_groups');
    if(!array_key_exists($group, $group_db)) {
      throw new Exception('FATAL: Group ' . $group . ' has not been defined in settings.ini!');
    }
    $members = array();
    $members_group = str::split($group_db[$group], ', ');
    foreach($members_group as $member) {
      if(preg_match('/^@/', $member)) {
        $submembers = $this->getGroupMembers(preg_replace('/^@/', '', $member));
        foreach($submembers as $m) {
          $members[] = $m;
        }
      } else {
        $members[] = $member;
      }
    }
    
    return $members;
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

