<?php
  namespace sbacic\wpdemo;
  $wpconfig = $options['config'];
  require($wpconfig);

  $_PATHS   = require('autoloader.php');
  $helper   = new Helper();
  $config   = require($_PATHS['config']);
  $prefix   = $table_prefix;
  $override = $options['host'] ? array('host' => $options['host']) : array();
  $pdo      = Helper::setupConnection($override);
  $uploads  = defined('UPLOADS') ? UPLOADS : 'wp-content/uploads';
  $loader   = new Loader($prefix, $config, $pdo, $_PATHS);

  set_time_limit(0);

  if (ini_get('max_execution_time') != 0) {
    throw new Exception("Execution time must be infinite, aborting process", 1);
  }

  //Cleanup
  $helper->restore($pdo, $_PATHS['demosql']);
  $loader->cleanup();

?>
