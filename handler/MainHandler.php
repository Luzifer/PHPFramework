<?php

class MainHandler extends HttpHandler {
  
  function get($params) {
    $this->do_layout('index');
  }
  
}