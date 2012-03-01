<?php

require_once '%%FRAMEWORK%%/dispatcher.php';

// BaseAutoLoader::register_base_lib('mysql');

$dispatcher = new Dispatcher(
    new ConfigIni('%%APPDIR%%/settings.ini', null)
  , '%%APPDIR%%'
);
$dispatcher->dispatch($_SERVER['REQUEST_URI']);
