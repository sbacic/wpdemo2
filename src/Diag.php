<?php
  namespace sbacic\wpdemo;

  class Diag {
    protected $pdo;

    function __construct($pdo = null)
    {
      $this->pdo = $pdo;
    }

    public function fileCheck($dir)
    {
      $files    = array(
        "$dir/Config.php", "$dir/wordpress.sql", "$dir/uploads", "$dir/wpdemo.phar",
        "$dir/wp-config.php", "$dir/wp-config-backup.php",
        "$dir/wpdemo_*"
      );
      $pattern  = sprintf('{%s}', implode(',', $files));
      $glob     = glob($pattern, GLOB_BRACE);
      $results  = array();

      foreach ($glob as $value){
        $results[] = array($value, decoct(fileperms($value) & 0777));
      }
      sort($results);
      return $results;
    }

    public function diffFiles($before, $after)
    {
      $before = array_map(function($value){return $value[0];}, $before);
      $after  = array_map(function($value){return $value[0];}, $after);

      $created    = array_diff($after, $before);
      $destroyed  = array_diff($before, $after);
      $unchanged  = array_intersect($before, $after);

      sort($created);
      sort($destroyed);

      return [
        'created'   => $created,
        'deleted'   => $destroyed,
        'unchanged' => $unchanged
      ];
    }

    public function fetchDbState($expiry = false)
    {
      $pdo     = $this->pdo;
      $sql     = "SELECT id, used, assigned FROM wpdemo";
      $results = $pdo->query($sql)->fetchAll(\PDO::FETCH_NUM);
      $tables  = array_map(function($value) {return $value[0];}, $results);
      $sql     = "SHOW TABLES LIKE 'wpdemo%'";
      $matches = $pdo->query($sql)->fetchAll(\PDO::FETCH_NUM);
      $return  = array();

      foreach($results as $value) {
        $count = count(array_filter($matches,
          function($tableName)use($value){
            return strpos($tableName[0], $value[0]) !== false;}
        ));
        $value[]  = $count;
        $value[]  = $value[1] == true && $expiry !== false ? $this->timeLeft($value[2], $expiry): null;
        $return[] = $value;
      }

      return $return;
    }

    public function diffTables($a, $b)
    {
      #Convert numeric array to associative
      $aa = [];
      $ab = [];

      foreach ($a as $value) {$aa[$value[0]] = $value;}
      foreach ($b as $value) {$ab[$value[0]] = $value;}

      #Merged the previous and after arrays
      $merged = array_merge($aa, $ab);

      #Compare previous and after arrays
      foreach($merged as $key => $value) {
        if (! array_key_exists($key, $ab)) {
          $merged[$key][] = 'deleted';
        } else if (! array_key_exists($key, $aa)) {
          $merged[$key][] = 'created';
        } else if ($ab[$key][1] != $aa[$key][1]) {
          $merged[$key][] = 'assigned';
        } else {
          $merged[$key][] = 'unchanged';
        }
      }

      #Return results
      return $merged;
    }

    protected function timeLeft($assigned, $lifetime)
    {
      $now   = new \DateTime("now");
      $death = new \DateTime("$assigned +$lifetime minutes");
      $left  = $now->diff($death)->i;
      return $left;
    }

    public function isLoaderInserted($path)
    {
      try {
        $haystack = file_get_contents($path);
        $needle   = '#WPDemo Loader';
        return strpos($haystack, $needle) !== false;
      } catch (\Exception $e) {
        return null;
      }
    }
  }
?>
