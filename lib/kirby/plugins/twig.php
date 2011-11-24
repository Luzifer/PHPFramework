<?php

if(!c::get('twig.root')) c::set('twig.root', c::get('root') . '/templates');

class tpl {
	
	static public $vars = array();
  static public $filters = array();
  static public $functions = array();

  public static function add_function($name, $method) {
    self::$functions[$name] = $method;
  }

  public static function add_filter($name, $method) {
    self::$filters[$name] = $method;
  }

	public static function set($key, $value=false) {
		if(is_array($key)) {
			self::$vars = array_merge(self::$vars, $key);
		} else {
			self::$vars[$key] = $value;
		}
	}

	public static function get($key=null, $default=null) {
		if($key===null) return (array)self::$vars;
		return a::get(self::$vars, $key, $default);				
	}

  public static function get_rendered_page($template='default', $vars=array(), $return=false) {
    $tpl = self::get_template_env($template, $vars, $return);    
    return $tpl->render(array_merge(self::$vars, $vars));
  }

  public static function load($template='default', $vars=array(), $return=false) {
    $tpl = self::get_template_env($template, $vars, $return);    
    $tpl->display(array_merge(self::$vars, $vars));
  }

  public static function get_template_env($template='default', $vars=array(), $return=false) {		
    $file = c::get('twig.root') . c::get('twig.tpldir', '') .'/' . $template . '.html';
    if(!file_exists($file)) return false;

    require_once(c::get('root') .'/plugins/Twig/Autoloader.php');
    Twig_Autoloader::register();

    $loader = new Twig_Loader_Filesystem(c::get('twig.root') . c::get('twig.tpldir'));
    $twig = new Twig_Environment($loader, array(
      'cache' => c::get('twig.cache', c::get('twig.root') .'/cache'),
      'debug' => c::get('twig.debug', false),
    ));

    if(!empty(self::$functions)) {
      foreach(self::$functions as $key => $value) {
        if(is_array($value)) {
          $twig->addFunction($key, new Twig_Function_Function($value[0] .'::'. $value[1]));
        } else {
          $twig->addFunction($key, new Twig_Function_Function($value));
        }
      }
    }

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
    return $template;
    
	}

}
