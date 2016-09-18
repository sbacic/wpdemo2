<?php

namespace sbacic\wpdemo;
use sbacic\wpdemo\Replacers;

class Loader {
  protected $prefix, $paths, $pdo, $config, $cookie;
  protected $instances = array();
  protected $active    = null;

  public function __GET($value='')
  {
    $instances = array_filter($this->instances, function($instance){
      return isset($instance['used']);
    });

    if ($value == 'free') {
      return array_filter($instances, function($instance){
        return $instance['used'] == 0;
      });
    } else if ($value == 'used') {
      return array_filter($instances, function($instance){
        return $instance['used'] == 1;
      });
    }
  }

  public function __construct($prefix, $config, $pdo, $paths = array(), $cookie = null)
  {
    $this->prefix = $prefix;
    $this->paths  = $paths;
    $this->config = $config;
    $this->cookie = $cookie;
    $this->pdo    = $pdo;
  }

  public function load()
  {
    //Load instances from the database
    $pdo     = $this->pdo;
    $sql     = "SELECT id, used, assigned FROM wpdemo";
    $query   = $pdo->query($sql);
    $results = $query->fetchAll(\PDO::FETCH_ASSOC);

    $this->instances = $results !== null ? $results : array();

    //Assign the active instance for this session
    $prefix = isset($this->cookie) && $this->cookie->get() ? $this->cookie->get() : false;

    if ($this->validSession($prefix, $this->used)){
      $this->active = $prefix;
    } else if ($this->cookie){
      $this->cookie->destroy();
    }
  }

  protected function validSession($prefix, $instances)
  {
    return array_filter($instances,
      function($instance) use($prefix){
        return $instance['id'] == $prefix;
      });
  }

  public function getPrefix()
  {
    return $this->active ? $this->active . '_' : false;
  }

  public function getUploadDir($prefix = null)
  {
    //Return the prefixed path, if it exists
    if ($prefix != null) {
      return $this->paths['wpDemoMediaDir'] . '/' . $prefix;
    }

    //Returns the path for the session, if available
    return $this->active ?
      sprintf('%s/%s', $this->paths['wpDemoMediaDir'], $this->active):
      false;
  }

  public function populate($force = false)
  {
    $diff = $force ? 1 : $this->config['maxinstances'] - count($this->instances);

    for ($i = 0; $i < $diff; $i++) {
      $this->generateInstance();
    }
  }

  protected function generateInstance()
  {
    $paths      = $this->paths;
    $dump       = $paths['exists']['backup'];
    $from       = $paths['exists']['uploads'];
    $to         = $paths['wpDemoMediaDir'];
    $defaultDir = $paths['wpOriginalMediaDir'];

    $pdo         = $this->pdo;
    $helper      = new Helper();
    $prefix      = bin2hex(openssl_random_pseudo_bytes(8));
    $to          = $to . DIRECTORY_SEPARATOR . 'wpdemo_' . $prefix;
    $replacers   = array(
      Replacers::prefix("wpdemo_$prefix".'_'),
      Replacers::uploads(
        $helper->relpath($this->paths['wpconfig'], $defaultDir), $helper->relpath($this->paths['wpconfig'], $to)
      )
    );
    $helper->restore($pdo, $dump, $replacers);
    $entry       = $this->createEntry($prefix);
    $helper->cloneDir($from, $to);

    return $entry;
  }

  protected function createEntry($prefix)
  {
    $pdo = $this->pdo;
    $sql = "INSERT INTO wpdemo (id, used) VALUES ('wpdemo_$prefix', 0)";
    $pdo->query($sql);
    return "wpdemo_$prefix";
  }

  public function purge()
  {
    $helper   = new Helper();
    $prefix   = 'wpdemo_';
    $helper->removeDir($this->paths['wpDemoMediaDir']);
    $helper->removeTables($this->pdo, $prefix);
    $this->pdo->query("TRUNCATE TABLE wpdemo");
  }

  public function cleanup()
  {
    $helper   = new Helper();
    $pdo      = $this->pdo;
    $lifetime = $this->config['lifetime'];
    $from     = $this->paths['wpDemoMediaDir'];
    $used     = array_filter($this->used, function($instance)use($lifetime){
      return $instance['assigned'] < date("Y-m-d H:i:s", strtotime("-$lifetime minutes"));
    });

    foreach($used as $row => $value) {
      $prefix   = $value['id'];
      $path     = $from . DIRECTORY_SEPARATOR . $prefix;
      $helper->removeDir($path);
      $helper->removeTables($pdo, $prefix);
    }

    $sql = "DELETE FROM wpdemo WHERE used = 1 AND (assigned + INTERVAL $lifetime MINUTE) < NOW()";
    $pdo->query($sql);
  }

  public function assignInstance()
  {
    $pdo          = $this->pdo;
    $row          = array_pop($this->free);
    $autogenerate = $this->config['autoGenerate'];

    if (! $row && $autogenerate == true) {
      $this->populate(true);
      $this->load();
      $row = array_pop($this->free);
    }

    if (! $row) {
      return false;
    } else {
      $prefix = $row['id'];
      $sql    = "UPDATE wpdemo SET used=1, assigned=NOW() WHERE id = '$prefix'";
      $pdo->query($sql);
      $this->active = $prefix;
      $this->cookie->set($prefix);
      //Mark as used
      foreach ($this->instances as $key => $value) {
        if ($value['id'] == $prefix) {
          $this->instances[$key]['used'] = 1;
        }
      }

      return true;
    }
  }
}

?>
