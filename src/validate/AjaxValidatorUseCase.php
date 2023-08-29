<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class AjaxValidatorUseCase extends UseCase{

	use ValidatorTrait;

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/validate";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = false;
			if (request()->getRequestURISegmentCount() < 2) {
				Debug::error("{$f} request URI segment count < 2");
			}
			$segments = request()->getRequestURISegments();
			$className = $segments[1];
			if ($print) {
				Debug::print("{$f} validator className is \"{$className}\"");
			}
			$validatorClass = mods()->getValidatorClass($className);
			if ($validatorClass == null) {
				Debug::error("{$f} null validator class name");
			} elseif (! class_exists($validatorClass)) {
				Debug::error("{$f} validator class \"{$validatorClass}\" does not exist");
			} elseif (! is_a($validatorClass, AjaxValidatorInterface::class, true)) {
				Debug::error("{$f} class \"{$validatorClass}\" is not an ajax validator");
			} elseif ($print) {
				Debug::print("{$f} validator class name is \"{$validatorClass}\"");
			}
			$validator = new $validatorClass();
			$params = getInputParameters();
			$name = $params['name'];
			if (is_array($name)) {
				Debug::error("{$f} disabled");
				Debug::printArray($name);
				$repack_us = [];
				$temp = &$name;
				while (true) {
					if (count($name) > 1) {
						Debug::error("{$f} this validator only works for one input at a time");
					}
					$key = array_keys($temp)[0];
					array_push($repack_us, $key);
					if (is_array($temp[$key])) {
						$temp = &$temp[$key];
					} else {
						array_push($repack_us, $temp[$key]);
						break;
					}
				}
				array_push($repack_us, $params['value']);
				if ($print) {
					Debug::print("{$f} flattened array name attribute to the following:");
					Debug::printArray($repack_us);
				}
				$repacked = $repack_us[count($repack_us) - 1];
				for ($i = count($repack_us) - 2; $i >= 0; $i --) {
					$repacked = [
						$repack_us[$i] => $repacked
					];
				}
				if ($print) {
					Debug::print("{$f} repacked array name attribute to the following:");
					Debug::printArray($repacked);
				}
			} else {
				$repacked = [
					$name => $params['value']
				];
			}
			$status = $validator->validate($repacked);
			$this->setValidator($validator);
			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getResponder(int $status):?Responder{
		return new AjaxValidatorResponder();
	}
}
