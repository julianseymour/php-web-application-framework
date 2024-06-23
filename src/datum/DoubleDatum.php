<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class DoubleDatum extends FloatingPointDatum{

	public function getColumnTypeString(): string{
		return "double";
	}

	public function cast($v){
		$f = __METHOD__;
		$cn = $this->getName();
		$print = false;
		if(is_string($v) && $this->hasApoptoticSignal() && $this->getApoptoticSignal() === $v){
			if($print){
				Debug::print("{$f} received the apoptotic signal");
			}
			return $v;
		}elseif($print){
			if(!is_string($v)){
				$gottype = gettype($v);
				Debug::print("{$f} \"{$v}\" is a {$gottype}, not a string");
			}elseif(!$this->hasApoptoticSignal()){
				Debug::print("{$f} column \"{$cn}\" does not have an apoptotic signal");
			}else{
				$apop = $this->getApoptoticSignal();
				if($apop !== $v){
					Debug::print("{$f} apoptotic signal \"{$apop}\" does not match received value \"{$v}\"");
				}else{
					Debug::error("{$f} this should be impossible");
				}
			}
		}
		return doubleval($v);
	}

	public static function validateStatic($value): int{
		return is_float($value) || is_int($value) ? SUCCESS : FAILURE;
	}

	public function parseValueFromQueryResult($v){
		$f = __METHOD__;
		try{
			if($v === null){
				return $this->isNullable() ? null : 0;
			}
			return ! is_double($v) ? doubleval($v) : $v;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function parseString(string $v){
		return doubleval($v);
	}
}
