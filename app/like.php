<?php

/**
 * Description of like
 *
 * @author paul
 */
class Like extends Axon {
  protected $count = null;  
  
  public function count() {    
    if($this->count == null) {
      $count_result = $this->select("COUNT(id) as count", NULL, NULL, NULL, 0, 0, false);

      if($count_result) {
        $this->count = $count_result[0]['count'];
      }
    }
    
    return $this->count;
  }
  
  public function getPages($page) {
    $total_pages = ceil($this->count() / 10);
    
    $pages = array();
    
    for($i = 1; $i <= $total_pages; $i++) {
      $pages[] = $i;
    }
    
    return $pages;
  }
  
  public function __construct($_id = "") {
    parent::__construct('likes');
    
    if(!empty($_id)) {
      $this->load('id="' . $_id . '"');
    }
  }
  
  public function beforeSave() {
    $this->ip_address = $_SERVER['REMOTE_ADDR'];
    $this->referer = $_SERVER['HTTP_REFERER'];
    $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
    $this->created_at = time();
  }
  
  public function getUrl() {
    return 'http://' . $this->id . '.' . App::get('host') . '/likes/' . $this->id;
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
