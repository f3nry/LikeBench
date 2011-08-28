<?php

require __DIR__ . '/app/app.php';

date_default_timezone_set("America/Chicago");

App::set('CACHE', FALSE);
App::set('DEBUG', 1);
App::set('AUTOLOAD', 'app/');

App::set('DB', new MongoDb(new Mongo(), 'likebench'));

App::set('host', $_SERVER['HTTP_HOST']);

User::init();
User::fbauth();

App::set('logged_in', (boolean) $_SESSION['_id']);

App::route('GET /', function() { 
  $likes = new Like();
  
  App::set('likes', $likes->find());
  App::set('content', Template::serve('templates/index.html'));
});

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
    
    $like = new Like();
    
    $like->text = $_POST['text'];
    $like->likes = 0;
    $like->updateTrackingInfo();
    
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
      
      var_dump($like); die;
    }
  }
});

App::run();

echo Template::serve('templates/template.htm');