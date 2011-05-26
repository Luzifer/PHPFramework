<?php

class AutoLoader {
  
  static function auto_load($class) {
    $chances = array(
      dirname(__file__) . '/../responder/',
      dirname(__file__) . '/../classes/',
      dirname(__file__) . '/../helper/'
    );

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
