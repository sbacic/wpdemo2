<?php

use sbacic\wpdemo\Loader;
use sbacic\wpdemo\Cookie;

class LoaderTest extends PHPUnit_Framework_TestCase {

  protected static $config, $paths;

  public static function setupBeforeClass()
  {
    chdir(UNIT);
    $tmp = TMP;
    exec("mkdir -p $tmp/wp-content/uploads");
    self::$config = require(STUBS  . "/Config.php");
    self::$paths   = array(
      "wpDemoMediaDir" => realpath("tmp/wp-content/uploads"),
      "wpOriginalMediaDir" => realpath("tmp/build/uploads"),
      'exists' => array(
        'backup' => 'stubs/wordpress.populate.sql',
        'uploads' => ROOT . '/assets/uploads'
    ));
  }

  public function testGetUploadDir()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO();
    $config = self::$config;
    $obj    = new Loader('wp_', $config, $pdo, $paths, null);
    $this->assertEquals(TMP . "/wp-content/uploads/wpdemo_butter", $obj->getUploadDir('wpdemo_butter'));
  }

  public function testLoadEmpty()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO();
    $config = self::$config;
    $obj    = new Loader('wp_', $config, $pdo, $paths, null);
    $obj->load();

    $this->assertEquals(array(), $obj->free);
  }

  public function testPurge()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO([
      "SHOW TABLE STATUS WHERE name LIKE 'wpdemo_%'" => [array('id' => 'wpdemo_butter', 'used' => 0)]
    ], []);
    $config = self::$config;
    $obj    = new Loader('wp_', $config, $pdo, $paths, null);

    exec("mkdir -p ".$paths['wpDemoMediaDir']);
    $obj->purge();

    $this->assertFileNotExists($paths['wpDemoMediaDir']);
  }

  public function testLoad()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => [array('used' => 0, 'id' => 'wpdemo_butter'), array('used' => 1, 'id' => 'wpdemo_toast')],
    ]);
    $config = self::$config;
    $obj    = new Loader('wp_', $config, $pdo, $paths, null);
    $obj->load();

    $this->assertEquals(1, count($obj->free));
    $this->assertEquals(1, count($obj->used));
  }

  public function testValidSession()
  {
    $_COOKIE['wpdemo'] = 'wpdemo_butter';

    $paths  = self::$paths;
    $pdo    = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => [array('used' => 1, 'id' => 'wpdemo_butter')]
    ]);
    $config = self::$config;
    $obj    = new Loader('wp_', $config, $pdo, $paths, new Cookie());
    $obj->load();

    $this->assertEquals('wpdemo_butter_', $obj->getPrefix());
    $this->assertEquals(TMP . "/wp-content/uploads/wpdemo_butter", $obj->getUploadDir());
  }

  public function testInvalidSession()
  {
    $_COOKIE['wpdemo'] = 'wpdemo_butter';

    $paths  = self::$paths;
    $pdo    = new MockPDO([], []);
    $config = self::$config;
    $obj    = new Loader('wp_', $config, $pdo, $paths, new Cookie());
    $obj->load();

    $this->assertEquals(false, $obj->getPrefix());
    $this->assertEquals(false, $obj->getUploadDir());
  }

  public function testAssign()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => [array('used' => 0, 'id' => 'wpdemo_butter')]
    ]);
    $config = self::$config;
    $cookie = new Cookie();
    $obj    = new Loader('wp_', $config, $pdo, $paths, $cookie);
    $obj->load();

    $this->assertNotEquals(false, $obj->assignInstance());
    $this->assertEquals('wpdemo_butter', $cookie->get());
    $this->assertEquals('wpdemo_butter_', $obj->getPrefix());
    $this->assertEquals(TMP . "/wp-content/uploads/wpdemo_butter", $obj->getUploadDir());
  }

  public function testFailedAssign()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO([], [[]]);
    $config = self::$config;
    $cookie = new Cookie();
    $obj    = new Loader('wp_', $config, $pdo, $paths, $cookie);
    $obj->load();

    $this->assertEquals(false, $obj->assignInstance());
    $this->assertEquals(false, $cookie->get());
    $this->assertEquals(false, $obj->getPrefix());
    $this->assertEquals(false, $obj->getUploadDir());
  }

  public function testSpotAssignWhenNotEmpty()
  {
    $paths  = self::$paths;
    $pdo    = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => [array('used' => 0, 'id' => 'wpdemo_butter')]
    ]);
    $config = self::$config;
    $config['autoGenerate'] = true;
    $cookie = new Cookie();
    $obj    = new Loader('wp_', $config, $pdo, $paths, $cookie);
    $obj->load();

    $this->assertNotEquals(false, $obj->assignInstance());
    $this->assertEquals(0, count($obj->free));
    $this->assertEquals(1, count($obj->used));
    $this->assertEquals('wpdemo_butter', $cookie->get());
    $this->assertEquals('wpdemo_butter_', $obj->getPrefix());
    $this->assertEquals(TMP . "/wp-content/uploads/wpdemo_butter", $obj->getUploadDir());
  }

  public function testPopulate()
  {
    $paths  = self::$paths;
    $config = self::$config;
    $pdo    = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => new MultiMock([
        [],
        [array('used' => 0, 'assigned' => null, 'id' => 'wpdemo_butter')]]
      )
    ]);
    $obj    = new Loader('wp_', $config, $pdo, $paths, null);
    $obj->load();
    $obj->populate();
    $obj->load();

    $this->assertEquals(1, count($obj->free));
    $this->assertFileExists($paths['wpDemoMediaDir'] . '/wpdemo_butter');
    $this->assertFileEquals($paths['exists']['uploads'], $paths['wpDemoMediaDir'] . '/wpdemo_butter');
  }

  public function testSpotAssignWhenEmpty()
  {
    exec('rm -rf tmp/wp-content/uploads/wpdemo_butter');
    $paths  = self::$paths;
    $pdo    = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => new MultiMock([
        [array('used' => 0, 'assigned' => null, 'id' => 'wpdemo_butter')],
        [array('used' => 1, 'assigned' => null, 'id' => 'wpdemo_butter')]]
      )
    ]);
    $config = self::$config;
    $config['autoGenerate'] = true;
    $cookie = new Cookie();
    $obj    = new Loader('wp_', $config, $pdo, $paths, $cookie);

    $this->assertEquals(true, $obj->assignInstance());
    $this->assertEquals(1, count($obj->used));
    $this->assertFileExists($paths['wpDemoMediaDir'] . '/wpdemo_butter');
    $this->assertFileEquals($paths['exists']['uploads'], $paths['wpDemoMediaDir'] . '/wpdemo_butter');
  }

  public function testCleanup()
  {
    exec('mkdir -p ' . TMP . '/wp-content/uploads/wpdemo_butter');
    exec('mkdir -p ' . TMP . '/wp-content/uploads/wpdemo_toast');

    $pdo = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => [
        array('used' => 1, 'id' => 'wpdemo_butter', 'assigned' => '2015-09-02 16:38:38'),
        array('used' => 1, 'id' => 'wpdemo_toast', 'assigned' => date("Y-m-d H:i:s"))
      ]
    ], []);

    $config = self::$config;
    $paths  = self::$paths;
    $obj    = new Loader($prefix = 'wp_', $config, $pdo, $paths, new Cookie());
    $obj->load();
    $obj->cleanup();
    $this->assertFileExists($paths['wpDemoMediaDir'] . '/wpdemo_toast');
    $this->assertFileNotExists($paths['wpDemoMediaDir'] . '/wpdemo_butter');
  }
}

?>
