<?php

/*!
 * TeleBot
 * Version 0.1
 *
 * Copyright 2015, Radya
 * Released under the MIT license
 */

class TeleBot {

  /**
   * Telegram BOT Token
   *
   * @var string
   */
  protected $token = '';

  /**
   * BOT Username
   *
   * @var string
   */
  protected $botname = '';

  /**
   * commands
   *
   * @var array
   */
  protected $commands = [];

  /**
   * ouput
   *
   * @var string
   */
  protected $output = '';

  /**
   * message
   *
   * @var array
   */
  protected $message = [];

  /**
   * text
   *
   * @var string
   */
  protected $text = '';

  /**
   * Constructor
   *
   * @param string $token
   * @param string $botname
   */
  public function __construct($token = null, $botname = null) {
    // check if the bot token has been set
    if (is_null($token)) {
      throw new Exception('BOT TOKEN not defined!');
    }

    // check if the bot username has been set
    if (is_null($botname)) {
      throw new Exception('BOT USERNAME not defined!');
    }

    $this->token = $token;

    if ($this->getUpdates() !== false) {
      $this->message = $this->getUpdates();
      $explode = explode(' ', $this->message['message']['text'], 2);
      $this->currentCommand = str_replace('@'.$botname, '', $explode[0]);
      $this->text = isset($explode[1]) ? $explode[1] : '';
    }
  }
  
  /**
   * Set command
   *
   * @param string $command
   * @param string $outout
   * @return array
   */
  public function cmd($command = '', $output) {
    if (is_null($command) || is_null($output)) {
      return;
    } else {
      // set commands list
      if (is_string($output) || is_array($output)) {
        $this->commands['/'.$command] = $output;
      } else {
        $this->commands['/'.$command] = call_user_func($output, $this->text);
      }
    }
  }

 /**
   * Execute command
   *
   * @return mixed
   */
  public function execute() {
    if (isset($this->commands[$this->currentCommand])) {
      $cmd = $this->commands[$this->currentCommand];

      // send request
      if (is_string($cmd)) {
        return $this->send('sendMessage', [
          'chat_id' => $this->message['message']['chat']['id'],
          'text' => $cmd,
          'reply_to_message_id' => $this->message['message']['message_id']
          ]
          );
      } else {
        $action = $cmd['action'];
        unset($cmd['action']);
        return $this->send($action, array_merge([
          'chat_id' => $this->message['message']['chat']['id'],
          'reply_to_message_id' => $this->message['message']['message_id']
          ], $cmd), true
          );
      }
    }
  }

  /**
   * Send request to telegram
   *
   * @param string $action
   * @param array $data
   * @param bool $multipart
   * @return array
   */
  public function send($action, array $data = array(), $multipart = false) {
    $ch = curl_init();
    $config = [
      CURLOPT_URL => 'https://api.telegram.org/bot'.$this->token . '/' . $action,
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true
    ];

    if (!empty($data)) {
      $config[CURLOPT_POSTFIELDS] = $data;
    }

    if ($multipart) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
    }

    curl_setopt_array($ch, $config);
    $result = curl_exec($ch);
    curl_close($ch);

    // return and decode json
    return !empty($result) ? json_decode($result, true) : false;
  }

  /**
   * Get updates
   *
   * @return array
   */
  private function getUpdates() {
    $post = json_decode(file_get_contents('php://input'), true);

    // if not valid json
    if (is_null($post)) {
      return false;
    }

    return $post;
  }

  /**
   * Send photo method
   *
   * @param string $imgPath
   * @param string $caption
   * @return array
   */
  public function sendPhoto($imagePath = null, $caption = null) {
    // get real path
    $path = realpath($imagePath);

    // check if file is exists/valid
    if (!is_file($path)) {
      throw new Exception('Image not exists');
    } else {
      return [
        'action' => 'sendPhoto',
        'photo' => $this->curlFile($path),
        'caption' => $caption
        ];
    }
  }

  /**
   * Send video method
   *
   * @param string $path
   * @param string $caption
   * @return array
   */
  public function sendVideo($path = null, $caption = null) {
    // get real path
    $path = realpath($path);

    // check if file is exists/valid
    if (!is_file($path)) {
      throw new Exception('Image not exists');
    } else {
      return [
        'action' => 'sendVideo',
        'photo' => $this->curlFile($path),
        'caption' => $caption
        ];
    }
  }

  /**
   * Send document method
   *
   * @param string $filePath
   * @return array
   */
  public function sendDocument($filePath = null) {
    //get real path
    $path = realpath($imagePath);

    // check if file is exists/valid
    if (!is_file($path))
      throw new Exception('file not exists');
    else {
      return [
        'action' => 'sendDocument',
        'photo' => $this->curlFile($path)
        ];
    }
  }

  /**
   * Send audio method
   *
   * @param string $filePath
   * @return array
   */
  public function sendAudio($filePath = null) {
    //get real path
    $path = realpath($imagePath);

    // check if file is exists/valid
    if (!is_file($path))
      throw new Exception('file not exists');
    else {
      return [
        'action' => 'sendDocument',
        'audio' => $this->curlFile($path)
        ];
    }
  }

  /**
   * Set webhook
   *
   * @param string $url
   * @return string
   */
  public function setWebhook($url = null) {
    // check if url has been set
    if (empty($url))
      throw new Exception('Hook url is empty!');
    else {
      $result = $this->send('setWebhook', ['url' => $url]);

      if (!$result['ok']) {
        throw new Exception('Webhook was not set! Error: ' . $result['description']);
      }

      echo $result['description'];
    }
  } 

  /**
   * Run TeleBot
   *
   * @return mixed
   */
  public function run() {
    try {
      if ($_SERVER['REQUEST_METHOD'] == 'POST')
        $this->execute();
    } catch (Exception $e) {
      // log telegram errors
      echo $e->getMessage();
    }
  }

  /**
   * create curl file
   *
   * @param string $fileName
   * @return string
   */
  private function curlFile($fileName) {
    // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
    // See: https://wiki.php.net/rfc/curl-file-upload
    if (function_exists('curl_file_create')) {
        return curl_file_create($filename);
    }
 
    // Use the old style if using an older version of PHP
    $value = "@$filename";
 
    return $value;
  }
}