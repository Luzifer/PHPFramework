<?php

// I suggest to use | as an regex delimiter because in URLs you
// will use / a lot and have to escape them all otherwise.
$urlpattern = array(
  '|^/$|' => 'MainHandler',
  '|^/login/$|' => 'LoginHandler'
);
