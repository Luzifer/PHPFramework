<?php

require_once(dirname(__file__) . '/../helper/dispatcher.php');

Dispatcher::getInstance()->dispatch($_SERVER['REQUEST_URI']);


