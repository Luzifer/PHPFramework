<?php

class BaseAutoLoader {
  
  public static function auto_load($class) {
    $chances = array(
      dirname(__file__) . '/../handlers/',
      dirname(__file__) . '/../classes/',
      dirname(__file__) . '/../helper/'
    );
    if(file_exists(dirname(__FILE__) . '/../../private/handlers/')) {
      array_unshift($chances, dirname(__FILE__) . '/../../private/handlers/');
    }

    foreach($chances as $chance){
      $cpaths = array(
        $chance . strtolower($class) . '.php',
        $chance . $class . '.php'
      );
      foreach($cpaths as $cpath){
        if(file_exists($cpath)) {
          include($cpath);
          return;
        }
      }
    }
  }
  
}
