<?php
namespace sbacic\wpdemo;

class Replacers
{
  public static function prefix($replacement)
  {
    return array(
      'function' => function($dump, $args) {
        $search   = array(
          'wp_',
          "_$args[0]attachment_metadata",
          "_$args[0]attached_file"
        );
        $replace  = array(
          $args[0],
          '_wp_attachment_metadata',
          '_wp_attached_file'
        );

        return str_replace($search, $replace, $dump);
      },
      'args' => array($replacement)
    );
  }

  public static function uploads($original, $prefixed)
  {
    return array(
      'function' => function($dump, $args) {
        $quoted  = preg_quote($args[0]);
        $search  = "#(localhost:[0-9]{0,6}\/)($quoted)#";
        $replace = sprintf('$1%s', $args[1]);
        return preg_replace($search, $replace, $dump);
      },
      'args' => array($original, $prefixed)
    );
  }
}

?>
