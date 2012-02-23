<?php

$path_to_dispatcher = '%%FRAMEWORK%%/dispatcher.php';

require_once $path_to_dispatcher;

$dispatcher = new Dispatcher(
    new ConfigIni('%%APPDIR%%/settings.ini', null)
  , '%%APPDIR%%'
);
$dispatcher->dispatch($_SERVER['REQUEST_URI']);
