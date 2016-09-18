<?php
  namespace sbacic\wpdemo;
  //Setup wpdemo
  $setup  = new Setup();
  $helper = new Helper();
  $_PATHS = require('autoloader.php');
  //Restore wp-config.php to its pristine state and back it up as needed
  $wpconfig = $options['config'];
  $backup   = sprintf('%s/wp-config-backup.php', dirname($options['config']));
  $setup->restoreToPristine($backup, $wpconfig);
  $setup->backupConfig($wpconfig, $backup);

  //Insert WPDemo loader into wp-config.php
  $wpconfig = $options['config'];
  $phar     = sprintf('%s/src/cli/run.php', $helper->relpath($wpconfig, \Phar::running()));
  $snr      = array('%pharPath%' => $phar);
  $string   = $setup->insertConfigValues($_PATHS['loader'], $snr);
  $setup->insertLoader($wpconfig, $string);
  require($wpconfig);

  //Create config, database and uploads backups
  $wpconfig = $options['config'];
  $absDir   = str_replace('phar://', '', dirname(\Phar::running()));
  $uploads  = defined('UPLOADS') ? realpath(UPLOADS) : sprintf('%s/wp-content/uploads', dirname($wpconfig));
  $username = DB_USER;
  $password = DB_PASSWORD;
  $host     = isset($options['host']) ? $options['host'] : DB_HOST;
  $database = DB_NAME;

  $pathToDbBackup       = sprintf('%s/wordpress.sql', $absDir);
  $pathToUploadsBackup  = sprintf('%s/uploads', $absDir);
  $setup->backupDatabase($username, $password, $host, $database, $pathToDbBackup, $table_prefix);
  $setup->backupUploadsDir($uploads, $pathToUploadsBackup);

  //Create wpdemo config in same directory as the phar
  $absDir   = str_replace('phar://', '', dirname(\Phar::running()));
  $absPhar  = \Phar::running();

  $from     = sprintf('%s/src/Config.php', $absPhar);
  $to       = sprintf('%s/Config.php', $absDir);
  $setup->createWPDemoConfig($to, $from);

  //Copy provisioner
  $provisioner = "$absDir/provisioner.php";
  $relWpConfig = $helper->relpath($absDir, $wpconfig);
  file_put_contents($provisioner,
    str_replace(
    '../wp-config.php', $relWpConfig,
    file_get_contents($absPhar . '/src/provisioner.php')
    )
  );
?>
