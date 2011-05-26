<?php

class FrankHandler extends HttpHandler {
  function get($params) {
    tpl::set('count', db::count('sistrix_simplevalue'));
    $this->do_layout('frank');
  }
}
