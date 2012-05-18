<?php

require_once(dirname(__FILE__) . '/../lib/Twig/Autoloader.php');

class BaseExceptionVisualizer {
  private static $display_template = null;

  /**
   * Sets the path to the template used to render uncaught exceptions
   *
   * @static
   * @param string $template_path Full path to the template for rendering exceptions
   */
  public static function set_display_template($template_path) {
    self::$display_template = $template_path;
  }

  /**
   * Returns the current used template path or null when no template path is set
   *
   * @static
   * @return string|null
   */
  public static function get_display_template() {
    return self::$display_template;
  }

  /**
   * Renders the exception display and puts it to stdout
   *
   * @static
   * @param Exception $exception The exception which occured
   */
  public static function render_exception($exception) {
    if(!headers_sent()) {
      header('HTTP/1.1 500 Internal Server Error');
      header('Cache-Control: no-cache');
    }
    $template_vars = self::generate_template_vars($exception);

    if(self::$display_template !== null) {
      Twig_Autoloader::register();

      $loader = new Twig_Loader_Filesystem(dirname(self::$display_template));
      $twig = new Twig_Environment($loader);
      $template = $twig->loadTemplate(basename(self::$display_template));
      $template->display($template_vars);
    } else {
      var_dump($template_vars);
    }
  }

  /**
   * @static
   * @param Exception $exception
   * @return array
   */
  private static function generate_template_vars($exception) {
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
        (array_key_exists('file', $stackPoint) ? $stackPoint['file'] : 'undefined'),
        (array_key_exists('line', $stackPoint) ? $stackPoint['line'] : 'undefined'),
        (array_key_exists('function', $stackPoint) ? $stackPoint['function'] : 'undefined'),
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
      'message' => $msg,
      'stacktrace' => $trace
    );
  }
}
