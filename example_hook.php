<?php
$token = 'BOT TOKEN';
$botname = 'BOT USERNAME';

require __DIR__.'/vendor/autoload.php';

$tg = new TeleBot\Api($token, $botname);

// Simple command : /hello => Hello world!
$tg->cmd('hello', 'Hello world!');

// Simple command with parameter : /echo telebot => telebot
$tg->cmd('echo', function($text){
  if (isset($text)) {
    return $text;
  } else {
    return '/echo <text>';
  }
 });
 
$tg->run();