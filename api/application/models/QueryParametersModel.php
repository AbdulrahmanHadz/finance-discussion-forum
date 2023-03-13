<?php
defined('BASEPATH') or exit('No direct script access allowed');


use Enums\OrderSort;


class QueryParametersModel extends CI_Model
{
	private string $orderType = 'createdAt';
	private string $orderSort = 'DESC';

	public function __construct()
	{
		$this->load->model('SanitiserModel');
	}

	public function checkQueryParams(array $params,
		$acceptedParams,
		$acceptedOrderValues = null)
	{
		$formattedParams = [];

		foreach ($acceptedParams::params as $paramName => $paramDefaultValue) {
			if (in_array($paramName, $acceptedParams::default) ||
				array_key_exists($paramName, $params)) {
				$userParamValue = $params[$paramName] ?? $paramDefaultValue;
				$formattedValue = $userParamValue;
				$paramType = gettype($paramDefaultValue);

				switch ($paramType) {
					case "boolean":
						$formattedValue =
							filter_var(($userParamValue), FILTER_VALIDATE_BOOL);
						break;
					case "integer":
						$formattedValue =
							$this->checkIntParam($userParamValue ?? null, $paramDefaultValue);
						break;
					default:
						break;
				}

				if (str_contains(strtolower($paramName), 'ids')) {
					$idsArray = $this->SanitiserModel->walkIntArray($userParamValue);
					if ($idsArray == null) {
						$formattedValue = null;
					} else {
						$formattedValue = $idsArray;
					}

				} else if (str_contains(strtolower($paramName), 'order')
					&& $acceptedOrderValues != null) {
					$formattedValue =
						$this->checkOrderInput(
							$userParamValue,
							$acceptedOrderValues);
				}

				$formattedParams[$paramName] = $formattedValue;
			}

//			if (!$this->getExnumValue($key, FetchParams::class)) {
//				throw new UnknownInputException("Unknown parameter '{$key}'", 400);
//			}
		}


		return $formattedParams;
	}

	public function checkIntParam($parameter, $default)
	{
		if (isset($parameter) && is_numeric($parameter)) {
			return (int)$parameter;
		}
		return (int)$default;
	}

	public function checkOrderInput(?array $orderObj,
		$enumClassToUse)
	{
		$orderType = array_key_first($orderObj);
		$orderSort = $orderObj[$orderType];

		if (!$this->getEnumValue($orderType, $enumClassToUse)) {
			throw new UnknownInputException("Unknown order type '{$orderType}'", 400);
		}

		if (!$this->getEnumValue($orderSort, OrderSort::class)) {
			throw new UnknownInputException("Unknown enum type '{$orderSort}'", 400);
		}

		return ['orderType' => $orderType, 'orderSort' => $orderSort];
	}

	protected function getEnumValue($value, $enumClass)
	{
		$cases = $enumClass::cases();
		return in_array($value, array_column($cases, "name"));
	}

	public function segmentId($segments_array, $acceptedPaths = null)
	{
		if (isset($acceptedPaths)) {
			return $this->acceptedPathsRegex($segments_array, $acceptedPaths);
		} else {
			return $this->segementsId($segments_array);
		}
	}

	protected function acceptedPathsRegex($segments_array, $acceptedPaths)
	{
		array_splice($segments_array, 0, 1);
		$normalisedSegment = "/" . implode('/', $segments_array);
		$closestMatch = null;

		foreach ($acceptedPaths as $pathIndex => $pathRegex) {
			$normalisedRegex = '/\\' . $pathRegex . '/i';
			$match = preg_match($normalisedRegex, $normalisedSegment, $matches);

			if ($match == 0 || !$match) {
				continue;
			}

			$closestMatch = $matches;
		}

		if (isset($closestMatch)) {
			array_splice($closestMatch, 0, 1);
			$array_to_return = [];
			foreach ($closestMatch as $key => $value) {
				if (is_numeric($value)) {
					$array_to_return['id'] = $value;
				} else {
					$array_to_return['method'] = $value;
				}
			}
			return $array_to_return;
		}
		return null;
	}

	protected function segementsId($segments_array)
	{
		$previous = null;
		foreach ($segments_array as $key => $segment) {
			if ($previous != null && strtolower($previous) == strtolower(get_class($this))) {
				if (is_numeric($segment)) {
					return (int)$segment;
				}
			}
			$previous = $segment;
		}

		if ($previous == 'index') {
			return null;
		}
		return $previous;
	}
}

