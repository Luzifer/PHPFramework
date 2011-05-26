<?php

class ErrorHandler {
  
  private function design_error($exception) {

      // these are our templates
      $traceline = "#%s %s(%s): %s(%s)";
      $msg = "Uncaught exception '%s' with message '%s' <br />in %s:%s";
      $trace_s = "Stack trace:\n%s\n  thrown in %s on line %s";

      // alter your trace as you please, here
      $trace = $exception->getTrace();
      foreach ($trace as $key => $stackPoint) {
          // I'm converting arguments to their type
          // (prevents passwords from ever getting logged as anything other than 'string')
          $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
      }

      // build your tracelines
      $result = array();
      foreach ($trace as $key => $stackPoint) {
          $result[] = sprintf(
              $traceline,
              $key,
              $stackPoint['file'],
              $stackPoint['line'],
              $stackPoint['function'],
              implode(', ', $stackPoint['args'])
          );
      }
      // trace always ends with {main}
      $result[] = '#' . ++$key . ' {main}';

      // write tracelines into main template
      $msg = sprintf(
          $msg,
          get_class($exception),
          $exception->getMessage(),
          $exception->getFile(),
          $exception->getLine()
      );
      $trace = sprintf(
          $trace_s,
          implode("\n", $result),
          $exception->getFile(),
          $exception->getLine()
      );

      // log or echo as you please
      return array(
        'msg' => $msg,
        'trace' => $trace
      );
  }
  
  static function handle_error($e) {
    header('HTTP/1.1 500 Internal Server Error');
    $exception = self::design_error($e);
    include(dirname(__file__) . '/../templates/internal/error.php');
  }
  
}
