<?php

use sbacic\wpdemo\Cookie;

class CookieTest extends PHPUnit_Framework_TestCase {

  public function setUp()
  {

  }
  
  public function testAll()
  {
    $_COOKIE['wpdemo'] = 'wpdemo_butter';
    $cookie = new Cookie();

    $this->assertEquals(true, $cookie->hasSession());
    $this->assertEquals('wpdemo_butter', $cookie->get());
    $cookie->set('wpdemo_toast');
    $this->assertEquals('wpdemo_toast', $cookie->get());
  }
}
?>
