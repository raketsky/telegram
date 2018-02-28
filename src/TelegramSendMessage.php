<?php
namespace Raketsky\Component;

use Exception;

trait TelegramSendMessage
{
	private $token = null;
	private $adminChatId = null;
	
	public function setTelegramSendMessageToken($token)
	{
		$this->token = $token;
	}
	
	public function setTelegramSendMessageAdmin($chatId)
	{
		$this->adminChatId = $chatId;
	}
	
	public function sendMessageToAdmin($message, array $keyboard = null, $markDown = true, $disableNotification = false)
	{
		if ($this->adminChatId) {
			$chatId = $this->adminChatId;
		} else if (defined('TELEGRAM_SEND_MESSAGE_ADMIN') && TELEGRAM_SEND_MESSAGE_ADMIN) {
			$chatId = TELEGRAM_SEND_MESSAGE_ADMIN;
		} else {
			throw new TelegramSendMessageException('No admin token provided');
		}
		
		return $this->sendMessage($chatId, $message, $keyboard, $markDown, $disableNotification);
	}
	
	/**
	 * @param $chatId
	 * @param $message
	 * @param array|null $keyboard
	 * @param bool|true $markDown
	 * @param bool|false $disableNotification Sends the message silently. Users will receive a notification with no sound.
	 * @return bool
	 * @throws TelegramSendMessageException
	 */
	public function sendMessage($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false)
	{
		return $this->sendMessageRaw('text', $chatId, $message, $keyboard, $markDown, $disableNotification);
	}
	public function sendImage($chatId, $image, array $keyboard = null, $markDown = true, $disableNotification = false)
	{
		return $this->sendMessageRaw('image', $chatId, $image, $keyboard, $markDown, $disableNotification);
	}
	
	public function sendMessageRaw($type, $chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false)
	{
		if ($this->token) {
			$token = $this->token;
		} else if (defined('TELEGRAM_SEND_MESSAGE_TOKEN') && TELEGRAM_SEND_MESSAGE_TOKEN) {
			$token = TELEGRAM_SEND_MESSAGE_TOKEN;
		} else {
			throw new TelegramSendMessageException('No token provided');
		}
		
        $url = 'https://api.telegram.org/bot';
		
		$url = $url.$token.'/sendMessage?chat_id='.$chatId;
		if ($type == 'text' && $message !== null) {
			$url .= '&text='.urlencode($message);
			$url .= '&disable_web_page_preview=1'; // Disables link previews for links in this message
			if ($markDown) {
				$url .= '&parse_mode=markdown';
			}
		} else if ($type == 'image') {
			$url .= '&photo='.urlencode($message);
		}
		if ($disableNotification) {
			$url .= '&disable_notification=1';
		}
		if ($keyboard) {
			$url .= '&reply_markup='.json_encode($keyboard);
		}
		$ch = curl_init();
		$optArray = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch, $optArray);
		$result = curl_exec($ch);
		$status = json_decode($result);
		curl_close($ch);
		
		sleep(1);
		
		if ((!$status || !isset($status->ok) || !$status->ok) && !$this->sendMessage($chatId, $message, $keyboard, false, $disableNotification)) {
			return false;
		}
		
		return (bool) $status->ok;
	}
}

class TelegramSendMessageException extends Exception {}