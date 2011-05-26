<?php

$urlpattern = array(
  '|^/$|' => 'MainHandler',
  '|^/knut/([0-9]+)$|' => 'KnutHandler',
  '|^/frank|' => 'FrankHandler',
  '|.?|' => 'MainHandler'
);
