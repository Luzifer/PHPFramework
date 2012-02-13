<?php

class BaseAutoLoader {
  private static $base_autoloader_app_path = null;
  private static $base_autoloader_library_paths = array();

  public static function register_app_path($app_path) {
    self::$base_autoloader_app_path = $app_path;
  }

  public static function register_library_path($library_path) {
    if(!in_array($library_path, self::$base_autoloader_library_paths)) {
      self::$base_autoloader_library_paths[] = $library_path;
    }
  }
  
  public static function auto_load($class) {
    $class_parts = preg_split('/([[:upper:]][[:lower:]]+)/', $class, null, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

    $chances = array();

    // Base-Classes are expected to be defined in a file with the class name
    // in the classes directory of the framework. Nowhere else!
    if(in_array($class_parts[0], array('Base', 'Config'))) {
      $chances[] = dirname(__FILE__) . '/' . $class . '.php';
    }

    if(self::$base_autoloader_app_path !== null) {
      // Try to assemble a path from the app-path and the class paths
      $chances[] = rtrim(self::$base_autoloader_app_path, '/') . '/' . join('/', $class_parts) . '.php';

      // Try the assembled stuff in lowercase
      $chances[] = rtrim(self::$base_autoloader_app_path, '/') . '/' . strtolower(join('/', $class_parts)) . '.php';

      // There might be four more choises to check for the class file
      $parts = array();
      for($i = 0; $i < count($class_parts) - 1; $i++) {
        $parts[] = $class_parts[$i];
      }
      $path = join('/', $parts);

      $chances[] = rtrim(self::$base_autoloader_app_path, '/') . '/' . $path . '/' . $class . '.php';
      $chances[] = rtrim(self::$base_autoloader_app_path, '/') . '/' . strtolower($path) . '/' . $class . '.php';
      $chances[] = rtrim(self::$base_autoloader_app_path, '/') . '/' . $path . '/' . strtolower($class) . '.php';
      $chances[] = rtrim(self::$base_autoloader_app_path, '/') . '/' . strtolower($path) . '/' . strtolower($class) . '.php';
    }

    // If there are library paths the class could be hidden there
    if(count(self::$base_autoloader_library_paths) > 0) {
      foreach(self::$base_autoloader_library_paths as $library_path) {
        $chances[] = rtrim($library_path, '/') . '/' . $class . '.php';
      }
    }


    foreach($chances as $file) {
      if(file_exists($file)) {
        include($file);
        return;
      }
    }
  }
  
}
