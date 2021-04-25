<?php
namespace Raketsky\Component;

use Egosun\Util\StringUtil;
use Raketsky\Telegram\Exception\TelegramClientException;

trait TelegramSendMessage
{
	protected $token = null;
	protected $adminChatId = null;

	public function setTelegramSendMessageToken($token)
	{
		$this->token = $token;
	}

	public function setTelegramSendMessageAdmin($chatId)
	{
		$this->adminChatId = $chatId;
	}

    /**
     * @param            $chatId
     * @param            $message
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @param array      $additionalParams
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function editTextMessage($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('editMessageText', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}

    /**
     * @param            $chatId
     * @param            $message
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @param array      $additionalParams
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function editMessageCaption($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('editMessageCaption', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}

    /**
     * @param            $chatId
     * @param            $message
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @param array      $additionalParams
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function editMessageReplyMarkup($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('editMessageReplyMarkup', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}

    /**
     * @param            $message
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @return bool
     * @throws TelegramClientException
     */
	public function sendMessageToAdmin($message, array $keyboard = null, $markDown = true, $disableNotification = false)
	{
		if ($this->adminChatId) {
			$chatId = $this->adminChatId;
		} else if (defined('TELEGRAM_SEND_MESSAGE_ADMIN') && TELEGRAM_SEND_MESSAGE_ADMIN) {
			$chatId = TELEGRAM_SEND_MESSAGE_ADMIN;
		} else {
			throw new TelegramClientException('No admin token provided');
		}

		return $this->sendMessage($chatId, $message, $keyboard, $markDown, $disableNotification);
	}

    /**
     * @param            $chatId
     * @param            $message
     * @param array|null $keyboard
     * @param bool|true  $markDown
     * @param bool|false $disableNotification Sends the message silently. Users will receive a notification with no sound.
     * @param array|null $additionalParams
     * @return bool
     * @throws TelegramClientException
     */
	public function sendMessage($chatId, $message, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('sendMessage', $chatId, $message, $keyboard, $markDown, $disableNotification, $additionalParams);
	}

    /**
     * @param            $chatId
     * @param            $image
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @param array      $additionalParams
     *
     * @return bool|int
     * @throws TelegramClientException
     */
	public function sendImage($chatId, $image, array $keyboard = null, $markDown = true, $disableNotification = false, array $additionalParams = [])
	{
		return $this->sendMessageRaw('sendPhoto', $chatId, $image, $keyboard, $markDown, $disableNotification, $additionalParams);
	}

    /**
     * @param            $chatId
     * @param            $image
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @return mixed|null
     * @throws TelegramClientException
     */
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
     * @throws TelegramClientException
     */
	public function forwardMessage($chatId, $fromChatId, $messageId, $disableNotification = true)
	{
		return $this->sendMessageRaw('forwardMessage', $chatId, $messageId, null, false, $disableNotification, ['from_chat_id' => $fromChatId]);
	}

    /**
     * @param      $fromChatId
     * @param      $messageId
     * @param bool $disableNotification
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function forwardToAdmin($fromChatId, $messageId, $disableNotification = true)
	{
		if ($this->adminChatId) {
			$chatId = $this->adminChatId;
		} else if (defined('TELEGRAM_SEND_MESSAGE_ADMIN') && TELEGRAM_SEND_MESSAGE_ADMIN) {
			$chatId = TELEGRAM_SEND_MESSAGE_ADMIN;
		} else {
			throw new TelegramClientException('No admin token provided');
		}

		return $this->sendMessageRaw('forwardMessage', $chatId, $messageId, null, false, $disableNotification, ['from_chat_id' => $fromChatId]);
	}

    /**
     * @param      $chatId
     * @param      $text
     * @param null $callback
     * @param int  $waittime
     * @throws TelegramClientException
     */
	public function sendTmpMessage($chatId, $text, $callback = null, $waittime = 2)
	{
		$msgId = $this->sendMessage($chatId, $text);
		if ($callback) {
			$callback();
		}
		sleep($waittime);
		if ($msgId > 1) {
			$this->deleteMessage($chatId, $msgId);
		}
	}

