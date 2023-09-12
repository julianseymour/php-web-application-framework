<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoSignatureDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use Exception;

abstract class UserSigned extends UserFingerprint{

	public function isSignatureRequired():bool{
		return true;
	}

	public function getSignableMessage(){
		return json_encode($this->toArray("sign"));
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$columns['signatoryName']->volatilize();
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try{
			if(!$this->hasSignature()) {
				$this->generateSignature();
			}
			if(!$this->hasSignature()) {
				Debug::error("{$f} signature is still undefined");
			}
			return parent::afterGenerateInitialValuesHook();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasSignatoryData():bool{
		return $this->hasForeignDataStructure("signatoryKey");
	}

	public function setSignatoryData($signatory){
		$f = __METHOD__;
		if(! isset($signatory)) {
			Debug::error("{$f} received null parameter");
		}
		// $this->setSignatoryKey($signatory->getIdentifierValue());
		return $this->setForeignDataStructure("signatoryKey", $signatory);
	}

	public function getSignatoryData(){
		return $this->getForeignDataStructure("signatoryKey");
	}

	public function hasSignatoryKey():bool{
		return $this->hasColumnValue("signatoryKey");
	}

	public function getSignatoryKey(){
		return $this->getColumnValue("signatoryKey");
	}

	public function getSignatoryUsername(){
		return $this->getSignatoryData()->getName();
	}

	public function setSignatoryUserKey($key){
		return $this->setColumnValue("signatoryKey", $key);
	}

	public function hasSignatoryAccountType():bool{
		return $this->hasColumnValue("signatoryAccountType");
	}

	public function getSignatoryAccountType(){
		return $this->getColumnValue("signatoryAccountType");
	}

	public function setSignatoryAccountType($value){
		return $this->setColumnValue("signatoryAccountType", $value);
	}

	public static function skipRemoteBackup():bool{
		return false;
	}

	public function setSignature($signature){
		$f = __METHOD__;
		try{
			$print = false;
			$len = strlen(($signature));
			if($len !== SODIUM_CRYPTO_SIGN_BYTES) {
				Debug::error("{$f} signature \"{$signature}\" is {$len} bytes, when it must be " . SODIUM_CRYPTO_SIGN_BYTES);
			}elseif($print) {
				Debug::print("{$f} signature is the correct length");
			}
			$this->setColumnValue("signature", $signature);
			return $this->getSignature();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function generateSignature(){
		$f = __METHOD__;
		$print = false;
		$signable = $this->getSignableMessage();
		if(empty($signable)) {
			Debug::error("{$f} signable message is null or empty string");
		}elseif($print) {
			Debug::print("{$f} about to sign the following pile:");
			Debug::print("{$f} signable");
		}
		$signature = $this->signMessage($signable);
		$len = strlen($signature);
		$shudbi = SODIUM_CRYPTO_SIGN_BYTES;
		if($len !== $shudbi) {
			Debug::error("{$f} signature \"{$signature}\" is {$len} bytes, when it must be {$shudbi}");
		}elseif($print) {
			Debug::print("{$f} signature is the correct length; about to assign newly generated signature");
		}
		return $this->setSignature($signature);
	}

	public function hasSignature():bool{
		return $this->hasColumnValue("signature");
	}

	public function getSignature(){
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasSignature()) {
				return $this->getColumnValue("signature");
			}elseif($print) {
				Debug::print("{$f} signature is undefined; about to sign endpoint");
			}
			return $this->generateSignature();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function signMessage($message){
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->hasSignatoryData()) {
				$signatory = $this->setSignatoryData(user());
			}else{
				$signatory = $this->getSignatoryData();
			}
			$signed = $signatory->signMessage($message);
			$len = strlen($signed);
			$shudbi = SODIUM_CRYPTO_SIGN_BYTES;
			if($len !== $shudbi) {
				Debug::error("{$f} signature \"{$signed}\" is {$len} bytes, when it must be {$shudbi}");
			}elseif($print) {
				Debug::print("{$f} signature is the correct length; returning normally");
			}
			return $signed;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$signature = new SodiumCryptoSignatureDatum();
		$signatory = new UserMetadataBundle("signatory", $ds);
		$signatory->setNullable(false);
		if(true || ! $ds->isSignatureRequired()) {
			$signatory->setNullable(true);
			$signature->setNullable(true);
		}
		array_push($columns, $signature, $signatory);
	}
}