<?php
  namespace sbacic\wpdemo;
  $wpconfig = $options['config'];
  require($wpconfig); //Include wp-config.php for its constants and $table_prefix

  $_PATHS   = require('autoloader.php');
  $helper   = new Helper();
  $config   = require($_PATHS['config']);
  $prefix   = $table_prefix; //From wp-config.php
  $override = $options['host'] ? array('host' => $options['host']) : array();
  $pdo      = Helper::setupConnection($override);
  $loader   = new Loader($prefix, $config, $pdo, $_PATHS);

  set_time_limit(0);

  if (ini_get('max_execution_time') != 0) {
    throw new Exception("Execution time must be infinite, aborting process", 1);
  }

  //Populate and cleanup
  $helper->restore($pdo, $_PATHS['demosql']);
  $loader->load();
  $loader->populate();
  $loader->cleanup();

?>
