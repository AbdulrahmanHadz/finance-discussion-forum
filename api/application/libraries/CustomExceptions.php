<?php
defined('BASEPATH') or exit('No direct script access allowed');


// Custom exception for catching custom errors
class CustomExceptions extends Exception
{

	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	// custom string representation of object
	#[ReturnTypeWillChange] public function __toString()
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n"; //edit this to your need
	}

}
