<?php

$routes = array(
    '|/test/$|' => 'TestappTestHandler'
  , '|/$|' => 'TestappMainHandler'

  , '|.|' => 'TestappError404Handler'
);
