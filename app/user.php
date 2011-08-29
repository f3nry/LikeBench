<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('FB_APP_ID', '208264955898181');
define('FB_APP_SECRET', 'bc366820186a31fbbf77490d1eaaeba4');

if(isset($_SERVER['HTTP_REFERER'])) {
  define('SITE_URL', $_SERVER['HTTP_REFERER']);
} else {
  define('SITE_URL', 'http://likebench.local/');
}

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

    $cookie_key = 'fbs_' . FB_APP_ID;

    if (!isset($_COOKIE[$cookie_key])) {
      return false;
    }

    parse_str(trim($_COOKIE[$cookie_key], '\\"'), $args);
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
    if(isset($_SESSION['uid'])) {
      $user = self::byFacebookId($_SESSION['uid']);
    } else {
      $user = new User();
    }

    if ($user->dry()) { //User is dry, create new user
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

  public static function initiate_fb_auth() {
    $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
    
    $dialog_url = "https://www.facebook.com/dialog/oauth?client_id="
	    . FB_APP_ID . "&redirect_uri=" . urlencode(SITE_URL) . "&state="
	    . $_SESSION['state'];

    header("Location: " . $dialog_url);
    exit;
  }
  
  public static function finish_fb_auth($code) {    
    if($_REQUEST['state'] == $_SESSION['state']) {
      $token_url = "https://graph.facebook.com/oauth/access_token?"
       . "client_id=" . FB_APP_ID . "&redirect_uri=" . urlencode(SITE_URL)
       . "&client_secret=" . FB_APP_SECRET . "&code=" . $code;
      
      $response = file_get_contents($token_url);
      $params = null;
      parse_str($response, $params);
      
      return $params;
    }
  }

  /**
   * Perform Facebook authentication, including creating an account on
   * our application.
   *
   * @return type 
   */
  public static function fbauth() {
    if(!empty($_REQUEST['code'])) {
      $user_data = self::finish_fb_auth($_REQUEST['code']);
    } else {    
      $user_data = self::get_facebook_cookie();

      if (!$user_data && isset($_SESSION['_id'])) {
	self::initiate_fb_auth();
      }
    }

    $_SESSION = array_merge($_SESSION, $user_data);

    if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) {
      if (!isset($_SESSION['_id']) && empty($_SESSION['_id'])) {
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
