<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class StringEnumeratedDatum extends TextDatum implements EnumeratedDatumInterface{

	use EnumeratedDatumTrait;

	public function validate($value): int{
		$f = __METHOD__;
		$print = false;
		$status = $this->validateEnumeration($value);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} validate enumeration returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print) {
			Debug::print("{$f} returning parent function");
		}
		return parent::validate($value);
	}
}
