<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>
      <? if(Config::getInstance()->get('debug', false)): ?>
        Unhandled Error occured
      <? else: ?>
        An error occured
      <? endif; ?>
    </title>
    <style type="text/css" media="screen">
      @import url(http://fonts.googleapis.com/css?family=Droid+Sans:regular,bold);
      @import url(http://fonts.googleapis.com/css?family=Droid+Sans+Mono);
      body, html { font-family:'Droid Sans'; font-size:12px; height:100%; margin:0;padding:0; }
      .swc0 { margin: 0 auto; display: table; width:600px; height:100%; }
      .swc1 { display: table-cell; vertical-align: middle; }
      .error { border:1px solid #900; background-color:#fce6e9; padding:10px; border-radius:10px; margin-bottom:30px; }
      .title { text-align:center; font-weight:bold; font-size:14px; }
      pre { font-family:'Droid Sans Mono'; }
    </style>
  </head>
  <body>
    <div class="swc0">
      <div class="swc1">
        <div class="error">
          <? if(Config::getInstance()->get('debug', false)): ?>
            <p class="title">Unhandled Error occured!</p>
            <p><?= preg_replace("/('[^']*')/", '<strong>\1</strong>', $exception['msg']); ?></p>
            <pre><?= $exception['trace']; ?></pre>
          <? else: ?>
            <p class="title">An error occured!</p>
            <p style="text-align:center;">We are sorry and will look after this as soon as possible.</p>
          <? endif; ?>
        </div>
      </div>
    </div>
  </body>
</html>
