<?php
  namespace sbacic\wpdemo;
  $wpconfig = $options['config'];
  require($wpconfig);

  $_PATHS   = require('autoloader.php');
  $config   = require_once($_PATHS['config']);
  $prefix   = $table_prefix;
  $host     = isset($options['host']) ? $options['host'] : DB_HOST;
  $loader   = new Loader($prefix, $config, Helper::setupConnection(array('host' => $host)), $_PATHS);

  $loader->purge();

?>
