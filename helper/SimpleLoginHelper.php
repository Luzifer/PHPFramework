<?php

/*
 * Copyright (c) 2011 Knut Ahlers <knut@ahlers.me>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Implements a very basic authentication mechanism based on the 
 * setting.ini file. A simple mapping of users (and their password
 * hashs) to groups is supported. Also mapping multiple groups into
 * a greater one is possible.
 * 
 * @author Knut Ahlers <knut@ahlers.me>
 */
class SimpleLoginHelper {
  
  private static $instance = null;
  private $user = null;
  private $systemname = 'PHPFrameworkLogin';
  
  private function __construct() {}
  
  /**
   * Here a singleton is implemented to avoid using multiple concurrent login handlers.
   * 
   * @return SimpleLoginHelper Singleton instance of SimpleLoginHelper
   */
  public static function getInstance() {
    if(self::$instance == null) {
      self::$instance = new self;
    }
    
    return self::$instance;
  }
  
  /**
   * Returns the current logged in user or null when no user is
   * discovered. 
   *
   * Attention: This method will return null until any user or
   * group is required using the functions requireUser or reuqireGroup!
   *
   * @return string Username of the current logged in user
   */
  public function getUser() { return $this->user; }
  
  /**
   * Sets the name of the login realm used in HTTP-Auth dialog
   * 
   * @param string $name Text to identify the secured section of the website
   */
  public function setSystemName($name) { $this->systemname = $name; }
  
  /**
   * Checks the logged in user and invokes a login dialog when the
   * user does not match the passed username. A check whether the 
   * user exists in config is NOT made.
   * 
   * @param string $user Name of the required user (Same as in settings.ini)
   */
  public function requireUser($user) {
    $this->fetchUser();
    if($this->user != $user) {
      $this->sendLogin();
    }
    return true;
  }
  

  /**
   * Same as {@link inGroup} except if the user is not member of the group a 
   * login dialog will be invoked.
   * 
   * @param string $group Name of the group
   */
  public function requireGroup($group) {
    $this->fetchUser();
    if($this->inGroup($group)) {
      return true;
    } else {
      $this->sendLogin();
    }
  }
  
  /**
   * Checks whether the current user is member of the passed group. 
   * 
   * @param string $group Name of the group which is checked for the membership of the current user
   * @throws Exception If the group is not existent in settings.ini an exception will be raised.
   */
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
    // Two hacks for PHP running in CGI mode
    if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
      list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    }
    
    if(array_key_exists('HTTP_AUTHORIZATION', $_GET)) {
      list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_GET['HTTP_AUTHORIZATION'], 6)));
    }
    
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

