<?php

use sbacic\wpdemo\Diag;

class DiagTest extends PHPUnit_Framework_TestCase {

  public function setUp()
  {
    chdir(UNIT);
    exec("rm -rf tmp/* &&
    cp stubs/wp-config.php tmp/wp-config.php &&
    cp stubs/wp-config-modified.php tmp/wp-config-modified.php &&
    mkdir -p tmp/wp-content/demos/wpdemo_butter &&
    mkdir -p tmp/wp-content/demos/wpdemo_toast &&
    mkdir -p tmp/build/uploads &&
    touch tmp/build/wpdemo.phar tmp/build/Config.php tmp/build/wordpress.sql tmp/wp-config-backup.php
    ");
  }

  public function testFilesExist()
  {
    $diag     = new Diag();

    //Build files
    $dir      = "tmp/build";
    $actual   = $diag->fileCheck($dir);
    $expected = array(
      ["$dir/Config.php", 664],
      ["$dir/wordpress.sql", 664],
      ["$dir/wpdemo.phar", 664],
      ["$dir/uploads", 775]
    );
    sort($expected);
    $this->assertEquals($expected, $actual);

    //Wordpress root
    $dir      = "tmp";
    $actual   = $diag->fileCheck($dir);
    $expected = array(
      ["$dir/wp-config.php", 664],
      ["$dir/wp-config-backup.php", 664]
    );
    sort($expected);
    $this->assertEquals($expected, $actual);

    //Cloned media upload dir
    $dir      = "tmp/wp-content/demos";
    $actual   = $diag->fileCheck($dir);
    $expected = array(
      ["$dir/wpdemo_butter", 775],
      ["$dir/wpdemo_toast", 775]
    );
    sort($expected);
    $this->assertEquals($expected, $actual);
  }

  public function testFileDiff()
  {
    $diag     = new Diag();
    $before   = [['a', 775], ['b', 775], ['c', 775]];
    $after    = [['a', 775], ['b', 775], ['d', 775]];
    $returned = $diag->diffFiles($before, $after);
    $expected = [
      'created'   => ['d'],
      'deleted'   => ['c'],
      'unchanged' => ['a','b']
    ];

    $this->assertEquals($expected, $returned);
  }

  public function testDbState()
  {
    $pdo      = new MockPDO([
      "SELECT id, used, assigned FROM wpdemo" => [
        ['wpdemo_butter', '0', null],
        ['wpdemo_toast', '1', date("Y-m-d H:i:00")]
      ],
      "SHOW TABLES LIKE 'wpdemo%'" => [
        ['wpdemo_butter_options'],
        ['wpdemo_toast_options']
      ]
    ]);
    $diag     = new Diag($pdo);
    $actual   = $diag->fetchDbState(5);
    $expected = array(
      ["wpdemo_butter", '0', null, 1, null],
      ["wpdemo_toast", '1', date("Y-m-d H:i:00"), 1, 4]
      #[id, used, assigned, num tables, dir, time left]
    );

    $this->assertEquals($expected, $actual);
  }

  public function testTablesDiff()
  {
    $diag     = new Diag();
    $before   = [
      ["wpdemo_butter", '0', null, 1, null],
      ["wpdemo_bacon", '0', null, 1, null],
      ["wpdemo_eggs", '1', date("Y-m-d H:i:00"), 1, 4]
    ];
    $after    = [
      ["wpdemo_butter", '1', null, 1, null],
      ["wpdemo_toast", '0', null, 1, null],
      ["wpdemo_eggs", '1', date("Y-m-d H:i:00"), 1, 4]
    ];
    $returned = $diag->diffTables($before, $after);
    $expected = [
      // ["wpdemo_butter", '1', null, 1, null, 'created|deleted|assigned|unchanged']
      "wpdemo_butter" => ["wpdemo_butter", '1', null, 1, null, 'assigned'],
      "wpdemo_bacon"  => ["wpdemo_bacon", '0', null, 1, null, 'deleted'],
      "wpdemo_eggs"   => ["wpdemo_eggs", '1', date("Y-m-d H:i:00"), 1, 4, 'unchanged'],
      "wpdemo_toast"  => ["wpdemo_toast", '0', null, 1, null, 'created'],

    ];

    $this->assertEquals($expected, $returned);
  }

  public function testLoaderInserted()
  {
    $diag     = new Diag();
    $actual   = $diag->isLoaderInserted("tmp/wp-config.php");
    $this->assertFalse($actual);

    $actual   = $diag->isLoaderInserted("tmp/wp-config-modified.php");
    $this->assertTrue($actual);

    $actual   = $diag->isLoaderInserted("tmp/wp-config-missing.php");
    $this->assertNull($actual);
  }
}
?>
