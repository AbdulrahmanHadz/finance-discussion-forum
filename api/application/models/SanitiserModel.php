<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SanitiserModel extends CI_Model
{
	public function sanitiseString(?string $stringToSanitise): ?string
	{
		if (!empty($stringToSanitise)) {
			$stringSanitised = trim($stringToSanitise);
			return $this->_checkStringLength($stringSanitised);
		}
		return null;
	}

	protected function _checkStringLength(string $stringToCheck): ?string
	{
		if (empty($stringToCheck) || strlen($stringToCheck) == 0) {
			return null;
		}
		return $stringToCheck;
	}

	public function walkStringArray($arrayToWalk): array
	{
		return array_map('self::sanitiseString', $arrayToWalk);
	}

	public function walkIntArray($arrayToWalk): array
	{
		if (empty($arrayToWalk)) {
			return [];
		}

		$returnArray = array();
		foreach ($arrayToWalk as $key => $value) {
			$sanitisedInt = $this->sanitiseInt($value);
			if ($sanitisedInt != null) {
				$returnArray[] = $sanitisedInt;
			}
		}
		return $returnArray;
	}

	public function sanitiseInt($intToSanitise): ?int
	{
		if (!isset($intToSanitise) || !is_numeric($intToSanitise)) {
			return null;
		}
		$intSanitised = trim($intToSanitise);
		return (int)$intSanitised;
	}

	public function convertStrToInt($string)
	{
		try {
			$int = (int)$string;
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	public function sanitiseInputId($id)
	{
		if (is_numeric($id)) {
			$sanitisedId = $this->sanitiseInt($id);
			if ($sanitisedId <= 0) {
				throw new UnknownInputException("Bad id.", 400);
			}
			return $sanitisedId;
		} else if ($id == null) {
			return null;
		}
		throw new UnknownInputException("Unknown input `{$id}` for 'id'", 400);
	}


}
