<?php
defined('BASEPATH') or exit('No direct script access allowed');

// When the input is not in the correct format
class UnknownInputException extends CustomExceptions
{
	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
