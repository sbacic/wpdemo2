<?php

namespace sbacic\wpdemo;

class Cookie {
  protected $prefix;
  public function __construct()
  {
    $this->prefix = isset($_COOKIE) && isset($_COOKIE['wpdemo']) ? $_COOKIE['wpdemo'] : null;
  }

  public function hasSession() {
    return $this->prefix !== null;
  }

  public function get() {
    return $this->prefix;
  }

  public function set($prefix) {
    $this->prefix = $prefix;
    setcookie('wpdemo', $prefix, time() + 3600, '/');
    $_COOKIE['wpdemo'] = $prefix;
  }

  public function destroy()
  {
    $this->prefix      = null;
    $_COOKIE['wpdemo'] = null;
    setcookie('wpdemo', '', time() - 3600, '/');
  }

}

?>
