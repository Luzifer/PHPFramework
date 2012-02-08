<?php

class BaseHttpHandler {
  
  private $request = null;
  private $response = null;
  
  public function __construct($request, $response) {
    $this->request = $request;
    $this->response = $response;
  }
  
  /**
   * Handler for GET requests
   * 
   * @param array $params Selections from the url defined in urls.php are passed to this
   * @throws MethodNotImplementedException When not implemented in child class
   */
  public function get($params) { throw new MethodNotImplementedException('Method GET not implemented for ' . get_class($this)); }
  
  /**
   * Handler for HEAD requests
   *
   * @param array $params Selections from the url defined in urls.php are passed to this    
   * @throws MethodNotImplementedException When not implemented in child class
   */
  public function head($params) { throw new MethodNotImplementedException('Method HEAD not implemented for ' . get_class($this)); }
  
  /**
   * Handler for POST requests
   * 
   * @param array $params Selections from the url defined in urls.php are passed to this
   * @throws MethodNotImplementedException When not implemented in child class
   */
  public function post($params) { throw new MethodNotImplementedException('Method POST not implemented for ' . get_class($this)); }


  /**
   * Handler for CLI requests
   * 
   * @param array $params Selections from the url defined in urls.php are passed to this
   * @throws MethodNotImplementedException When not implemented in child class
   */
  public function cli($params) { throw new MethodNotImplementedException('Method CLI not implemented for ' . get_class($this)); }
}

class MethodNotImplementedException extends Exception {}
