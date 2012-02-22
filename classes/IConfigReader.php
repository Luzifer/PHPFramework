<?php

interface IConfigReader {
  public function get($config_key);
  public function getSection($config_section_name);
}