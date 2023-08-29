<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\argon_hash;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoBoxPublicKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

/**
 * DContains all the keys and nonces needed to register a user, change or hard reset password
 *
 * @author j
 *        
 */
class PasswordData extends DataStructure{

	use PasswordDerivedColumnsTrait;

	public static function getDatabaseNameStatic():string{
		return "error";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$hash = new PasswordDatum("password");
		$privateKey = new BlobDatum("privateKey");
		$public = new SodiumCryptoBoxPublicKeyDatum("publicKey");
		$keyGenerationNonce = new Base64Datum("keyGenerationNonce");
		$signaturePrivateKey = new BlobDatum("signaturePrivateKey");
		$signatureSeed = new BlobDatum("signatureSeed");
		$public_sign = new Base64Datum("signaturePublicKey");
		$encrypted_nonce = new NonceDatum("sessionRecoveryNonce");
		$dsk = new BlobDatum("deterministicSecretKey");
		static::pushTemporaryColumnsStatic($columns, $hash, $privateKey, $public, $keyGenerationNonce, $signaturePrivateKey, $signatureSeed, $encrypted_nonce, $public_sign, $dsk);
	}

	public static function getPrettyClassName():string{
		return _("Password");
	}

	public static function getTableNameStatic(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPrettyClassNames():string{
		return _("Passwords");
	}

	public static function getPhylumName(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_VOLATILE;
	}

	/**
	 * generate the password hash, asymmetric keypair, etc needed for authentication and so forth
	 */
	public static function generate($password, $storage_keypair = null, $crypto_sign_seed = null){
		$f = __METHOD__;
		try {
			$print = false;
			// 1. generate 2 nonces to salt the dumb user's shitty password
			$keyGenerationNonce = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
			if ($password !== null) {
				// 2. Generate a secret key from your password
				$deterministicSecretKey = argon_hash($password, $keyGenerationNonce); // uses the user's password to generate a code that encrypts the rest of the data. This must be done separately for individual user
				if (strlen($deterministicSecretKey) != 32) { // SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES){
					Debug::error("{$f} secret key is " . strlen($deterministicSecretKey) . " bytes; required length is 32");
					return null;
				}
				// 3. Generate a password hash to verify login. Unfortunately the encrypted private key cannot be used to store both because bcrypt is nondeterministic
				$options = [
					'memory_cost' => 2048,
					'time_cost' => 4,
					'threads' => 3
				];
				if ($print) {
					if (defined("PASSWORD_ARGON2ID")) {
						Debug::print("{$f} the server supports argon2id");
					} else {
						Debug::print("{$f} the server does not support argon2id");
					}
				}
				$password_hash = password_hash($password, PASSWORD_ARGON2I, $options);
			}
			// 4. USER surrogate keypair. Use the surrogate public key to encrypt sensitive data; private key never leaves volatile memory and is stored only in encrypted form. This function now requires a keypair to make it useful for changing passwords without replacing the keypair, which stays the same between key changes
			if (! isset($storage_keypair)) {
				if ($print) {
					Debug::print("{$f} keypair is null; generating a new keypair");
				}
				$storage_keypair = sodium_crypto_box_keypair();
			}
			$publicKey = sodium_crypto_box_publickey($storage_keypair);
			$privateKey = sodium_crypto_box_secretkey($storage_keypair);
			// 5. Generate a reauthentication key so the password hash is not used as the sole parameter for reauthentication b/c it is stored in plaintext for someone who is able to breach both database and session
			if (! isset($crypto_sign_seed)) {
				// Debug::print("{$f} signature keypair seed is null; generating a new one now");
				$crypto_sign_seed = random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES);
				if($print){
					Debug::print("{$f} generated signature seed in base 64 ".base64_encode($crypto_sign_seed));
				}
			}elseif($print){
				Debug::print("{$f} signature seed \"".base64_encode($crypto_sign_seed)."\" was provided");
			}
			$length = strlen($crypto_sign_seed);
			if ($length !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
				$shoodbi = SODIUM_CRYPTO_SIGN_SEEDBYTES;
				Debug::error("{$f} incorrect seed length ({$length}, should be {$shoodbi}");
			}elseif($print){
				Debug::print("{$f} signature seed is the correct length");
			}

			// 6. generate signature keypair using the reauthentication key as a seed
			$signature_keypair = sodium_crypto_sign_seed_keypair($crypto_sign_seed);
			$signaturePublicKey = sodium_crypto_sign_publickey($signature_keypair);
			$signaturePrivateKey = sodium_crypto_sign_secretkey($signature_keypair);
			
			$cipher = AsymmetricEncryptionScheme::encrypt($crypto_sign_seed, $publicKey);
			if($print){
				if(AsymmetricEncryptionScheme::decrypt($cipher, $storage_keypair) !== $crypto_sign_seed){
					Debug::error("{$f} cipher was not successfully decrypted");
				}else{
					Debug::print("{$f} it's working now");
				}
			}
			
			// 7. generate a session recovery nonce that is used to disassociate an object from this user except for anyone who can decrypt this nonce
			$session_recov_nonce = random_bytes(32);

			$that = new PasswordData();

			if ($password !== null) {
				$that->setDeterministicSecretKey($deterministicSecretKey);
				$that->setPasswordHash($password_hash);
			}
			$that->setPrivateKey($privateKey);
			$that->setPublicKey($publicKey);
			$that->setKeyGenerationNonce($keyGenerationNonce);
			$that->setSignaturePublicKey($signaturePublicKey);
			$that->setSignaturePrivateKey($signaturePrivateKey);
			$that->setSignatureSeed($crypto_sign_seed);
			$that->setSessionRecoveryNonce($session_recov_nonce);

			if ($print) {
				$vars = [
					'deterministicSecretKey' => $deterministicSecretKey,
					'password_hash' => $password_hash,
					'privateKey' => $privateKey,
					'publicKey' => $publicKey,
					'keyGenerationNonce' => $keyGenerationNonce,
					'signaturePublicKey' => $signaturePublicKey,
					'signaturePrivateKey' => $signaturePrivateKey,
					'signatureSeed' => $crypto_sign_seed,
					'sessionRecoveryNonce' => $session_recov_nonce
				];
				foreach ($vars as $i => $v) {
					Debug::print("{$f} {$i}: " . sha1($v));
				}
				// Debug::printStackTraceNoExit();
			}

			return $that;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasDeterministicSecretKey():bool{
		return $this->hasColumnValue("deterministicSecretKey");
	}

	public function setDeterministicSecretKey(string $value):string{
		return $this->setColumnValue("deterministicSecretKey", $value);
	}

	public function getDeterministicSecretKey():string{
		return $this->getColumnValue("deterministicSecretKey");
	}

	protected function nullPrivateKeyHook(): int{
		return FAILURE;
	}
}
