<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;

class PreMultifactorAuthenticationData extends AuthenticationData{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$half = new BooleanDatum("half_login");
		array_push($columns, $half);
	}

	public function handSessionToUser(PlayableUser $user, ?int $mode = null):PlayableUser{
		$f = __METHOD__;
		if (! $user instanceof AuthenticatedUser) {
			Debug::error("{$f} only authenticated users can use MFA");
		}
		$ret = parent::handSessionToUser($user);
		// $this->setHalfLoginFlag(true);
		$cookie = new PreMultifactorAuthenticationCookie();
		$reauth_nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
		$reauth_key = $cookie->generateReauthenticationKey();
		$reauth_hash = $this->generateReauthenticationHash($reauth_nonce, $reauth_key);
		$pkc = $user->getColumn("privateKey");
		$this->setSignature(sodium_crypto_sign_detached($reauth_hash, sodium_crypto_box_seal_open($user->getColumn("signaturePrivateKey")
			->getCipherValue(), sodium_crypto_box_keypair_from_secretkey_and_publickey(SymmetricEncryptionScheme::decrypt($pkc->getCipherValue(), $this->getDeterministicSecretKey(), $pkc->getNonceValue()), $user->getPublicKey()))));
		return $ret;
	}

	protected static function getUserMetadataBundleName():string{
		return "tempMFAUser";
	}

	public static function getUsernameColumnName():string{
		return "tempMFAUsername";
	}

	public static function getReauthenticationHashColumnName():string{
		return "tempMFAReauthHash";
	}

	public static function getReauthenticationNonceColumnName():string{
		return "tempMFAReauthNonce";
	}

	public static function getDeterministicSecretKeyColumnName():string{
		return "tempMFADeterministicSecretKey";
	}

	public static function getAuthenticationType():string{
		return LOGIN_TYPE_PARTIAL;
	}

	public static function getSignatureColumnName():string{
		return "tempMFASignature";
	}
}
