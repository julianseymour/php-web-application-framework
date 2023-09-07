<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\SecretKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

/**
 * Encrypt a datum with a symmetric key that is itself encrypted with another scheme
 *
 * @author j
 *        
 */
abstract class IntermediateSymmetricEncryptionScheme extends SymmetricEncryptionScheme{

	public abstract static function getIntermediateEncryptionSchemeClass();

	public function generateComponents(?DataStructure $ds = null): array{
		$f = __METHOD__;
		$print = false;
		$components1 = parent::generateComponents();
		$datum = $this->getColumn();
		$vn = $datum->getName();
		$keyname = "{$vn}AesKey";
		$aes_key = new SecretKeyDatum($keyname);
		$intermediate_class = static::getIntermediateEncryptionSchemeClass();
		$aes_key->setEncryptionScheme($intermediate_class);
		$aes_key->setPersistenceMode(PERSISTENCE_MODE_ENCRYPTED);
		if (! is_a($intermediate_class, AsymmetricEncryptionScheme::class, true)) {
			if ($print) {
				Debug::print("{$f} encryption scheme {$intermediate_class} is not asymmetric");
			}
			$aes_key->setTranscryptionKeyName($datum->getTranscryptionKeyName());
			$datum->setTranscryptionKeyName(null);
		} elseif ($print) {
			Debug::print("{$f} encryption scheme {$intermediate_class} is asymmetric");
		}
		$datum->setTranscryptionKeyName($keyname);
		$components1[$keyname] = $aes_key;
		$intermediate = new $intermediate_class($aes_key);
		$components2 = $intermediate->generateComponents();
		$components_merged = array_merge($components1, $components2);
		return $components_merged;
	}

	public function extractTranscryptionKey(Datum $datum):?string{
		$f = __METHOD__;
		$print = false;
		$vn = $datum->getName() . "AesKey";
		$ds = $datum->getDataStructure();
		if ($print) {
			$dsc = $ds->getClass();
			Debug::print("{$f} about to call {$dsc}->getColumnValue({$vn})");
		}
		return $ds->getColumnValue($vn);
	}

	public function generateEncryptionKey(Datum $datum):string{
		$vn = $datum->getName();
		$ds = $datum->getDataStructure();
		$index = "{$vn}AesKey";
		if ($ds->hasColumnValue($index)) {
			return $ds->getColumnValue($index);
		}
		return $ds->setColumnValue($index, random_bytes(32));
	}
}
