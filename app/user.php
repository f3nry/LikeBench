<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('FB_APP_ID', '208264955898181');
define('FB_APP_SECRET', 'bc366820186a31fbbf77490d1eaaeba4');
define('SITE_URL', 'http://likebench.net');

require_once __DIR__ . "/libs/facebook/facebook.php";

/**
 * Description of user
 *
 * @author paul
 */
class User extends M2 {
  protected static $facebook;
  
  public static function init() {
    session_start();
    
    self::$facebook = new Facebook(array(
	'appId' => FB_APP_ID,
	'secret' => FB_APP_SECRET
    ));
  }
  
  public static function fbauth() {
    return self::$facebook->getUser();
  }
}

?>
