<?php

  class MockPDO extends PDO
  {
    protected $map = array();
    protected $default;

    function __construct($map = [], $default = null)
    {
      $this->map     = $map;
      $this->default = $default;
    }

    public function query($value)
    {
      if (isset($this->map[$value])){
        $result = $this->map[$value];
      } else {
        $result = $this->default;
      }

      return new MockStatement($result);
    }
  }

  //TLDR: A hack to return different sets for the same subsequent query
  class MultiMock {
    protected $array;
    function __construct($array){
      $this->array = $array;
    }

    public function get()
    {
      return array_shift($this->array);
    }
  }

  class MockStatement extends PDOStatement
  {
    protected $value;

    function __construct($value)
    {
      $this->value = $value;
    }

    public function fetchAll()
    {
      return is_a($this->value, 'MultiMock') ? $this->value->get() : $this->value;
    }
  }
?>
