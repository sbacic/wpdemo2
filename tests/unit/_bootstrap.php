<?php
// Here you can initialize variables that will be available to your tests

//Helper functions
function getPDO()
{
  $db     = 'wordpress';
  $host   = exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' wpdemo_db");
  $user   = 'root';
  $pass   = 'example';

  $pdo    = new \PDO("mysql:dbname=$db;host=$host;", $user, $pass, array(1002 => 'SET NAMES utf8'));

  return $pdo;
}

function query($query)
{
  $query = getPDO()->query($query);
  return $query;
}

//Helper constants
define('ROOT', realpath(__DIR__ . '/../../'));
define('STUBS', ROOT . '/tests/unit/stubs');
define('UNIT', ROOT . '/tests/unit');
define('TMP', ROOT . '/tests/unit/tmp');

//Cleanup and preparation
function cleanup()
{
  $tmp = TMP;
  exec("rm -rf tests/unit/tmp/* && mkdir -p $tmp");
}

//Run tasks
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require(ROOT . '/vendor/autoload.php');
require(STUBS . '/MockPDO.php');
require(STUBS . '/functions.php');
cleanup();
