<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\random_bytes_bcrypt;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\auth\ReauthenticationCookie;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use Exception;

class FullAuthenticationData extends AuthenticationData{

	public static function getAuthenticationType(){
		return LOGIN_TYPE_FULL;
	}

	public function setPreviousLoginMode($mode){
		return $this->setColumnValue("previousLoginMode", $mode);
	}

	public function getPreviousLoginMode(){
		return $this->getColumnValue("previousLoginMode");
	}

	public static function getReauthenticationHashColumnName():string{
		return 'reauth_hash';
	}

	public static function getReauthenticationNonceColumnName():string{
		return 'reauth_nonce';
	}

	public static function getUsernameColumnName():string{
		return "username";
	}

	public static function getDeterministicSecretKeyColumnName():string{
		return 'deterministicSecretKey';
	}

	/**
	 * Do not call this until the user has been set in app()->setUserData()
	 *
	 * {@inheritdoc}
	 * @see AuthenticationData::handSessionToUser()
	 */
	public function handSessionToUser(PlayableUser $user, ?int $mode = null):PlayableUser{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::printStackTraceNoExit("{$f} entered");
			}
			if(!$user->hasNotificationDeliveryTimestamp()){
				$user->setNotificationDeliveryTimestamp(time());
			}
			if($mode === null){
				$mode = $this->getPreviousLoginMode();
			}
			switch($mode){
				case LOGIN_TYPE_PARTIAL:
					if($print){
						Debug::print("{$f} completing a multifactor authentication login");
					}
					$partial = new PreMultifactorAuthenticationData();
					if($print){
						Debug::print("{$f} about to transfer pre-MFA authentication data to full session");
					}
					if(!$partial->hasDeterministicSecretKey()){
						Debug::error("{$f} pre-MFA authentication data lacks a DSK");
					}elseif(!$partial->hasUserKey()){
						Debug::error("{$f} pre-MFA authentication data lacks a user key");
					}elseif(!$partial->hasUserAccountType()){
						Debug::error("{$f} pre-MFA authentication data lacks a user account type");
					}
					$this->setUsername($partial->ejectUsername());
					$this->setUserKey($partial->ejectUserKey());
					$dsk = $partial->ejectDeterministicSecretKey();
					if(empty($dsk)){
						Debug::error("{$f} DSK is empty");
					}elseif($print){
						Debug::print("{$f} deterministic secret key is \"{$dsk}\"");
					}
					$this->setDeterministicSecretKey($dsk);
					$this->setUserAccountType($partial->ejectUserAccountType());
					$user = parent::handSessionToUser($user);
					if(!$user instanceof AnonymousUser){
						$this->setFullLoginFlag(true);
					}
					break;
				case LOGIN_TYPE_FULL:
				case LOGIN_TYPE_UNDEFINED:
					if($print){
						Debug::print("{$f} going straight to fully authenticated");
					}
					$user = parent::handSessionToUser($user);
					if(!$user instanceof AnonymousUser){
						$this->setFullLoginFlag(true);
					}
					break;
				default:
					Debug::error("{$f} invalid login mode \"{$mode}\"");
			}
			$cookie = new ReauthenticationCookie();
			$reauth_nonce = random_bytes_bcrypt(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
			if(strlen($reauth_nonce) == 0){
				Debug::error("{$f} reauthentication nonce is null/empty string");
			}
			$reauth_key = $cookie->generateReauthenticationKey();
			if(strlen($reauth_key) == 0){
				Debug::error("{$f} reauthentication key is null/empty string");
			}
			$reauth_hash = $this->generateReauthenticationHash($reauth_nonce, $reauth_key);
			deallocate($cookie);
			app()->setUserData($user);
			if($print){
				$user_class = $user->getClass();
				$user_key = $user->getIdentifierValue();
				if($user->hasPrivateKey()){
					$pk = $user->getPrivateKey();
					if($pk == null){
						Debug::error("{$f} hasPrivateKey doesn't work");
					}
					Debug::print("{$f} user of class \"{$user_class}\" with key \"{$user_key}\" has a private key defined, and it's \"{$pk}\"");
				}else{
					Debug::error("{$f} user of class \"{$user_class}\" with key \"{$user_key}\" does NOT have a private key defined");
				}
				$spk = $user->getSignaturePrivateKey();
				if($user->hasSignaturePrivateKey()){
					Debug::print("{$f} user of class \"{$user_class}\" with key \"{$user_key}\" has a signature private key defined");
				}else{
					Debug::print("{$f} user of class \"{$user_class}\" with key \"{$user_key}\" does NOT have a signature private key defined");
				}
				$dsk = $this->getDeterministicSecretKey();
				Debug::print("{$f} deterministic secret key has hash " . sha1($dsk));
				Debug::print("{$f} signature private key successfully decrypted");
			}
			$this->setSignature($user->signMessage($reauth_hash));
			return $user;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getFullLoginFlag():bool{
		return $this->getColumnValue("full_login");
	}

	public function ejectFullLoginFlag():?bool{
		return $this->ejectColumnValue("full_login");
	}

	public function setFullLoginFlag(bool $value):bool{
		return $this->setColumnValue("full_login", $value);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$full = new BooleanDatum("full_login");
		$full->setDefaultValue(false);
		$previousLoginMode = new UnsignedIntegerDatum("previousLoginMode", 8);
		$previousLoginMode->volatilize();
		array_push($columns, $full, $previousLoginMode);
	}

	public static function getSignatureColumnName():string{
		return "signature";
	}
}
