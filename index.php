<?php

require __DIR__ . '/app/app.php';
require __DIR__ . '/app/config.php';

User::init();
User::fbauth();

App::set('logged_in', (boolean) App::array_get($_SESSION, '_id'));

App::route('GET /', function() {
  get_likes("created_at DESC");
});

App::route('GET /popular', function() {  
  get_likes("likes DESC");
});

function get_likes($sort) {
  $likes = new Like();
  
  if(isset($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    $page = 1;
  }
  
  $offset = ($page - 1) * 10;
  
  App::set('likes', $likes->find(null, $sort, 10, $offset));
  App::set('pages_found', ceil($likes->count() / 10));
  App::set('pages', $likes->getPages($page));
  App::set('content', Template::serve('templates/index.html'));
}

App::route('GET /likes/@id', function() {
  $like = new Like(App::get('PARAMS["id"]'));
  
  App::set('like', $like);
  
  App::set('content', Template::serve('templates/like.html'));
});

App::route('GET /add', function() { 
  App::set('content', Template::serve('templates/add.html'));
});

App::route('POST /add', function() {
  if(!empty($_POST)) {
    App::scrub($_POST);
    
    if(empty($_POST['text'])) {
      App::reroute('/');
      exit;
    }
    
    $like = new Like();
    
    $like->text = $_POST['text'];
    $like->likes = 0;
    
    $like->save();
    
    App::reroute('/');
  }
  
  App::reroute('/add');
});

App::route('POST /events/like', function() {
  if(!empty($_POST)) {
    $parts = explode("/", $_POST['href']);
    
    $index = count($parts) - 1;
    
    if(isset($parts[$index])) {
      $id = $parts[$index];
      
      $like = new Like($id);
      $like->increment();

      $like->save();
    }
  }
});

App::run();

echo Template::serve('templates/template.htm');