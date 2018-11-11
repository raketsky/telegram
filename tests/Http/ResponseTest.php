<?php
namespace Test\Http;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Raketsky\Telegram\Http\Model\RawDataHandlerTrait;
use Raketsky\Telegram\Http\Response;

/**
 * vendor/bin/phpunit tests/Http/ResponseTest.php
 */
class ResponseTest extends TestCase
{
    public function testResponse()
    {
    	$message = $this->generateChatMessage();
        $rawMessage = '{"update_id":'.$message['update_id'].',"message":{"message_id":'.$message['message']['message_id'].',"from":{"id":'.$message['message']['from']['id'].',"is_bot":false,"first_name":"'.$message['message']['from']['first_name'].'","last_name":"'.$message['message']['from']['last_name'].'","language_code":"'.$message['message']['from']['language_code'].'"},"chat":{"id":'.$message['message']['chat']['id'].',"title":"'.$message['message']['chat']['title'].'","type":"'.$message['message']['chat']['type'].'"},"date":'.$message['message']['date'].',"text":"'.$message['message']['text'].'"}}';
        $this->assertEquals($rawMessage, json_encode($message));
        
        $response = new Response($rawMessage);
        $this->assertEquals($message['message']['from']['first_name'], $response->getUserDisplayName());
        $this->assertEquals($message['message']['text'], $response->text);
    }
    
    public function testUpdate()
    {
    	$message = $this->generateChatMessage();
        $rawMessage = '{"update_id":'.$message['update_id'].',"message":{"message_id":'.$message['message']['message_id'].',"from":{"id":'.$message['message']['from']['id'].',"is_bot":false,"first_name":"'.$message['message']['from']['first_name'].'","last_name":"'.$message['message']['from']['last_name'].'","language_code":"'.$message['message']['from']['language_code'].'"},"chat":{"id":'.$message['message']['chat']['id'].',"title":"'.$message['message']['chat']['title'].'","type":"'.$message['message']['chat']['type'].'"},"date":'.$message['message']['date'].',"text":"'.$message['message']['text'].'"}}';
        $this->assertEquals($rawMessage, json_encode($message));
        
        $response = new Response($rawMessage);
        $this->assertEquals($message['message']['from']['first_name'], $response->getUserDisplayName());
        $this->assertEquals($message['message']['text'], $response->text);
    }
    
    private $messageId = 1;
    private function generateChatMessage($fromUserId = false, $type = 'supergroup')
	{
		$message = [
			'update_id' => rand(100000000, 999999999),
			'message' => [
				'message_id' => $this->messageId,
				'from' => [
					'id' => $fromUserId ? $fromUserId : rand(100000000, 999999999),
					'is_bot' => false,
					'first_name' => 'Test',
					'last_name' => 'Pilot',
					'language_code' => 'en-EN',
				],
				'chat' => [
					'id' => intval('-100'.rand(1000000000, 9999999999)),
					'title' => 'Team',
					'type' => $type,
				],
				'date' => time(),
				'text' => 'test',
			],
		];
		$this->messageId++;
		
		return $message;
	}
	
	
	
	
	
	
	
	
	public function setUp()
    {
        $this->configureDatabase();
        $this->migrateIdentitiesTable();
    }
    protected function configureDatabase()
    {
        $db = new \Illuminate\Database\Capsule\Manager;
        $db->addConnection(array(
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ));
        $db->bootEloquent();
        $db->setAsGlobal();
    }

    public function migrateIdentitiesTable()
    {
    	//Schema::table('telegram_updates', function(Blueprint $table) {
//        DB::schema()->create('telegram_updates', function(Blueprint $table) {
//            $table->increments('id');
//            $table->integer('update_id');
//            $table->integer('chat_id')->nullable()->default(null);
//            $table->integer('author_id')->nullable()->default(null);
//            $table->string('author_signature', 64)->nullable()->default(null);
//            $table->text('raw');
//            $table->dateTime('created_at')->nullable()->default(null);
//            $table->dateTime('updated_at')->nullable()->default(null);
//        });
//        Update::create(array('page_id' => 1));
//        Update::create(array('page_id' => 2));

    }

    public function testUpdateFill()
    {
    	$message = $this->generateChatMessage();
    	
//        $extractor = new ClassToTest();
//        $result = $extractor->someFunctionThatUsesAnEloquentModel(1);
//        $expected = array(
//            'id' => '1',
//            'page_id' => '1'
//        );
//        $this->assertEquals($expected['page_id'], $result['page_id']);
		
		$update = new Update();
		$update->raw = $message;
		
		$this->assertEquals($message['update_id'], $update->update_id);
		$this->assertEquals($message['message']['chat']['id'], $update->chat_id);
		$this->assertEquals($message['message']['from']['id'], $update->author_id);
		$this->assertEquals($message['message']['from']['first_name'], $update->author_signature);
    }
}


/**
 * @property int id
 * @property int update_id
 * @property int chat_id
 * @property int author_id
 * @property string author_signature
 * @property string raw
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Update extends Model
{
	use RawDataHandlerTrait;
	
	public $table = 'telegram_updates';
	public $fillable = ['update_id', 'chat_id'];
	
//	protected $casts = [
//		'raw' => 'array',
//	];
}
