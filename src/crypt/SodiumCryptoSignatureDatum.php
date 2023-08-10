<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use Exception;

class SodiumCryptoSignatureDatum extends Base64Datum
{

	protected $signColumnNames;

	public static function getColumnNameStatic()
	{
		return "signature";
	}

	public function __construct($name = null)
	{
		if (empty($name)) {
			$name = static::getColumnNameStatic();
		}
		parent::__construct($name);
	}

	public function getRequiredLength()
	{
		return SODIUM_CRYPTO_SIGN_BYTES;
	}

	public final function hasRequiredLength()
	{
		return true;
	}

	public function hasSignColumnNames(): bool
	{
		return isset($this->signColumnNames) && is_array($this->signColumnNames) && ! empty($this->signColumnNames);
	}

	public function setSignColumnNames(?array $scn): ?array
	{
		$f = __METHOD__; //SodiumCryptoSignatureDatum::getShortClass()."(".static::getShortClass().")->setSignColumnNames()";
		if ($scn === null) {
			unset($this->signColumnNames);
			return null;
		} elseif (! is_array($scn)) {
			Debug::error("{$f} sign column names must be an array");
		}
		return $this->signColumnNames = $scn;
	}

	public function getSignColumnNames(): ?array
	{
		$f = __METHOD__; //SodiumCryptoSignatureDatum::getShortClass()."(".static::getShortClass().")->getSignColumnNames()";
		if (! $this->hasSignColumnNames()) {
			Debug::error("{$f} sign column names are undefined");
		}
		return $this->signColumnNames;
	}

	public function generate(): int
	{
		$f = __METHOD__; //SodiumCryptoSignatureDatum::getShortClass()."(".static::getShortClass().")->generate()";
		try {
			$print = false;
			if ($this->hasGenerationClosure() || ! $this->hasSignColumnNames()) {
				if ($print) {
					if ($this->hasGenerationClosure()) {
						Debug::print("{$f} generation closure is defined; returning parent function");
					} elseif (! $this->hasSignColumnNames()) {
						Debug::print("{$f} sign column names are undefined");
					} elseif (! $this->isNullable()) {
						Debug::warning("{$f} this column is not nullable, be careful");
					}
				}
				return parent::generate();
			}
			$scn = $this->getSignColumnNames();
			$ds = $this->getDataStructure();
			$temp = [];
			foreach ($scn as $name) {
				if (! $ds->hasColumn($name)) {
					if ($print) {
						Debug::print("{$f} data structure lacks a column named \"{$name}\"");
					}
					continue;
				}
				$column = $ds->getColumn($name);
				if ($column instanceof VirtualDatum || $column->getPersistenceMode() === PERSISTENCE_MODE_VOLATILE) {
					if ($print) {
						Debug::print("{$f} column \"{$name}\" is virtual or volatile");
					}
					continue;
				}
				$temp[$name] = true;
			}
			unset($scn);
			if (empty($temp)) {
				Debug::warning("{$f} no columns to sign");
				return parent::generate();
			}
			$this->setValue(user()->signMessage(json_encode($ds->toArray($temp))));
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/*
	 * public function validate($signature):int{
	 * $f = __METHOD__; //SodiumCryptoSignatureDatum::getShortClass()."(".static::getShortClass().")::validate()";
	 * try{
	 * if(empty($signature)){
	 * if(!$this->isNullable()){
	 * Debug::warning("{$f} signature is empty");
	 * return ERROR_NULL_SIGNATURE;
	 * }
	 * return SUCCESS;
	 * }
	 * $len = strlen(($signature));
	 * if($len !== SODIUM_CRYPTO_SIGN_BYTES){
	 * Debug::error("{$f} signature \"{$signature}\" is {$len} bytes, when it must be ".SODIUM_CRYPTO_SIGN_BYTES);
	 * }
	 * Debug::print("{$f} signature is the correct length");
	 * return parent::validate($signature);
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public function dispose(): void
	{
		parent::dispose();
		unset($this->signColumnNames);
	}
}
