<?php

/*!
 * Telebot
 * Version 0.1
 *
 * Copyright 2015, Radya
 * Released under the MIT license
 */

namespace TeleBot;

class Api {

  /**
   * Telegram BOT Token
   *
   * @var string
   */
  protected $token = '';

  /**
   * commands
   *
   * @var array
   */
  protected $commands = array();

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
  protected $message = array();

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
    if (is_null($token))
      throw new Exception('BOT TOKEN not defined!');

    // check if the bot username has been set
    if (is_null($botname))
      throw new Exception('BOT USERNAME not defined!');

    $this->token = $token;
    $this->request = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';

    if ($this->request == 'POST') {
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
  public function cmd($command, $output) {
    // if output is function
    if (is_object($output))
      $output = call_user_func($output, $this->text);

    // if output is a string
    if (is_string($output)) {
      $output = array(
        'type' => 'text',
        'send' => $output
        );
    }

    // set command lists
    $this->commands['/'.$command] = $output;
  }

 /**
   * Execute command
   *
   * @return mixed
   */
  public function execute() {
    if (isset($this->commands[$this->currentCommand])) {
      $cmd = $this->commands[$this->currentCommand];
      $multipart = false;

      // type of message
      switch($cmd['type']) {

        // Photo
        case 'photo':
          $action = 'sendPhoto';
          $caption = isset($cmd['caption']) ? '' : $cmd['caption'];
          $param = array(
            'photo' => $this->curlFile($cmd['send']),
            'caption' => $caption
            );
        break;

        // Video
        case 'video':
          $action = 'sendVideo';
          $caption = isset($cmd['caption']) ? '' : $cmd['caption'];
          $param = array(
            'video' => $this->curlFile($cmd['send']),
            'caption' => $caption
            );
        break;

        // Audio
        case 'audio':
          $action = 'sendAudio';
          $param = array(
            'audio' => $this->curlFile($cmd['send'])
            );
        break;

        // Document
        case 'document':
          $action = 'sendDocument';
          $param = array(
            'document' => $this->curlFile($cmd['send'])
            );
        break;

        // Location
        case 'location':
          $action = 'sendLocation';
          $param = array(
            'latitude' => $cmd['send'][0],
            'longitude' => $cmd['send'][1]
            );
        break;

        // Text
        default:
          $action = 'sendMessage';
          $preview = !isset($cmd['web_preview']) ? true : $cmd['web_preview'];
          $param = array(
            'text' => $cmd['send'],
            'disable_web_page_preview' => $preview
            );
        break;
      }
      // send request
      $param['chat_id'] = $this->message['message']['chat']['id'];
      $param['reply_to_message_id'] = $this->message['message']['message_id'];
      $this->send($action,  $param, $multipart);
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
  public function send($action, $data = array(), $multipart = false) {
    $ch = curl_init();
    $config = array(
      CURLOPT_URL => 'https://api.telegram.org/bot'.$this->token . '/' . $action,
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true
    );

    if ($multipart)
      $config[CURLOPT_HTTPHEADER] = array('Content-Type: multipart/form-data');

    if (!empty($data))
      $config[CURLOPT_POSTFIELDS] = $data;

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
    if (is_null($post))
      throw new Exception('Invalid JSON');

    return $post;
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
      die('Hook url is empty!');
    else {
      $result = $this->send('setWebhook', array('url' => $url));

      if (!$result['ok'])
        die('Webhook was not set! Error: ' . $result['description']);

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
      if ($this->request == 'POST')
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
    // set realpath
    $filename = realpath($fileName);

    // check file
    if (!is_file($filename))
      throw new Exception('File does not exists');

    // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
    // See: https://wiki.php.net/rfc/curl-file-upload
    if (function_exists('curl_file_create'))
        return curl_file_create($filename);
 
    // Use the old style if using an older version of PHP
    return "@$filename";
  }
}