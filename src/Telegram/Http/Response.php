<?php
namespace Raketsky\Telegram\Http;

class Response
{
    private $update;

    public $updateId = null;
    public $messageId = null;
    public $userId = null;
    public $isBot = null;
    public $firstName = '';
    public $lastName = '';
    public $username = '';
    public $language = null;
    public $chatId = null;
    public $chatTitle = null;
    public $chatType = null;
    public $text = null;

    public function __construct($update)
    {
        if (is_string($update)) {
            $update = json_decode($update, true);
        }
        $this->update = $update;

        $this->fillAttributes($this->update);
    }

    public function getUserDisplayName()
    {
        if ($this->firstName) {
            return $this->firstName;
        }
        if ($this->lastName) {
            return $this->lastName;
        }
        if ($this->username) {
            return '@'.$this->username;
        }
        return 'user';
    }
    
    public function getUpdate()
	{
		return $this->update;
	}

    private function fillAttributes(array $update)
    {
        if (isset($update['update_id'])) {
            $this->updateId = $update['update_id'];
        }
        if (isset($update['message'])) {
            if (isset($update['message']['message_id'])) {
                $this->messageId = $update['message']['message_id'];
            }
            if (isset($update['message']['from'])) {
                $this->userId = $update['message']['from']['id'];
                $this->isBot = $update['message']['from']['is_bot'];
                if (isset($update['message']['from']['first_name'])) {
                    $this->firstName = $update['message']['from']['first_name'];
                }
                if (isset($update['message']['from']['last_name'])) {
                    $this->lastName = $update['message']['from']['last_name'];
                }
                if (isset($update['message']['from']['username'])) {
                    $this->username = $update['message']['from']['username'];
                }
                if (isset($update['message']['from']['language_code'])) {
                    $this->language = $update['message']['from']['language_code'];
                }
            }
            if (isset($update['message']['chat'])) {
                $this->chatId = $update['message']['chat']['id'];
                $this->chatTitle = $update['message']['chat']['title'];
                $this->chatType = $update['message']['chat']['type'];
            }
            $this->text = $update['message']['text'];
        }
    }
}
