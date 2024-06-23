<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\set_secure_cookie;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\CheckedInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\MultipleRadioButtons;
use Exception;

class BooleanDatum extends UnsignedIntegerDatum implements StaticElementClassInterface{

	public function __construct($name){
		parent::__construct($name, 1);
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return CheckboxInput::class;
	}

	public function cast($v){
		$f = __METHOD__;
		try{
			$print = false;
			if($v === null){
				if($this->isNullable()){
					if($print){
						Debug::print("{$f} column is nullable; returning null");
					}
					return null;
				}elseif($print){
					Debug::print("{$f} column is not nullable; returning false");
				}
				return false;
			}elseif(is_bool($v)){
				if($print){
					Debug::print("{$f} value is boolean");
				}
				return $v;
			}elseif(is_int($v)){
				if($print){
					Debug::print("{$f} value is integer \"{$v}\"");
				}
				return $this->parseValueFromQueryResult($v);
			}elseif(is_string($v)){
				if($print){
					Debug::print("{$f} value is the string \"{$v}\"");
				}
				return $this->parseValueFromSuperglobalArray($v);
			}
			Debug::error("{$f} value is not boolean or integer");
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function validateStatic($v): int{
		return is_bool($v) ? SUCCESS : FAILURE;
	}

	public function getDefaultValueString(){
		return $this->getDefaultValue() ? 1 : 0;
	}

	public static function getDatabaseEncodedValueStatic($value){
		$f = __METHOD__;
		if($value === null){
			return 0;
		}elseif(is_bool($value)){
			if($value){
				return 1;
			}else{
				return 0;
			}
		}elseif(is_int($value)){
			switch($value){
				case 0:
				case 1:
					return $value;
				default:
			}
		}elseif(is_string($value)){
			switch($value){
				case "0":
				case "false":
					return 0;
				case "1":
				case "true":
					return 1;
				default:
			}
		}
		Debug::error("{$f} invalid value \"{$value}\"");
	}

	public function parseValueFromQueryResult($value){
		$f = __METHOD__;
		try{
			$print = false;
			if($value === null){
				if($print){
					Debug::print("{$f} value is null");
				}
				if($this->isNullable()){
					return $value;
				}
				return false;
			}elseif(is_bool($value)){
				if($print){
					Debug::print("{$f} value is boolean");
				}
				return $value;
			}elseif(is_string($value)){
				switch($value){
					case "0":
					case "1":
						$value = intval($value);
					default:
				}
			}elseif($print){
				$gottype = gettype($value);
				Debug::print("{$f} value is a {$gottype}");
			}
			if(is_int($value)){
				if($print){
					Debug::print("{$f} value is an integer");
				}
				switch($value){
					case 0:
						if($print){
							Debug::print("{$f} returning false");
						}
						return false;
					case 1:
						if($print){
							Debug::print("{$f} returning true");
						}
						return true;
					default:
				}
			}
			Debug::error("{$f} invalid raw value \"{$value}\"");
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function parseValueFromSuperglobalArray($value){
		$f = __METHOD__; 
		try{
			$print = false;
			if(is_bool($value)){
				if($print){
					Debug::print("{$f} value is boolean");
				}
				return $value;
			}elseif(is_int($value)){
				if($print){
					Debug::print("{$f} value is the integer \"{$value}\"");
				}
				switch($value){
					case 0:
						return false;
					case 1:
						return true;
					default:
						return $value;
				}
			}elseif(is_string($value)){
				if($print){
					Debug::print("{$f} value is the string \"{$value}\"");
				}
				switch($value){
					case "0":
					case "off":
						return false;
					case "1":
					case "on":
						return true;
					default:
						return $value;
				}
			}else{
				Debug::error("{$f} value is not boolean, integer or string");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setDefaultValue($v){
		$f = __METHOD__;
		if($v !== null && ! is_bool($v)){
			Debug::error("{$f} default value must be boolean or null");
		}
		return parent::setDefaultValue($v);
	}

	public static function parseString(string $string){
		return boolval($string);
	}

	/**
	 * BooleanDatums are set to false instead of getting erased
	 *
	 * {@inheritdoc}
	 * @see Datum::unsetValue()
	 */
	public function unsetValue(bool $force = false): int{
		$f = __METHOD__;
		$print = false;
		$column_name = $this->getName();
		if($force){
			if($print){
				Debug::print("{$f} forcefully annihiliating this boolean value");
			}
			return parent::unsetValue($force);
		} else if($this->getValue()){
			if($print){
				Debug::print("{$f} column value is true; setting it to false");
			}
			$this->setValue(false);
			$storage = $this->getPersistenceMode();
			switch($storage){
				case PERSISTENCE_MODE_COOKIE:
					unset($_COOKIE[$column_name]);
					if(headers_sent()){
						Debug::warning("{$f} headers already sent somehow");
					}else{
						set_secure_cookie($column_name, false);
					}
					break;
				case PERSISTENCE_MODE_SESSION:
					unset($_SESSION[$column_name]);
					break;
				case PERSISTENCE_MODE_ENCRYPTED:
					$this->getCipherColumn()->unsetValue($force);
				default:
			}
			$this->setUpdateFlag(true);
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} column \"{$column_name}\" is already false");
		}
		return STATUS_UNCHANGED;
	}

	public function processInput($input){
		$f = __METHOD__;
		$print = false;
		if($print){
			if($input->hasValueAttribute()){
				$value = $input->getValueAttribute();
				Debug::print("{$f} input has value \"{$value}\"");
				if($value == "default"){
					Debug::printStackTraceNoExit();
				}
			}else{
				Debug::print("{$f} input has no value attribute");
			}
		}
		if($input instanceof CheckedInput){
			if($input->hasCheckedAttribute()){
				if($print){
					Debug::print("{$f} setting value to true");
				}
				$this->setValue(true);
			}else{
				if($print){
					Debug::print("{$f} setting value to false");
				}
				$this->setValue(false);
			}
			return SUCCESS;
		}elseif($input instanceof MultipleRadioButtons){
			if($print){
				Debug::print("{$f} input is a MultipleRadioButtons; about to return results of parent function");
			}
		}elseif($print){
			Debug::print("{$f} neither of the above");
		}
		return parent::processInput($input);
	}
}
