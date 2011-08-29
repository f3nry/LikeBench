<?php

require __DIR__ . '/../lib/base.php';

/**
 * Description of App
 *
 * @author paul
 */
class App extends F3 { 
  public static function array_get($array, $key) {
    return (isset($array[$key]) ? $array[$key] : false);
  }
}

?>
