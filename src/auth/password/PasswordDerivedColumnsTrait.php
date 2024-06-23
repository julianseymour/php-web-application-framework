<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\KeypairColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\crypt\SignatureKeypairColumnsTrait;
use Exception;

trait PasswordDerivedColumnsTrait{

	use KeypairColumnsTrait;
	use SignatureKeypairColumnsTrait;

	public function hasKeyGenerationNonce():bool{
		return $this->hasColumnValue('keyGenerationNonce');
	}

	public function setKeyGenerationNonce($nonce){
		$f = __METHOD__;
		try{
			if(empty($nonce)){
				Debug::error("{$f} nonce is null or empty string");
			}
			return $this->setColumnValue('keyGenerationNonce', $nonce);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getKeyGenerationNonce(){
		$f = __METHOD__;
		try{
			if(!$this->hasKeyGenerationNonce()){
				Debug::error("{$f} nonce is undefined");
			}
			$kgn = $this->getColumnValue('keyGenerationNonce');
			$len = strlen($kgn);
			if($len !== SODIUM_CRYPTO_PWHASH_SALTBYTES){
				Debug::error("{$f} nonce length is {$len}");
			}
			return $kgn;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getPasswordHash(){
		return $this->getColumnValue("password");
	}

	public function setSessionRecoveryNonce($nonce){
		return $this->setColumnValue("sessionRecoveryNonce", $nonce);
	}

	public function setSignatureSeed($value){
		return $this->setColumnValue("signatureSeed", $value);
	}

	public function hasSignatureSeed():bool{
		return $this->hasColumnValue("signatureSeed");
	}

	public function getSignatureSeed(){
		$f = __METHOD__;
		$crypto_sign_seed = $this->getColumnValue("signatureSeed");
		$length = strlen($crypto_sign_seed);
		if($length !== SODIUM_CRYPTO_SIGN_SEEDBYTES){
			$shoodbi = SODIUM_CRYPTO_SIGN_SEEDBYTES;
			Debug::error("{$f} incorrect seed length ({$length}, should be {$shoodbi}");
		}
		return $crypto_sign_seed;
	}

	public function setPasswordHash($hash){
		return $this->setColumnValue("password", $hash);
	}

	public function getSessionRecoveryNonce(){
		return $this->getColumnValue("sessionRecoveryNonce");
	}

	public function hasSessionRecoveryNonce():bool{
		return $this->hasColumnValue("sessionRecoveryNonce");
	}
}
