<?php

use sbacic\wpdemo\Replacers;

class ReplacersTest extends PHPUnit_Framework_TestCase {

  public function testUpload()
  {
    $dump     = "'2016-07-11 13:18:50','',0,'http://localhost:8080/wp-content/uploads/2016/07/FF4D00-0.8.png',0,'attachment','image/png',0);";
    $original = 'wp-content/uploads';
    $prefixed = 'wp-content/demos/uploads';
    $replacer = Replacers::uploads($original, $prefixed);

    $match    = "'2016-07-11 13:18:50','',0,'http://localhost:8080/wp-content/demos/uploads/2016/07/FF4D00-0.8.png',0,'attachment','image/png',0);";

    $result = $replacer['function']($dump, array($original, $prefixed));

    $this->assertEquals($match, $result);
  }
}
