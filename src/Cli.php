<?php
  namespace sbacic\wpdemo;
  define("DONT_RUN_WPDEMO", true);

  //Get options and main paths
  $options  = getopt('', array("populate", "setup", "cleanup", "purge", "debug", "config:", "host:"));

  $_PATHS   = require('autoloader.php');
  $action   = array_pop(array_filter(array_keys($options), function($value){
    return in_array($value, array('populate', 'setup', 'cleanup', 'purge'));
  }));

  //Show errors
  if (isset($options['debug'])) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  }

  //Populate and cleanup wpdemo
  if ($action) {
    $path = sprintf('%s/cli/%s.php', __DIR__, $action);

    call_user_func(function($options, $path){
      require($path);
    }, $options, $path);
  }
?>
