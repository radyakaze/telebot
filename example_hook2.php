<?php

$token = 'BOT TOKEN';
$botname = 'BOT USERNAME';

require __DIR__.'/vendor/autoload.php';

$tg = new TeleBot\Api($token, $botname);

// Weather command, /weather city
$tg->cmd('weather', function($location){
  if (empty($location)) {
    return 'You must specify location in format: /weather <city>';
  } else {
    $ch = curl_init();
    $curlConfig = [
      CURLOPT_URL => 'http://api.openweathermap.org/data/2.5/weather?q=' . urlencode($location) . '&units=metric',
      CURLOPT_RETURNTRANSFER => true
    ];
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);

    $loc = json_decode($result);

    if (is_null($loc) || $loc->cod != 200) {
      return 'Can not find weather for location: ' . $location;
    }
    $output = 'The temperature in ' . $loc->name . ' (' . $loc->sys->country . ') is ' . $loc->main->temp . '°C'."\n";
    $output .= 'Current conditions are: ' . $loc->weather[0]->description;

    switch (strtolower($loc->weather[0]->main)) {
      case 'clear':
          $output .= ' ☀';
          break;

      case 'clouds':
          $output .= ' ☁☁';
          break;

      case 'rain':
          $output .= ' ☔';
          break;

      case 'thunderstorm':
          $output .= ' ☔☔☔☔';
          break;
    }

    return $output;
  }
});

$tg->run();