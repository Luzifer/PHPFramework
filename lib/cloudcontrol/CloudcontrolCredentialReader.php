<?php

class CloudcontrolCredentialReader implements IConfigReader {

  /**
   * @var IConfigReader
   */
  private $main_config = null;

  /**
   * @var array
   */
  private $credentials = array();

  /**
   * @param IConfigReader $main_config
   */
  public function __construct($main_config) {
    $this->main_config = $main_config;

    $creds = file_get_contents($_ENV['CRED_FILE'], false);
    if($creds !== false) {
      $this->credentials = json_decode($creds, true);
    }
  }

  private function get_credential($config_key) {
    $return_value = null;
    $key_parts = explode('.', $config_key);

    // Only default database connection will be mapped.
    // Others will be passed to original config!
    if(count($key_parts) == 4 && $key_parts[0] == 'db' && $key_parts[2] == 'default') {
      if($key_parts[1] == 'mysql') {
        $return_value = $this->cctrl_mysql_cred($key_parts[4]);
      }
      if($key_parts[1] == 'couchdb') {
        $return_value = $this->cctrl_couchdb_cred($key_parts[4]);
      }
    }

    return $return_value;
  }

  private function cctrl_mysql_cred($key) {
    if(!array_key_exists('MYSQLS', $this->credentials) && !array_key_exists('MYSQLD', $this->credentials)) {
      return null;
    }

    $prefix = 'MYSQLS';

    if(array_key_exists('MYSQLD', $this->credentials)) {
      $prefix = 'MYSQLD';
    }

    switch($key) {
      case 'host':
        return $this->credentials[$prefix][$prefix . '_HOSTNAME'] . ':' . $this->credentials[$prefix][$prefix . '_PORT'];
      case 'user':
        return $this->credentials[$prefix][$prefix . '_USERNAME'];
      case 'password':
        return $this->credentials[$prefix][$prefix . '_PASSWORD'];
      case 'database':
        return $this->credentials[$prefix][$prefix . '_DATABASE'];
      default:
        return null;
    }
  }

  private function cctrl_couchdb_cred($key) {
    if(!array_key_exists('CLOUDANT', $this->credentials)) {
      return null;
    }

    switch($key) {
      case 'host':
        return $this->credentials['CLOUDANT']['CLOUDANT_HOSTNAME'];
      case 'port':
        return $this->credentials['CLOUDANT']['CLOUDANT_PORT'];
      case 'database':
        return $this->credentials['CLOUDANT']['CLOUDANT_DATABASE'];
      case 'user':
        return $this->credentials['CLOUDANT']['CLOUDANT_USERNAME'];
      case 'password':
        return $this->credentials['CLOUDANT']['CLOUDANT_PASSWORD'];
      default:
        return null;
    }
  }

  public function get($config_key, $default = null) {
    $cred = $this->get_credential($config_key);
    if($cred === null) {
      return $this->main_config->get($config_key, $default);
    }
    return $cred;
  }

  public function getSection($config_section_name) {
    // TODO: Implement getSection() method.
  }

  public function set($config_key, $config_value) {
    return $this->main_config->set($config_key, $config_value);
  }
}
