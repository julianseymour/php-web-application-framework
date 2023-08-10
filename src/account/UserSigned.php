<?php
namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoSignatureDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use Exception;

abstract class UserSigned extends UserFingerprint
{

	public function isSignatureRequired()
	{
		return true;
	}

	public function getSignableMessage()
	{
		return json_encode($this->toArray("sign"));
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::reconfigureColumns($columns, $ds);
		$columns['signatoryName']->volatilize();
	}

	protected function afterGenerateInitialValuesHook(): int
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->afterGenerateKeyHook()";
		try {
			/*
			 * $print = false;
			 * if(!$this->hasColumn("signature")){
			 * Debug::error("{$f} signature datum does not exist");
			 * }elseif(!app()->hasUserData()){
			 * Debug::warning("{$f} current user data is undefined; let's see what happens anyways");
			 * }
			 */
			if (! $this->hasSignature()) {
				$this->generateSignature();
			}
			if (! $this->hasSignature()) {
				Debug::error("{$f} signature is still undefined");
			}
			return parent::afterGenerateInitialValuesHook();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasSignatoryData()
	{
		return $this->hasForeignDataStructure("signatoryKey");
	}

	public function setSignatoryData($signatory)
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->setSignatoryData()";
		if (! isset($signatory)) {
			Debug::error("{$f} received null parameter");
		}
		// $this->setSignatoryKey($signatory->getIdentifierValue());
		return $this->setForeignDataStructure("signatoryKey", $signatory);
	}

	public function getSignatoryData()
	{
		return $this->getForeignDataStructure("signatoryKey");
	}

	public function hasSignatoryKey()
	{
		return $this->hasColumnValue("signatoryKey");
	}

	public function getSignatoryKey()
	{
		return $this->getColumnValue("signatoryKey");
	}

	public function getSignatoryUsername()
	{
		return $this->getSignatoryData()->getName();
	}

	public function setSignatoryUserKey($key)
	{
		return $this->setColumnValue("signatoryKey", $key);
	}

	public function hasSignatoryAccountType()
	{
		return $this->hasColumnValue("signatoryAccountType");
	}

	public function getSignatoryAccountType()
	{
		return $this->getColumnValue("signatoryAccountType");
	}

	public function setSignatoryAccountType($value)
	{
		return $this->setColumnValue("signatoryAccountType", $value);
	}

	public static function skipRemoteBackup()
	{
		return false;
	}

	/*
	 * protected function afterInsertHook(mysqli $mysqli):int{
	 * $f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->afterInsertHook()";
	 * try{
	 * $status = parent::afterInsertHook($mysqli);
	 * //Debug::print("{$f} returned parent function");
	 * if($status !== SUCCESS){
	 * $err = ErrorMessage::getResultMessage($status);
	 * Debug::warning("{$f} parent function returned error status \"{$err}\"");
	 * return $this->setObjectStatus($status);
	 * }
	 * //Debug::print("{$f} parent function executed successfully");
	 * //Debug::print("{$f} about to enqueue remote backup");
	 * if(!$this->skipRemoteBackup()){
	 * $status = $this->enqueueRemoteBackup($mysqli);
	 * if($status !== SUCCESS){
	 * $err = ErrorMessage::getResultMessage($status);
	 * Debug::error("{$f} enqueue remote backup returned error status \"{$err}\"");
	 * return $this->setObjectStatus($status);
	 * }
	 * }
	 * //Debug::print("{$f} returning successfully");
	 * return SUCCESS;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public function setSignature($signature)
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->setSignature()";
		try {
			$print = false;
			$len = strlen(($signature));
			if ($len !== SODIUM_CRYPTO_SIGN_BYTES) {
				Debug::error("{$f} signature \"{$signature}\" is {$len} bytes, when it must be " . SODIUM_CRYPTO_SIGN_BYTES);
			} elseif ($print) {
				Debug::print("{$f} signature is the correct length");
			}
			$this->setColumnValue("signature", $signature);
			return $this->getSignature();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateSignature()
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->generateSignature()";
		$print = false;
		$signable = $this->getSignableMessage();
		if (empty($signable)) {
			Debug::error("{$f} signable message is null or empty string");
		} elseif ($print) {
			Debug::print("{$f} about to sign the following pile:");
			Debug::print("{$f} signable");
		}
		$signature = $this->signMessage($signable);
		$len = strlen($signature);
		$shudbi = SODIUM_CRYPTO_SIGN_BYTES;
		if ($len !== $shudbi) {
			Debug::error("{$f} signature \"{$signature}\" is {$len} bytes, when it must be {$shudbi}");
		} elseif ($print) {
			Debug::print("{$f} signature is the correct length; about to assign newly generated signature");
		}
		return $this->setSignature($signature);
	}

	public function hasSignature()
	{
		return $this->hasColumnValue("signature");
	}

	public function getSignature()
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->getSignature()";
		try {
			$print = false;
			if ($this->hasSignature()) {
				return $this->getColumnValue("signature");
			} elseif ($print) {
				Debug::print("{$f} signature is undefined; about to sign endpoint");
			}
			return $this->generateSignature();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function signMessage($message)
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")->signMessage()";
		try {
			$print = false;
			if (! $this->hasSignatoryData()) {
				$signatory = $this->setSignatoryData(user());
			} else {
				$signatory = $this->getSignatoryData();
			}
			$signed = $signatory->signMessage($message);
			$len = strlen($signed);
			$shudbi = SODIUM_CRYPTO_SIGN_BYTES;
			if ($len !== $shudbi) {
				Debug::error("{$f} signature \"{$signed}\" is {$len} bytes, when it must be {$shudbi}");
			} elseif ($print) {
				Debug::print("{$f} signature is the correct length; returning normally");
			}
			return $signed;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$f = __METHOD__; //UserSigned::getShortClass()."(".static::getShortClass().")::declareColumns()";
		parent::declareColumns($columns, $ds);
		$signature = new SodiumCryptoSignatureDatum();
		$signatory = new UserMetadataBundle("signatory", $ds);
		$signatory->setNullable(false);
		if (true || ! $ds->isSignatureRequired()) {
			$signatory->setNullable(true);
			$signature->setNullable(true);
		}
		static::pushTemporaryColumnsStatic($columns, $signature, $signatory);
	}
}