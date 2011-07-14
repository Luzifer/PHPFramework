<?php

if(!c::get('twig.root')) c::set('twig.root', c::get('root') . '/templates');

class tpl {
	
	static public $vars = array();
  static public $filters = array();

  function add_filter($name, $method) {
    self::$filters[$name] = $method;
  }

	function set($key, $value=false) {
		if(is_array($key)) {
			self::$vars = array_merge(self::$vars, $key);
		} else {
			self::$vars[$key] = $value;
		}
	}

	function get($key=null, $default=null) {
		if($key===null) return (array)self::$vars;
		return a::get(self::$vars, $key, $default);				
	}

	function load($template='default', $vars=array(), $return=false) {		
    $file = c::get('twig.root') . '/' . $template . '.html';
    if(!file_exists($file)) return false;

    require_once(c::get('root') .'/plugins/Twig/Autoloader.php');
    Twig_Autoloader::register();
    $loader = new Twig_Loader_Filesystem(c::get('twig.root'));
    $twig = new Twig_Environment($loader, array(
      'cache' => c::get('twig.cache', c::get('twig.root') .'/cache'),
      'debug' => c::get('twig.debug', false),
    ));

    if(!empty(self::$filters)) {
      foreach(self::$filters as $key => $value) {
        if(is_array($value)) {
          $twig->addFilter($key, new Twig_Filter_Function($value[0] .'::'. $value[1]));
        } else {
          $twig->addFilter($key, new Twig_Filter_Function($value));
        }
      }
    }
    $template = $twig->loadTemplate($template .'.html');
    $template->display(array_merge(self::$vars, $vars));
	}

}
