<?php
defined('BASEPATH') or exit('No direct script access allowed');

// When a database operation goes wrong
class DatabaseOperationException extends CustomExceptions
{
	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
