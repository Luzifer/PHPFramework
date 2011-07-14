<?php

/**
  Base class for any handler used in urls.php
  
  @author Knut Ahlers <knut@ahlers.me>
*/
class HttpHandler {
  
  /**
    Short call to do a redirect to a new URL
    
    @param url (string) The full URL used in the redirect
    @param code (int) May be 301 (permanent redirect) or 302 (temporary redirect)
  */
  protected function redirect($url, $code = 302) {
    if($code == 301) {
      header("HTTP/1.1 301 Moved Permanently");
    } else {
      header("HTTP/1.1 302 Moved Temporarily");
    }
    header('Location: ' . $url);
  }
  
  /**
    Wrapper for template loading
    
    @param template (string) Name of the template to execute
  */
  protected function do_layout($template) {
    tpl::load($template);
  }
  
  /**
    Handler for GET requests
    
    @param params (string[]) Selections from the url defined in urls.php are passed to this
    @throws HttpResponderException When not implemented in child class
  */
  public function get($params) { throw new HttpResponderException('Method GET not implemented for ' . get_class($this)); }
  
  /**
    Handler for HEAD requests

    @param params (string[]) Selections from the url defined in urls.php are passed to this    
    @throws HttpResponderException When not implemented in child class
  */
  public function head($params) { throw new HttpResponderException('Method HEAD not implemented for ' . get_class($this)); }
  
  /**
    Handler for POST requests
    
    @param params (string[]) Selections from the url defined in urls.php are passed to this
    @throws HttpResponderException When not implemented in child class
  */
  public function post($params) { throw new HttpResponderException('Method POST not implemented for ' . get_class($this)); }
}

class HttpResponderException extends Exception {}