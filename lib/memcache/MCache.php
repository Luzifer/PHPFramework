<?php

class MCache extends Memcached {

  /**
   * @param IConfigReader $config
   */
  public function __construct($config) {
    parent::__construct();

    $servers = array();
    $config_data = $config->getSection('memcache');
    foreach($config_data as $server) {
      if(!empty($server['user']) && !empty($server['password'])) {
        $this->configureSasl($server['user'], $server['password']);
      }
      $this->addServer($server['host'], (!empty($server['port'])) ? $server['port'] : '11211');
    }
  }
}
