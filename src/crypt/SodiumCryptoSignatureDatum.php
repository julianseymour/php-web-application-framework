<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use Exception;

class SodiumCryptoSignatureDatum extends Base64Datum{

	protected $signColumnNames;

	public function getRequiredLength():int{
		return SODIUM_CRYPTO_SIGN_BYTES;
	}

	public final function hasRequiredLength():bool{
		return true;
	}

	public function hasSignColumnNames(): bool{
		return isset($this->signColumnNames) && is_array($this->signColumnNames) && ! empty($this->signColumnNames);
	}

	public function setSignColumnNames(?array $scn): ?array{
		$f = __METHOD__;
		if($scn === null) {
			unset($this->signColumnNames);
			return null;
		}elseif(!is_array($scn)) {
			Debug::error("{$f} sign column names must be an array");
		}
		return $this->signColumnNames = $scn;
	}

	public function getSignColumnNames(): ?array{
		$f = __METHOD__;
		if(!$this->hasSignColumnNames()) {
			Debug::error("{$f} sign column names are undefined");
		}
		return $this->signColumnNames;
	}

	public function generate(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasGenerationClosure() || ! $this->hasSignColumnNames()) {
				if($print) {
					if($this->hasGenerationClosure()) {
						Debug::print("{$f} generation closure is defined; returning parent function");
					}elseif(!$this->hasSignColumnNames()) {
						Debug::print("{$f} sign column names are undefined");
					}elseif(!$this->isNullable()) {
						Debug::warning("{$f} this column is not nullable, be careful");
					}
				}
				return parent::generate();
			}
			$scn = $this->getSignColumnNames();
			$ds = $this->getDataStructure();
			$temp = [];
			foreach($scn as $name) {
				if(!$ds->hasColumn($name)) {
					if($print) {
						Debug::print("{$f} data structure lacks a column named \"{$name}\"");
					}
					continue;
				}
				$column = $ds->getColumn($name);
				if($column instanceof VirtualDatum || $column->getPersistenceMode() === PERSISTENCE_MODE_VOLATILE) {
					if($print) {
						Debug::print("{$f} column \"{$name}\" is virtual or volatile");
					}
					continue;
				}
				$temp[$name] = true;
			}
			unset($scn);
			if(empty($temp)) {
				Debug::warning("{$f} no columns to sign");
				return parent::generate();
			}
			$this->setValue(user()->signMessage(json_encode($ds->toArray($temp))));
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
	
	public function dispose(): void{
		parent::dispose();
		unset($this->signColumnNames);
	}
}
