<?php
namespace Raketsky\Telegram\Exception;

use Exception;
use Throwable;

class TelegramClientException extends Exception
{
    private $context;

	public function __construct(string $message, array $context = [], Throwable $previous = null)
    {
	    parent::__construct($message, 0, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
	    return $this->context;
    }
}
