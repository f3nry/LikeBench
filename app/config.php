<?php

date_default_timezone_set("America/Chicago");

App::set('CACHE', FALSE);
App::set('DEBUG', 1);
App::set('AUTOLOAD', 'app/');

if(strpos($_SERVER['HTTP_HOST'], "local") === false) {
  App::set('DB', new DB(
    'mysql:host=mysql.mphwebsystems.com;port=3306;dbname=likebench',
    'likebench',
    'QHUfbvGx6fSYX5sQ'
  ));
} else {
  App::set('DB', new DB(
    'mysql:host=localhost;port=3306;dbname=likebench',
    'root',
    ''
  ));
}

App::set('host', $_SERVER['HTTP_HOST']);