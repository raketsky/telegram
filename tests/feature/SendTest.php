<?php
namespace Test\Unit;

use PHPUnit\Framework\TestCase;
use Raketsky\Component\TelegramSendMessage;
use Raketsky\Telegram\Client;

/**
 * 
 * vendor/bin/phpunit tests/feature/SendTest.php
 */
class SendTest extends TestCase
{
	use TelegramSendMessage;
	
	public function testSendMessage()
	{
		$token = getenv('TELEGRAM_TEST_BOT_TOKEN');
		$adminChatId = getenv('TELEGRAM_TEST_ADMIN_ID');
		$client = new Client($token, $adminChatId);
		
		$testChatId = getenv('TELEGRAM_TEST_CHAT_ID');
		
		$id = $client->sendMessage($testChatId, 'Hello!');
		$this->assertTrue(is_numeric($id), 'Send message response ID is not numeric');
		$this->assertTrue($id > 1, 'Response ID is not a number?');
		
		$deleteStatus = $client->deleteMessage($testChatId, $id);
		$this->assertTrue($deleteStatus === true);
	}
}
