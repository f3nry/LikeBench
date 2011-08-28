<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('FB_APP_ID', '208264955898181');
define('FB_APP_SECRET', 'bc366820186a31fbbf77490d1eaaeba4');
define('SITE_URL', 'http://likebench.net');

/**
 * User representation. Hooks into and completely depends on Facebook
 * authentication.
 *
 * @author paul
 */
class User extends M2 {
  
  public function __construct() {
    parent::__construct('users');
  }
  
  /**
   * Load User by Facebook ID
   *
   * @param string $facebook_id
   * @return User 
   */
  public static function byFacebookId($facebook_id) {
    $user = new User();
    $user->load(array("facebook_id" => $facebook_id));
    
    return $user;
  }
  
  /**
   * Go ahead and init the session
   */
  public static function init() {
    session_start();
  }
  
  /**
   * Parse and get the Facebook cookie
   *
   * @return array 
   */
  public static function get_facebook_cookie() {
    $args = array();
    parse_str(trim($_COOKIE['fbs_' . FB_APP_ID], '\\"'), $args);
    ksort($args);
    $payload = '';
    foreach ($args as $key => $value) {
      if ($key != 'sig') {
	$payload .= $key . '=' . $value;
      }
    }
    if (md5($payload . FB_APP_SECRET) != $args['sig']) {
      return null;
    }
    return $args;
  }
  
  /**
   * Query the Facebook graph API for the user info
   *
   * @return stdClass 
   */
  public static function get_fb_user() {
    $graph_url = "https://graph.facebook.com/me?access_token=" . $_SESSION['access_token'];
    
    $user = json_decode(file_get_contents($graph_url));
    
    return $user;
  }
  
  /**
   * Make sure the user is saved in our application
   *
   * @return type 
   */
  public static function get() {
    $user = self::byFacebookId($_SESSION['uid']);
    
    if($user->dry()) { //User is dry, create new user
      $profile = self::get_fb_user();

      $user->facebook_id = $profile->id;
      $user->first_name = $profile->first_name;
      $user->last_name = $profile->last_name;
      $user->username = $profile->username;
      $user->gender = $profile->gender;
      $user->timezone = $profile->timezone;

      $user->save();
    }
    
    return $user;
  }
  
  /**
   * Perform Facebook authentication, including creating an account on
   * our application.
   *
   * @return type 
   */
  public static function fbauth() {
    $cookie = self::get_facebook_cookie();
    
    $_SESSION = array_merge($_SESSION, $cookie);
    
    if(isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
      if(empty($_SESSION['_id'])) {
	$user = User::get();
	
	$_SESSION['_id'] = $user->_id;
      }
      
      return true;
    } else {
      return false;
    }
  }
}

?>
