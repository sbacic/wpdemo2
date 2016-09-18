<?php
  namespace sbacic\wpdemo;

  class Setup {

    public function backupConfig($source, $dest)
    {
      if (! file_exists($dest)) {
        if (! file_exists($source)) {
          throw new \Exception("Could not find wp-config.php at $source.", 1);
        } else if (is_readable($source ) && is_writable(dirname($dest))){
          copy($source, $dest);
          return true;
        } else {
          throw new \Exception("Could not backup wp-config.php. Make sure the root Wordpress directory has write permissions enabled.", 1);
        }
      } else {
        return false;
      }

    }

    public function restoreToPristine($backup, $original)
    {
      if (file_exists($backup) && basename($original != 'wp-config-backup.php')) {
        @unlink($original);
        @rename($backup, $original);
      }
    }

    public function insertLoader($wpconfig, $loaderString)
    {
      if (! file_exists($wpconfig)) {
        throw new \Exception("Could find wp-config.php at '$wpconfig'.", 1);
      }

      if (! is_readable($wpconfig) || ! is_writable($wpconfig)) {
        throw new \Exception('Could not insert loader into wp-config.php. Verify that you have read and write permissions.', 1);
      }

      $search = "require_once(ABSPATH . 'wp-settings.php');";
      $string = file_get_contents($wpconfig);
      $loader = str_replace($search, $loaderString, $string);
      file_put_contents($wpconfig, $loader);

      return true;
    }

    public function createWPDemoConfig($saveTo, $from, $snr = array())
    {
      file_put_contents($saveTo, $this->insertConfigValues($from, $snr));
    }

    public function insertConfigValues($path, $snr, $removeTokens = true)
    {
      $subject   = file_get_contents($path);
      $search    = array_keys($snr);
      $replace   = array_values($snr);
      $processed = str_replace($search, $replace, $subject);

      if ($removeTokens === true) {
        $processed = str_replace('%', '', $processed);
      }

      return $processed;
    }

    public function backupDatabase($username, $password, $host, $database, $path, $prefix)
    {
      $helper = new Helper();
      return $helper->dump($username, $password, $host, $database, $path, $prefix);
    }

    public function backupUploadsDir($from, $to)
    {
      $helper = new Helper();
      $helper->cloneDir($from, $to);
    }
  }

?>
