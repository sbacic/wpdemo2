<?php
  namespace sbacic\wpdemo;
  $_PATHS = require(dirname(__DIR__) . '/autoloader.php');

  $absDir  = str_replace('phar://', '', dirname(\Phar::running()));
  $config  = require($_PATHS['config']);
  $pdo     = Helper::setupConnection();
  $loader  = new Loader(
    $table_prefix,
    $config,
    $pdo,
    $_PATHS,
    new Cookie()
  );

  $helper = new Helper();
  $helper->restore($pdo, $_PATHS['demosql']);
  $loader->load();

  if ($loader->getPrefix() !== false) {
    define('UPLOADS', $loader->getUploadDir());
    return $loader->getPrefix();
  } else if ($config['manualAssign'] === false) {
    if ($loader->assignInstance()) {
      define('UPLOADS', $loader->getUploadDir());
      return $loader->getPrefix();
    } else {
      die("No free demo sessions available");
    }
  } else {
    die("Cannot automatically start the demo. Contact the administrator to assign you a demo session");
  }

?>
