<?php

use sbacic\wpdemo\Helper;

class HelperTest extends PHPUnit_Framework_TestCase {

  public static function setupBeforeClass()
  {
    define('DONT_RUN_WPDEMO', true);
  }

  public function testRelpath()
  {
    $helper = new Helper();
    $one    = 'HelperTest.php';
    $two    = 'wordpress.sql';
    $three  = 'LoaderTest.php';

    chdir(STUBS . '/relpath');

    $path = $helper->relpath('c.txt', 'b/b.txt');
    $this->assertEquals('b/b.txt', $path);

    $path = $helper->relpath('b/b.txt', 'c.txt');
    $this->assertEquals('../c.txt', $path);

    $path = $helper->relpath('c.txt', 'd.txt');
    $this->assertEquals('d.txt', $path);

    $path = $helper->relpath('d.txt', 'c.txt');
    $this->assertEquals('c.txt', $path);

    $path = $helper->relpath('phar://a/a.phar', 'c.txt');
    $this->assertEquals('../c.txt', $path);

    $path = $helper->relpath('c.txt', 'phar://a/a.phar');
    $this->assertEquals('phar://a/a.phar', $path);

    $path = $helper->relpath('c.txt', 'phar://a');
    $this->assertEquals('a', $path);

    chdir('b');

    $path = $helper->relpath('../c.txt', '../a/a.txt');
    $this->assertEquals('a/a.txt', $path);

    $path = $helper->relpath('../a/a.txt', '../c.txt');
    $this->assertEquals('../c.txt', $path);

    $path = $helper->relpath('../a/', '.');
    $this->assertEquals('../b', $path);

    try {
      $path = $helper->relpath('../f/', '.');
      $this->fails('Expect missing file exception was not thrown.');
    } catch (Exception $e) {
      $this->assertEquals('../f/ does not exist.', $e->getMessage());
    }

  }

  public function testCloneMissingDir()
  {
    $pdo = new Helper();
    $this->assertFalse($pdo->cloneDir('/foo/bar', '/foo/baz'));
  }

  public function testSetupConnection()
  {
    if (exec("docker ps | grep wpdemo_db") == '') {
      $this->markTestSkipped('wpdemo_db container is not running, skipping test');
    }

    require_once(STUBS . '/wp-config-modified.php');
    $host = exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' wpdemo_db");
    $pdo  = Helper::setupConnection(array('host' => $host));
  }

  public function testFailedSetupConnection()
  {
    require_once(STUBS . '/wp-config-modified.php');
    $this->setExpectedException(\PDOException::class);
    $pdo = Helper::setupConnection();
  }
}
