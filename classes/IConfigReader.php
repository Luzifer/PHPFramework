<?php

interface IConfigReader {
  public function get($config_key, $default = null);
  public function getSection($config_section_name);
  public function set($config_key, $config_value);
}
