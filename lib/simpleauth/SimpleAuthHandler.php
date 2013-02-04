<?php

class SimpleAuthHandler extends BaseHttpHandler {

  /**
   * Name of the currently authenticated user, null if none
   *
   * @var null|string
   */
  protected $authenticated_user = null;

  public function __construct($request, $response, $config) {
    parent::__construct($request, $response, $config);

    $this->check_auth();
  }

  private function check_auth() {
    $auth_data = array();

    try {
      $auth_data = $this->config->getSection('authorization');
    } catch(Exception $ex) {
      throw new SimpleAuthConfigurationError('Please provice "authorization" config section.');
    }

    if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
      $this->send_to_auth();
    }

    if(!array_key_exists($_SERVER['PHP_AUTH_USER'], $auth_data)) {
      $this->send_to_auth();
    }

    $password_hash = $auth_data[$_SERVER['PHP_AUTH_USER']];
    if(sha1($_SERVER['PHP_AUTH_PW']) !== $password_hash) {
      $this->send_to_auth();
    }

    $this->authenticated_user = $_SERVER['PHP_AUTH_USER'];
  }

  private function send_to_auth() {
    $realm = $this->config->get('auth_realm', 'Please login!');
    header('WWW-Authenticate: Basic realm="' . $realm . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo('Login required!');
    exit;
  }

}

class SimpleAuthConfigurationError extends Exception {}
