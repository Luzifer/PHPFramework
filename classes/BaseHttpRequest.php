<?php

class BaseHttpRequest {
  
  private $value_sources = array(
      'get' => array('default' => 'get', 'fallback' => 'post')
    , 'post' => array('default' => 'post', 'fallback' => 'get')
    , 'head' => array('default' => 'get', 'fallback' => 'post')
  );
  private $request_method = null;
  
  public function __construct($request_method) {
    $this->request_method = $request_method;
  }
  
  /**
   * Returns a parameter value from the request sent by the client
   * 
   * @param string $parameter_name Name of the request parameter to deliver
   * @param mixed $default_value Default value to deliver when parameter is not found
   * @param boolean $use_strict_mode Whether to allow searching in other request types than the used one
   * @return mixed
   * @throws MethodNotSupportedException when the parameter is read for a request type which is not supported by the framework
   */
  public function get($parameter_name, $default_value = null, $use_strict_mode = false) {
    // Early exit when not defined where to read request values
    if(!array_key_exists($this->request_method, $this->value_sources)) {
      throw new MethodNotSupportedException('Getting request parameters of ' . $this->request_method . ' is not supported.');
    }
    
    try {
      return $this->read_request_parameter($parameter_name, $this->value_sources[$this->request_method]['default']);
    } catch(ParameterNotFoundException $ex) {}
    
    if(!$use_strict_mode) {
      try {
        return $this->read_request_parameter($parameter_name, $this->value_sources[$this->request_method]['fallback']);
      } catch(ParameterNotFoundException $ex) {}
    }
    
    return $default_value;
    
  }
  
  /**
   * Returns the value of an parameter for a specified source if the source is supported and the parameter exists
   * 
   * @param string $parameter_name Name of the request parameter to deliver
   * @param string $value_source Name of the source to use when reading the parameter
   * @return mixed
   * @throws ValueSourceNotSupportedException when the name of the source is unsupported
   * @throws ParameterNotFoundException when the parameter is not found in the source to avoid collision with other value types
   */
  private function read_request_parameter($parameter_name, $value_source) {
    $source = null;
    if($value_source == 'get') {
      $source = &$_GET;
    } elseif($value_source == 'post') {
      $source = &$_POST;
    }
    
    if($source === null) {
      throw new ValueSourceNotSupportedException('The source ' . $value_source . ' is not supported to read values from.');
    }
    
    if(array_key_exists($parameter_name, $source)) {
      return $source[$parameter_name];
    }
    
    throw new ParameterNotFoundException('Parameter ' . $parameter_name . ' was not found in source ' . $value_source);
  }
  
}

class MethodNotSupportedException extends Exception {}
class ValueSourceNotSupportedException extends Exception {}
class ParameterNotFoundException extends Exception {}
