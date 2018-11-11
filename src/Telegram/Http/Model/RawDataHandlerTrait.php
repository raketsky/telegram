<?php
namespace Raketsky\Telegram\Http\Model;

trait RawDataHandlerTrait
{
    public function getRawAttribute($value)
    {
        return json_decode($value, true);
    }
    
    public function setRawAttribute($value)
    {
        $this->attributes['raw'] = json_encode($value);
        if (isset($value['update_id'])) {
        	$this->update_id = $value['update_id'];
		}
        if (isset($value['message'])) {
        	if (isset($value['message']['chat']) && isset($value['message']['chat']['id'])) {
        		$this->chat_id = $value['message']['chat']['id'];
			}
        	if (isset($value['message']['from'])) {
        		if (isset($value['message']['from']['first_name'])) {
        			$this->author_signature = $value['message']['from']['first_name'];
				}
        		if (isset($value['message']['from']['id'])) {
        			$this->author_id = $value['message']['from']['id'];
				}
			}
		}
    }
}
