<?php

require_once '%%FRAMEWORK%%/dispatcher.php';

$dispatcher = new Dispatcher(
    new ConfigIni('%%APPDIR%%/settings.ini', null)
  , '%%APPDIR%%'
);
$dispatcher->dispatch($_SERVER['REQUEST_URI']);
