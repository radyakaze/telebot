# TeleBot - PHP Telegram Bot

A Simple Telegram Bot based on the official [Telegram Bot API](https://core.telegram.org/bots/api)

## Requirements
* PHP 5.3+
* Telegram Bot API Access Token - Talk to [@BotFather](http://telegram.me/BotFather) and generate one. [Documentation](https://core.telegram.org/bots#botfather).

## Installation

#### Install Through Composer

You can either add the package directly by firing this command

```cli
$ composer require radyakaze/telebot
```

## Usage
You must set [WebHook](https://core.telegram.org/bots/api#setwebhook)

Create set.php and put:
```php
<?php
$token = 'BOT TOKEN';
$botname = 'BOT USERNAME';

require __DIR__.'/vendor/autoload.php';

$tg = new TeleBot\Api($token, $botname);
$tg->setWebhook('https://domain/path_to_hook.php');
```
And open your set.php via browser

After create hook.php and put:
```php
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
```

#### Send Photo
```php
$tg->cmd('/upload', array(
  'type' => 'photo',
  'send' => 'path/to/photo.jpg'
);
// OR
$tg->cmd('/upload2', function($text) {
  return array(
    'type' => 'photo',
    'send' => 'path/to/photo.jpg'
  )
});
```

### Send Location
```php
<?php
$tg->cmd('/myloc', function($text) {
  return array(
    'type' => 'location',
    'send' => array(-7.61, 109.51) // Gombong, Kebumen, Indonesia, you can integrate with google maps api
  )
});
```

### Avaible Types
* text, optional: web_preview (default: true) 
* photo, optional: caption
* video, optional: caption
* document
* audio
* location, required: send as array($latitude, $longitude)


## License
TeleBot is under the MIT License

## Credits

Created by [Radya][1].

[0]: https://github.com/radya/telebot
[1]: mailto:radya.38@gmail.com