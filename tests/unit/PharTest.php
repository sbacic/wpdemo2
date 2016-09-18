<?php

use sbacic\wpdemo\MockManager;

class PharTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    //Skip test if the db container is not running
    if (exec("docker ps | grep wpdemo_db") == '') {
      $this->markTestSkipped('wpdemo_db container is not running, skipping test');
    }
  }

  public function testFullPharSetup()
  {
    //Prepare
    $root   = ROOT;
    $tmp    = TMP;
    $stubs  = STUBS;

    chdir(ROOT);
    exec("composer build -q");
    exec("
      rm -rf tests/unit/tmp/* &&
      mkdir $tmp/build &&
      cp $root/build/wpdemo.phar $tmp/build/wpdemo.phar &&
      cp $stubs/wp-config.php $tmp/wp-config.php &&
      mkdir -p $tmp/wp-content/uploads &&
      cp -R $root/assets/uploads $tmp/wp-content/uploads
    ");

    query("DROP DATABASE wordpress; CREATE DATABASE wordpress; USE wordpress;");

    //Set mock return values
    $host   = exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' wpdemo_db");
    $return = array('setup' => true, 'config'=> "$tmp/wp-config.php", 'host' => $host);
    MockManager::getInstance()->set('getopt', $return);

    //Call CLI
    require("phar://$tmp/build/wpdemo.phar/src/Cli.php");

    //Backup loader assert
    $this->assertFileExists("$tmp/wp-config-backup.php");
    $this->assertFileExists("$tmp/wp-config.php");

    //Insert loader assert
    $this->assertContains("WPDemo Loader", file_get_contents("$tmp/wp-config.php"));

    //Setup files assert
    $this->assertFileExists("$tmp/build/Config.php");
    $this->assertFileExists("$tmp/build/wordpress.sql");
    $this->assertContains("Database: wordpress", file_get_contents("$tmp/build/wordpress.sql"));
    $this->assertFileExists("$tmp/build/uploads");
  }

  public function testPharPopulate()
  {
    //Prepare
    query("DROP DATABASE wordpress; CREATE DATABASE wordpress; USE wordpress;");
    $tmp  = TMP;
    $path = "$tmp/build/Config.php";
    file_put_contents($path, str_replace("15", "1", file_get_contents($path)));

    //Create mock
    $host   = exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' wpdemo_db");
    $return = array('populate' => true, 'config'=> "$tmp/wp-config.php", 'host' => $host);
    MockManager::getInstance()->set('getopt', $return);
    MockManager::getInstance()->set('bin2hex', 'toast');

    //Call CLI
    require("phar://$tmp/build/wpdemo.phar/src/Cli.php");

    //Uploads and db asserts
    $this->assertFileExists("$tmp/wp-content/demos/wpdemo_toast");
    $this->assertFileEquals("$tmp/build/uploads", "$tmp/wp-content/demos/wpdemo_toast");
    $query = query("SELECT * FROM wpdemo")->fetchAll(\PDO::FETCH_NUM);
    $this->assertEquals(1, count($query));
    $this->assertEquals('wpdemo_toast', $query[0][0]);
  }

  public function testInstanceLimit()
  {
    //Create mocks
    MockManager::getInstance()->set('bin2hex', 'butter');

    //Call CLI
    $tmp  = TMP;
    require("phar://$tmp/build/wpdemo.phar/src/Cli.php");

    //Uploads and db asserts
    $this->assertFileExists("$tmp/wp-content/demos/wpdemo_toast");
    $this->assertFileEquals("$tmp/build/uploads", "$tmp/wp-content/demos/wpdemo_toast");
    $query = query("SELECT * FROM wpdemo")->fetchAll(\PDO::FETCH_NUM);
    $this->assertEquals(1, count($query));
    $this->assertEquals('wpdemo_toast', $query[0][0]);
  }
}
