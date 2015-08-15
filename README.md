# TeleBot - PHP Telegram Bot

A Simple Telegram Bot based on the official [Telegram Bot API](https://core.telegram.org/bots/api)

## Requirements
* PHP 5.4+
* Telegram Bot API Access Token - Talk to [@BotFather](http://telegram.me/BotFather) and generate one [Documentation](https://core.telegram.org/bots#botfather).

## Usage
You must set [WebHook](https://core.telegram.org/bots/api#setwebhook)

Create set.php and put:
```php
<?php
$token = 'BOT TOKEN';
$botname = 'BOT USERNAME';

require dirname(__FILE__).'/TeleBot.php';

$tg = new TeleBot($token, $botname);
$tg->setWebhook('https://domain/path_to_hook.php');
```
And open your set.php via browser

After create hook.php and put:
```php
<?php
token = 'BOT TOKEN';
$botname = 'BOT USERNAME';

require dirname(__FILE__).'/TeleBot.php';

$tg = new TeleBot($token, $botname);

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
 
$f->run();
```

#### Send Photo
```php
$tg->cmd('/upload', $tg->sendPhoto('image/path.jpg'));
```

##### Avaible Method
* sendPhoto($path,$caption);
* sendVideo($path, $caption);
* sendDocument($path);
* sendAudio($path);

## License
TeleBot is under the MIT License

## Credits

Created by [Radya][1].

[0]: https://github.com/radya/telebot
[1]: mailto:radya.38@gmail.com