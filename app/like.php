<?php

/**
 * Description of like
 *
 * @author paul
 */
class Like extends M2 {
  public function __construct($_id = "") {
    parent::__construct('likes');
    
    if(!empty($_id)) {
      $this->load(array('_id' => new MongoId($_id)));
    }
  }
  
  public function beforeSave() {
    if($this->dry()) {
      $this->ip_address = $_SERVER['REMOTE_ADDR'];
      $this->referer = $_SERVER['HTTP_REFERER'];
      $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
      $this->created_at = time();
    }
  }
  
  public function getUrl() {
    return 'http://' . $this->_id . '.' . App::get('host') . '/likes/' . $this->_id;
  }
  
  public function getEncodedUrl() {
    return urlencode($this->getUrl());
  }
  
  public function increment() {
    $current = $this->likes;
    
    if(empty($current)) {
      $this->likes = 0;
    }
    
    $this->likes++;
    
    $this->save();
  }
}

?>
