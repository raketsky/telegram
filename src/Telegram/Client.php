<?php
namespace Raketsky\Telegram;

use Raketsky\Component\TelegramSendMessage;

class Client
{
	private $apiUrl = 'https://api.telegram.org/bot';
	
	use TelegramSendMessage;
	
	public function __construct($token, $adminChatId = null)
	{
		$this->setTelegramSendMessageToken($token);
		$this->setTelegramSendMessageAdmin($adminChatId);
	}
	
	/**
	 * @todo implement multiple admins + on send to admin send to all
	 * 
	 * @param $chatId
	 * @return bool
	 */
	public function isAdmin($chatId)
	{
//		$chatId = isset($this->users[$chatId]) ? $this->users[$chatId] : $chatId;
//		return in_array($chatId, $this->admins);
		
		return $this->adminChatId == $chatId;
	}
	
	/**
	 * @param int $count
	 * @return array
	 */
	public function getUpdates($count=3)
	{
		$data = $this->getUpdatesRaw();
		$data = array_reverse($data);
		
		$items = [];
		foreach ($data as $i => $r) {
			if ($i >= $count) {
				break;
			}
			$items[] = [
				'id' => $r->message->message_id,
				'name' => $r->message->from->first_name,
				'username' => $r->message->from->username,
				'text' => isset($r->message->text) ? $r->message->text : null,
				'chat_id' => $r->message->chat->id,
				'created_at' => date('Y-m-d H:i:s', $r->message->date),
			];
		}
		$items = array_reverse($items);
		
		return $items;
	}
	
	public function getUpdatesRaw()
	{
		$url = $this->apiUrl.$this->token.'/getUpdates';
		$data = file_get_contents($url);
		$data = json_decode($data);
		
		return $data->result;
	}
}
