<?php
  namespace sbacic\wpdemo;

  if (! defined("DONT_RUN_WPDEMO")) {
    define("DONT_RUN_WPDEMO", true);
  }

  return call_user_func(function(){
    #Don't forget to change these when you move the provisioner
    $phar     = "phar://wpdemo.phar";
    $wpconfig = "../wp-config.php";

    #Require wp-config.php and the autoloader
    require($wpconfig);
    $_PATHS = require(sprintf("%s/src/autoloader.php", $phar));

    #Assign an instance
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

    if ($loader->getPrefix() === false) {
      return $loader->assignInstance();
    } else {
      return false;
    }
  });
?>
