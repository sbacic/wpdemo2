<?php
namespace sbacic\wpdemo;

class Helper {

  public static function setupConnection($override = array())
  {
    $username = isset($override['user']) ? $override['user'] : DB_USER;
    $password = isset($override['pass']) ? $override['pass'] : DB_PASSWORD;
    $database = isset($override['name']) ? $override['name'] : DB_NAME;
    $host     = isset($override['host']) ? $override['host'] : DB_HOST;

    $format = "mysql:dbname=%s;host=%s;";
    $dsn    = sprintf($format, $database, $host);

    return new \PDO($dsn, $username, $password, array(1002 => 'SET NAMES utf8'));
  }

  /**
   * Accepts two paths and returns the second path relative to
   * to the first one. Works with phar files, but does not work
   * with files inside phar archives. For usage examples,
   * see HelperTest.
   */
  public function relpath($one, $two)
  {
    $one = str_replace('phar://', '', $one, $c1);
    $two = str_replace('phar://', '', $two, $c2);

    $ap2one = $one[0] === '/' ? $one : realpath($one);
    $ap2two = $two[0] === '/' ? $two : realpath($two);

    if (!$ap2one || !$ap2two) {
      $target = ! $ap2one ? $one : $two;
      throw new \Exception("$target does not exist.", 1);
    }

    $pcs1   = explode(DIRECTORY_SEPARATOR, (is_file($ap2one) ? dirname($ap2one) : $ap2one));
    $pcs2   = explode(DIRECTORY_SEPARATOR, (is_file($ap2two) ? dirname($ap2two) : $ap2two));

    while(! empty($pcs1) && ! empty($pcs2) && $pcs1[0] === $pcs2[0]) {
      array_shift($pcs1);
      array_shift($pcs2);
    }

    $c    = count($pcs1);
    $dots = $c > 0 ? implode(DIRECTORY_SEPARATOR, array_fill(0, $c, '..')) . '/' : '';
    is_file($ap2two) ? array_push($pcs2, basename($ap2two)) : null;
    $path = sprintf('%s%s', $dots, implode(DIRECTORY_SEPARATOR, $pcs2));

    return $c2 > 0 && is_file($two) ? sprintf('phar://%s', $path): $path;
  }

  public function cloneDir($from, $to)
  {
      if (file_exists($from) === false) {
          return false;
      } else {
          mkdir($to, fileperms($from), true);

          foreach (
              $iterator = new \RecursiveIteratorIterator(
                  new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS),
                  \RecursiveIteratorIterator::SELF_FIRST) as $item
                  ) {
                  $subpath = $to . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                  if ($item->isDir()) {
                      mkdir($subpath, fileperms($item->getPathname()));
                  } else {
                      copy($item, $subpath);
              }
          }
      }
  }

  public function removeDir($prefix)
  {
    $uploadDirs = glob($prefix . '*');

    foreach ($uploadDirs as $dir) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir . '/', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file)
            $file->isDir() === true ? rmdir($file->getRealPath()) : unlink($file->getRealPath());

        rmdir($dir);
    }
  }

  public function removeTables($pdo, $prefix)
  {
    $escaped = str_replace('_', '\_', $prefix); //Underscores just happen to be a wildcard in SQL LIKE statements, so we need to work around this
    $sql     = "SHOW TABLES LIKE '$prefix%'";
    $query   = $pdo->query($sql);
    $tables  = $query->fetchAll(\PDO::FETCH_COLUMN, 0);

    $toDrop  = array();

    foreach ($tables as $table) {
        $toDrop[] = $table;
    }

    $sql     = sprintf("DROP TABLE %s;", implode(', ', $toDrop));

    $pdo->query($sql);
  }

  public function dump($username, $password, $host, $database, $path, $prefix = null)
  {
    if ($prefix !== null) {
      $pdo = Helper::setupConnection(array(
        'user' => $username,
        'pass' => $password,
        'host' => $host,
        'name' => $database
      ));

      $escaped  = str_replace('_', '\_', $prefix);
      $tables   = $pdo->query("SHOW TABLES LIKE '$escaped%'")->fetchAll(\PDO::FETCH_NUM);
      $tables   = array_map(function($row){ return $row[0]; }, $tables);
      $tables   = implode(' ', $tables);
    } else {
      $tables   = '';
    }

    $command    = "mysqldump -h$host -u$username -p'$password' $database $tables > $path";
    exec($command);
  }

  public function restore($pdo, $path, $replacers = array())
  {
    $dump = file_get_contents($path);
    foreach ($replacers as $replacer) {
       $dump = $replacer['function']($dump, $replacer['args']);
    }

    $pdo->query($dump);
  }

}
?>
