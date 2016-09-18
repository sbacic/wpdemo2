<?php
  namespace sbacic\wpdemo;

  $_PATHS = array(
  );

  //Template for self-invoking function
  $_PATHS = call_user_func(function($_PATHS){
    return $_PATHS;
  }, $_PATHS);

  //Phar
  //These paths are only available when autoloader.php is inside a PHAR
  //Note that this still does not guarantee that the files actually exist
  //so use file_exists to be sure
  $_PATHS = call_user_func(function($_PATHS){
    if (\Phar::running()) {
      $dir = str_replace('phar://', '', dirname(\Phar::running()));

      $_PATHS['phar']    = \Phar::running();
      $_PATHS['config']  = sprintf('%s/Config.php', $dir);
      $_PATHS['backup']  = sprintf('%s/wordpress.sql', $dir);
      $_PATHS['uploads'] = sprintf('%s/uploads', $dir);
    }
    return $_PATHS;
  }, $_PATHS);

  //Classes
  $_PATHS = call_user_func(function($_PATHS){
    $files    = scandir(__DIR__);
    $classes  = array_filter($files, function($string){return preg_match('#^[A-Z].+\.php$#', $string);});

    foreach ($classes as $class) {
      $key = sprintf('sbacic\wpdemo\%s', substr($class, 0, -4));
      $val = sprintf('%s/%s', __DIR__, $class);
      $_PATHS[$key] = $val;
    }
    unset($_PATHS['sbacic\wpdemo\Cli']);
    unset($_PATHS['sbacic\wpdemo\Config']);

    return $_PATHS;
  }, $_PATHS);

  //Non-php assets
  $_PATHS = call_user_func(function($_PATHS){
    $_PATHS['demosql'] = sprintf('%s/demo.sql', __DIR__);
    $_PATHS['loader'] = sprintf('%s/loader.template', __DIR__);

    return $_PATHS;
  }, $_PATHS);

  //Wordpress files
  $_PATHS = call_user_func(function($_PATHS){
    //wp-config path is only available when called from the CLI or
    //included by wordpress (ie, the wp-config is in the stack trace)
    $wpconfig = array_filter(get_included_files(), function($path){ return strpos($path, 'wp-config.php') !== false;});
    $options = getopt('', array("config:"));

    if ($wpconfig) {
      $_PATHS['wpconfig'] = array_pop($wpconfig);
    } else if (isset($options['config'])){
      $_PATHS['wpconfig'] = realpath($options['config']);
    }

    //The upload directory path is only available when the
    //Config.php file has been generated
    if (isset($_PATHS['config']) && file_exists($_PATHS['config'])){
      $config                   = require($_PATHS['config']);
      $_PATHS['wpDemoMediaDir'] = dirname($_PATHS['wpconfig']) . '/' . $config['cloneMediaTo'];
    }

    //Get the default wp-config uploads path
    if (isset($_PATHS['wpconfig'])) {
      $_PATHS['wpOriginalMediaDir'] = dirname($_PATHS['wpconfig']) . '/' . (defined('UPLOADS') ? UPLOADS : 'wp-content/uploads');
    }

    return $_PATHS;
  }, $_PATHS);

  //Add your own $_PATHS function here

  //Class autoloading
  if (! spl_autoload_functions() || ! in_array('sbacic\wpdemo\wpdemoAutoload', spl_autoload_functions())) {
    function wpdemoAutoload($classname)
    {
      $_PATHS = require(__FILE__);
      if (isset($_PATHS[$classname])) {
        require_once($_PATHS[$classname]);
      }
    }

    spl_autoload_register('sbacic\wpdemo\wpdemoAutoload');
  }

  //Return paths variable
  $_PATHS['exists'] = array_filter($_PATHS, function($path){return file_exists($path);});

  return $_PATHS;
?>
