<?php
  namespace sbacic\wpdemo;

    class MockManager {
      protected static $instance;
      protected $mocks = array();

      protected function __construct()
      {

      }

      public static function getInstance()
      {
        if (! self::$instance) {
          self::$instance = new MockManager();
        }

        return self::$instance;
      }

      public function get($function)
      {
        return isset($this->mocks[$function])
          ? $this->mocks[$function]
          : null ;
      }

      public function set($function, $return)
      {
        $this->mocks[$function] = $return;
      }
    }

    //Mocked functions
    function getopt()
    {
      $return = MockManager::getInstance()->get('getopt');
      return $return;
    }

    function bin2hex()
    {
      $return = MockManager::getInstance()->get('bin2hex')
        ? MockManager::getInstance()->get('bin2hex')
        : 'butter';
        
      return $return;
    }
?>
