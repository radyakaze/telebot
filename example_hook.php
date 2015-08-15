<?php

$token = 'BOT TOKEN';
$botname = 'BOT USERNAME';

require dirname(__FILE__).'/TeleBot.php';

$tg = new TeleBot($token, $botname);

// Simple ommand
$tg->cmd('hello', 'Hello world!');

// Simple command with parameter
$tg->cmd('echo', function($text){
  if (isset($text)) {
    return $text;
  } else {
    return '/echo <text>';
  }
 });

/*
$tg->cmd('/upload', $tg->sendPhoto('image/path.jpg'));
*/

$f->run();