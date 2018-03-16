<?php
namespace Raketsky\Component;

use Exception;
use MindFly\Utils\StringUtil;

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
	
	public function editTextMessage($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('editMessageText', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}
	public function editMessageCaption($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('editMessageCaption', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}
	public function editMessageReplyMarkup($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('editMessageReplyMarkup', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
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
	 * @param array|null $additionalParams
	 * @return bool
	 * @throws TelegramSendMessageException
	 */
	public function sendMessage($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('sendMessage', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}
	
    /**
     * @param $chatId
     * @param $image
     * @param array|null $keyboard
     * @param bool $markDown
     * @param bool $disableNotification
     * @param array $additionalParams
     *
     * @return bool|int
     * @throws TelegramSendMessageException
     */
	public function sendImage($chatId, $image, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('sendPhoto', $chatId, $image, $keyboard, $markDown, $disableNotification, $additionalParams);
	}
	
	public function sendVideo($chatId, $image, array $keyboard = null, $markDown = true, $disableNotification = false)
	{
		return $this->sendMessageRaw('sendVideo', $chatId, $image, $keyboard, $markDown, $disableNotification);
	}
	
	/**
	 * @param int|string $chatId              Unique identifier for the target chat or username of the target channel (in the format @channelusername)
	 * @param int        $fromChatId          Unique identifier for the chat where the original message was sent (or channel username in the format @channelusername)
	 * @param int        $messageId           Message identifier in the chat specified in from_chat_id
	 * @param bool|true  $disableNotification Sends the message silently. Users will receive a notification with no sound.
	 * @return bool
	 * @throws TelegramSendMessageException
	 */
	public function forwardMessage($chatId, $fromChatId, $messageId, $disableNotification = true)
	{
		return $this->sendMessageRaw('forwardMessage', $chatId, $messageId, null, false, $disableNotification, ['from_chat_id' => $fromChatId]);
	}
	
	public function forwardToAdmin($fromChatId, $messageId, $disableNotification = true)
	{
		if ($this->adminChatId) {
			$chatId = $this->adminChatId;
		} else if (defined('TELEGRAM_SEND_MESSAGE_ADMIN') && TELEGRAM_SEND_MESSAGE_ADMIN) {
			$chatId = TELEGRAM_SEND_MESSAGE_ADMIN;
		} else {
			throw new TelegramSendMessageException('No admin token provided');
		}
		
		return $this->sendMessageRaw('forwardMessage', $chatId, $messageId, null, false, $disableNotification, ['from_chat_id' => $fromChatId]);
	}
	
	public function sendTmpMessage($chatId, $text, $callback = null, $waittime = 2)
	{
		$msgId = $this->sendMessage($chatId, $text);
		if ($callback) {
			$callback();
		}
		sleep($waittime);
		$this->deleteMessage($chatId, $msgId);
	}
	
	public function sendMessageRaw($type, $chatId, $message, array $keyboard = null, $markDown = false, $disableNotification = false, array $additionalParams = [])
	{
		if ($this->token) {
			$token = $this->token;
		} else if (defined('TELEGRAM_SEND_MESSAGE_TOKEN') && TELEGRAM_SEND_MESSAGE_TOKEN) {
			$token = TELEGRAM_SEND_MESSAGE_TOKEN;
		} else {
			throw new TelegramSendMessageException('No token provided');
		}
		
		if ($markDown && !isset($additionalParams['parse_mode'])) {
			$additionalParams['parse_mode'] = 'markdown';
		}
		if (!isset($additionalParams['parse_mode']) || strtolower($additionalParams['parse_mode']) != 'html') {
			$message = html_entity_decode($message);
		}
		
        $url = 'https://api.telegram.org/bot';
		
		$url = $url.$token.'/'.$type.'?chat_id='.$chatId;
		if (in_array($type, ['sendMessage', 'editMessageText']) && $message !== null) {
			$url .= '&text='.urlencode($message);
			$url .= '&disable_web_page_preview=1'; // Disables link previews for links in this message
		} else if ($type == 'sendVideo' || $type == 'sendPhoto' && StringUtil::endsWith($message, '.gif')) {
		    if ($type == 'sendPhoto') {
				$url = str_replace('/sendPhoto?', '/sendVideo?', $url);
			}
			$url .= '&video='.$message;
			// TODO add caption $url .= '&caption='.urlencode('This is #gif');
		} else if ($type == 'editMessageCaption') {
			$url .= '&caption='.urlencode($message);
		} else if ($type == 'editMessageReplyMarkup') {
			//$url .= '&caption='.urlencode($message);
		} else if ($type == 'sendPhoto') {
			$url .= '&photo='.$message;
		} else if ($type == 'sendChatAction') {
		    $url .= '&action='.$message;
		} else if (in_array($type, ['deleteMessage', 'forwardMessage'])) {
		    $url .= '&message_id='.intval($message);
		} else if ($type == 'restrictChatMember') {
		    $url .= '&user_id='.intval($message);
		} else {
		    return false;
        }
		if ($disableNotification) {
			$url .= '&disable_notification=1';
		}
		if ($keyboard) {
			$url .= '&reply_markup='.json_encode($keyboard);
		}
		if ($additionalParams) {
			foreach ($additionalParams as $k => $v) {
				$url .= '&'.$k.'='.urlencode($v);
			}
		}
		
		if (!$this->beforeSendMessageRaw($url)) {
			return false;
		}
		
		$ch = curl_init();
		$optArray = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch, $optArray);
		$result = curl_exec($ch);
		$status = json_decode($result);
		curl_close($ch);
		
		sleep(1);
		
		if ((!$status || !isset($status->ok) || !$status->ok) && in_array($type, ['sendMessage', 'editMessageText']) && $markDown && !$this->sendMessage($chatId, $message, $keyboard, false, $disableNotification)) {
			return false;
		} else if (!$status || !isset($status->ok) || !$status->ok) {
		    //print_r($status);
        }
		if ($status->ok) {
		    return is_object($status->result) ? $status->result->message_id : true;
        } else {
			if (isset($status->description) && $type != 'sendMessage') {
				$errorMessage = '_'.$status->description.'_'."\n";
				$errorMessage .= '*Chat*: '.$chatId."\n";
				$errorMessage .= '*Type*: '.$type."\n";
				$errorMessage .= '*Message*: '.$message."\n";
				$errorMessage .= $url;
				$this->sendMessageToAdmin($errorMessage);
			}
		    return false;
        }
	}
	
	public function sendChatAction($chatId, $messageId)
    {
		return $this->sendMessageRaw('sendChatAction', $chatId, $messageId);
    }
	
	public function restrictChatMember($chatId, $userId, $untilDate, $canSendMessages = true, $canSendMedia = true, $canSendOther = true, $canAddWebPagePreview = true)
	{
		return $this->sendMessageRaw('restrictChatMember', $chatId, $userId, null, false, false, [
			'until_date' => $untilDate,
			'can_send_messages' => $canSendMessages ? 'True' : 'False',
			//'can_send_media_messages' => !$canSendMedia ? 'True' : 'False',
			//'can_send_other_messages' => !$canSendOther ? 'True' : 'False',
			//'can_add_web_page_previews' => !$canAddWebPagePreview ? 'True' : 'False',
		]);
	}
	
	
	
		
	public function deleteMessage($chatId, $messageId)
    {
		return $this->sendMessageRaw('deleteMessage', $chatId, $messageId);
    }
	
	
	
	protected function beforeSendMessageRaw($url)
	{
		return true;
	}
}

class TelegramSendMessageException extends Exception {}