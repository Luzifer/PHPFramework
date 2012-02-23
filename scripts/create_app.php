<?php

if($argc != 2) {
  die('Usage: ' . $argv[0] . ' <appdir>' . "\n");
}

$frameworkdir = realpath(dirname(__FILE__) . '/../');
$appdir = $argv[1];

if(is_dir($appdir)) {
  die('Target directory "' . $appdir . '" already exists. Cannot create application!' . "\n");
}

copy_directory(dirname(__FILE__) . '/../docs/example_appdir', $appdir);
rename(rtrim($appdir, '/') . '/.htaccess', '.htaccess');

$index = file_get_contents(rtrim($appdir, '/') . '/index.php');
$index = str_replace('%%APPDIR%%', $appdir, $index);
$index = str_replace('%%FRAMEWORK%%', $frameworkdir, $index);

if(!file_exists('index.php')) {
  file_put_contents('index.php', $index);
}
unlink(rtrim($appdir, '/') . '/index.php');

echo 'App "' . $appdir . '" has been created.' . "\n";



function copy_directory( $source, $destination ) {
  if ( is_dir( $source ) ) {
    @mkdir( $destination );
    $directory = dir( $source );
    while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
      if ( $readdirectory == '.' || $readdirectory == '..' ) {
        continue;
      }
      $PathDir = $source . '/' . $readdirectory;
      if ( is_dir( $PathDir ) ) {
        copy_directory( $PathDir, $destination . '/' . $readdirectory );
        continue;
      }
      copy( $PathDir, $destination . '/' . $readdirectory );
    }

    $directory->close();
  }else {
    copy( $source, $destination );
  }
}