    /**
     * @param            $type
     * @param            $chatId
     * @param            $message
     * @param array|null $keyboard
     * @param bool       $markDown
     * @param bool       $disableNotification
     * @param array      $additionalParams
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function sendMessageRaw($type, $chatId, $message, array $keyboard = null, $markDown = false, $disableNotification = false, array $additionalParams = [])
	{
		if ($this->token) {
			$token = $this->token;
		} else if (defined('TELEGRAM_SEND_MESSAGE_TOKEN') && TELEGRAM_SEND_MESSAGE_TOKEN) {
			$token = TELEGRAM_SEND_MESSAGE_TOKEN;
		} else {
			throw new TelegramClientException('No token provided');
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
		} else if ($type == 'sendAnimation') {
			$url .= '&animation='.$message;
		} else if ($type == 'sendChatAction') {
		    $url .= '&action='.$message;
		} else if (in_array($type, ['deleteMessage', 'forwardMessage'])) {
		    $url .= '&message_id='.intval($message);
		} else if ($type == 'restrictChatMember') {
		    $url .= '&user_id='.intval($message);
		} else {
			throw new TelegramClientException('Unknown sendMessageRaw type', ['type' => $type]);
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

		$this->response = null;

		$this->beforeSendMessageRaw($url);

		$ch = curl_init();
		$optArray = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch, $optArray);
		$this->response = curl_exec($ch);
		$status = json_decode($this->response, true);
		curl_close($ch);

		sleep(1);

		$isNotOk = !$status || !isset($status['ok']) || !$status['ok'];
		if ($isNotOk && $type == 'deleteMessage') {
			throw new TelegramClientException($status->description ?? 'Unable to delete message', [
			    'chatId' => $chatId,
			    'type' => $type,
			    'message' => $message,
                'response' => $status,
            ]);
		} elseif (!$isNotOk && $type == 'deleteMessage') {
			// stopping script, cuz everything done
            // todo refactor in general
		    return 0;
		}
		$isMessageMarkdown = in_array($type, ['sendMessage', 'editMessageText']) && isset($additionalParams['parse_mode']) && $additionalParams['parse_mode'] == 'markdown';
		if ($isNotOk && $isMessageMarkdown) {
			return $this->sendMessage($chatId, $message, $keyboard, false, $disableNotification);
		} else if ($isNotOk) {
			throw new TelegramClientException('Unable to send message', ['response' => $status]);
        }
		if ($status['ok']) {
			if (isset($status['result']) && isset($status['result']['message_id'])) {
				return $status['result']['message_id'];
			}

		    return isset($status['result']) ? $status['result'] : $status;
        } else {
			throw new TelegramClientException($status->description ?? 'Unable to delete message', [
			    'chatId' => $chatId,
			    'type' => $type,
			    'message' => $message,
                'response' => $status,
            ]);
        }
	}

    /**
     * @param $chatId
     * @param $messageId
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function sendChatAction($chatId, $messageId)
    {
		return $this->sendMessageRaw('sendChatAction', $chatId, $messageId);
    }

    /**
     * @param      $chatId
     * @param      $userId
     * @param      $untilDate
     * @param bool $canSendMessages
     * @param bool $canSendMedia
     * @param bool $canSendOther
     * @param bool $canAddWebPagePreview
     * @return mixed|null
     * @throws TelegramClientException
     */
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


	private $response = null;
	public function getMessageRawRespone()
	{
		return $this->response;
	}


    /**
     * @param $chatId
     * @param $messageId
     * @return mixed|null
     * @throws TelegramClientException
     */
	public function deleteMessage($chatId, $messageId)
    {
        $this->sendMessageRaw('deleteMessage', $chatId, $messageId);

		return true;
    }



	protected function beforeSendMessageRaw($url)
	{
		// if error occured than throw exception
        // throw new TelegramClientException('Before send failed');
	}
}
