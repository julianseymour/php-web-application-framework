<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

/**
 * Datum that represents a C-style unsigned integer enumeration
 * i.e.
 * not the way they are done in MYSQL, which has string enums.
 * If you want those see StringEnumeratedDatum
 *
 * @author j
 */
class IntegerEnumeratedDatum extends UnsignedIntegerDatum implements EnumeratedDatumInterface{

	use EnumeratedDatumTrait;

	public function __construct($name, $bit_count){
		parent::__construct($name, $bit_count);
	}

	public function validate($value): int{
		$f = __METHOD__;
		$status = $this->validateEnumeration($value);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} validate enumeration returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return parent::validate($value);
	}

	/**
	 * IntegerEnumeratedDatums are set to 0 instead of getting erased
	 *
	 * {@inheritdoc}
	 * @see Datum::unset()
	 */
	public function unsetValue(bool $force = false): int{
		if($force){
			return parent::unset($force);
		}
		$column_name = $this->getName();
		if($this->value !== 0){
			$this->setValue(0);
			$this->setUpdateFlag(true);
			$storage = $this->getPersistenceMode();
			switch($storage){
				case PERSISTENCE_MODE_COOKIE:
					unset($_COOKIE[$column_name]);
					break;
				case PERSISTENCE_MODE_SESSION:
					unset($_SESSION[$column_name]);
					break;
				default:
			}
			return SUCCESS;
		}
		return STATUS_UNCHANGED;
	}
}
