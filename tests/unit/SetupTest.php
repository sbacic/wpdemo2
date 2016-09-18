<?php

use sbacic\wpdemo\Setup;

class SetupTest extends PHPUnit_Framework_TestCase
{
  protected static $original;
  protected static $backup;

  public function setUp()
  {
    cleanup();
    chdir(UNIT);
    $root   = ROOT;
    $tmp    = TMP;
    $stubs  = STUBS;

    self::$original = "$tmp/wp-config.php";
    self::$backup   = "$tmp/wp-config-backup.php";
    exec("cp -r $stubs/wp-config.php $tmp/wp-config.php");
    exec("cp -r $stubs/wp-config-backup.php $tmp/wp-config-backup.php");
    exec("mkdir -p $tmp/build");
  }

  public function testBackupConfig()
  {
    $setup  = new Setup();
    $setup->backupConfig(self::$original, self::$backup);
    $this->assertTrue(file_exists(self::$backup));
  }

  public function testRestoreToPristine()
  {
    $setup  = new Setup();
    $setup->restoreToPristine(self::$backup, self::$original);
    $this->assertFileEquals(STUBS . "/wp-config.php", self::$original);
    $this->assertFileNotExists(self::$backup);
  }

  public function testCreateWPDemoConfig()
  {
    $setup  = new Setup();
    $setup->createWPDemoConfig('tmp/build/Config.php', ROOT .'/src/Config.php',  array());

    $this->assertFileExists('tmp/build/Config.php');
  }

  public function testInsertLoader()
  {
    $setup  = new Setup();
    $snr    = array('%pharPath%' => '../build/wpdemo.phar', '%configPath%' => '../build/Config.php');
    $string = $setup->insertConfigValues(ROOT . '/src/loader.template', $snr);
    $status = $setup->insertLoader(self::$original, $string);
    $this->assertTrue($status);

    $haystack = file_get_contents(self::$original);
    $needle   = $string;
    $this->assertNotFalse(strpos($haystack, $needle));

    exec("chmod -rw ".self::$original);

    try {
      $setup->insertLoader(self::$original, $string);
      $this->fail('Expected permissions exception not thrown.');
    } catch (Exception $e) {
      $this->assertEquals('Could not insert loader into wp-config.php. Verify that you have read and write permissions.', $e->getMessage());
    }

    exec("chmod +rw ".self::$original);
    exec("rm ".self::$original);

    try {
      $setup->insertLoader(self::$original, $string);
      $this->fail('Expected file missing exception not thrown.');
    } catch (Exception $e) {
      $this->assertEquals('Could find wp-config.php', substr($e->getMessage(), 0, 24));
    }

  }

  public function testBackupDatabase()
  {
    if (exec("docker ps | grep wpdemo_db") == '') {
      $this->markTestSkipped('wpdemo_db container is not running, skipping test');
    }

    query('DROP DATABASE wordpress; CREATE DATABASE wordpress; USE wordpress;');
    query(file_get_contents(STUBS . '/wordpress.backupDatabase.sql'));
    $setup    = new Setup();
    $username = 'root';
    $password = 'example';
    $host     = exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' wpdemo_db");
    $database = 'wordpress';
    $path     = 'tmp/build/wordpress.sql';

    if (! $host) {
      throw new Exception("Host field is empty, are you sure wpdemo_db container is up?", 1);
    }

    $setup->backupDatabase($username, $password, $host, $database, $path, 'wp_');
    sleep(0.5);
    $haystack = file_get_contents($path);

    $this->assertFileExists($path);
    $this->assertTrue(strpos($haystack, 'wp_') !== false);
    $this->assertTrue(strpos($haystack, 'wpdemo') === false);
  }

  public function testBackupUploads()
  {
    exec('rm -rf tmp/build/uploads');
    $setup  = new Setup();
    $from   = ROOT . '/assets/uploads';
    $to     = 'tmp/build/uploads';
    $setup->backupUploadsDir($from, $to);
    $this->assertFileEquals($from, $to);
  }
}
?>
