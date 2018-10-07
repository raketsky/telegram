<?php
namespace Raketsky\Telegram;

use Raketsky\Component\TelegramSendMessage;

class Client
{
	use TelegramSendMessage;
	
	public function __construct($token, $adminChatId = null)
	{
		$this->setTelegramSendMessageToken($token);
		$this->setTelegramSendMessageAdmin($adminChatId);
	}
}
