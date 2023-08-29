<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\argon_hash;
use function JulianSeymour\PHPWebApplicationFramework\base64url_encode;
use function JulianSeymour\PHPWebApplicationFramework\ip_mask;
use function JulianSeymour\PHPWebApplicationFramework\is_base64;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\SecretKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyTrait;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\access\RequestEvent;
use Exception;
use mysqli;

abstract class ConfirmationCode extends RequestEvent implements EmailNoteworthyInterface{

	use EmailNoteworthyTrait;

	protected static $requestTimeoutDuration = - 1;

	protected abstract function encrypt(string $data): ?string;

	protected abstract function decrypt(string $data): ?string;

	public abstract static function getSentEmailStatus();

	public abstract static function getConfirmationUriStatic($suffix);

	public abstract static function getConfirmationCodeTypeStatic();

	public abstract function getKeypair();

	public function __construct(){
		$f = __METHOD__;
		try {
			$print = false;
			parent::__construct();
			$type = static::getConfirmationCodeTypeStatic();
			if ($type !== ACCESS_TYPE_BACKUP) {
				$this->setConfirmationCodeType($type);
			} elseif ($print) {
				Debug::print("{$f} this is just a backup; skipping record type initialization");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function hasSubtypeStatic():bool{
		return true;
	}
	
	public static function getSubtypeStatic(): string{
		return static::getConfirmationCodeTypeStatic();
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);

		$secretCode = new SecretKeyDatum("secretCode"); // secret that is hashed to get the confirmation code
		$secretCode->volatilize();
		$xoredSecretCode = new CipherDatum("xoredSecretCode"); // the secret code, xor encrypted with xor key
		$xoredSecretCode->volatilize();
		$secretCodeXorKey = new SecretKeyDatum("secretCodeXorKey"); // xor key used to transcrypt the secret code
		$secretCodeXorKey->volatilize();
		$hashedSecretCode = new BlobDatum("hashedSecretCode"); // hash of (nonce.secret)
		$hashedSecretCode->volatilize();

		$confirmationCodeType = new StringEnumeratedDatum("confirmationCodeType");
		$confirmationCodeType->setValidEnumerationMap([
			ACCESS_TYPE_ACTIVATION,
			ACCESS_TYPE_CHANGE_EMAIL,
			ACCESS_TYPE_UNLISTED_IP_ADDRESS,
			ACCESS_TYPE_RESET,
			ACCESS_TYPE_LOCKOUT_WAIVER
		]);

		$nonce = new NonceDatum("nonce");
		$nonce->setRequiredLength(SODIUM_CRYPTO_PWHASH_SALTBYTES);
		$data = new TextDatum("data");
		$data->setDefaultValue(null);
		static::pushTemporaryColumnsStatic($columns, $nonce, $data, $confirmationCodeType, $secretCode, $xoredSecretCode, $secretCodeXorKey, $hashedSecretCode);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"disableNotification",
			"dismissNotification"
		]);
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$columns['userNameKey']->setOnDelete(REFERENCE_OPTION_CASCADE);
	}

	public function getSubtypeValue(){
		return $this->getConfirmationCodeType();
	}

	public static function getTableNameStatic(): string{
		return "confirmation_codes";
	}

	protected function getConfirmationUriGetParameters(): array{
		$f = __METHOD__;
		$x1 = $this->getSecretCodeXorKey();
		if (empty($x1)) {
			Debug::error("{$f} secret code XOR key is null or empty string");
		}
		return [
			'x1_64' => base64_encode($x1),
			'confirmationCodeKey' => $this->getIdentifierValue()
		];
	}

	public function getConfirmationUri(){
		$f = __METHOD__;
		$print = false;
		$this->getHashedSecretCode();
		$params = $this->getConfirmationUriGetParameters();
		if (! isset($params['x1_64'])) {
			Debug::error("{$f} x1_64 is undefined");
		}
		$uri = $this->getConfirmationUriStatic(base64url_encode($this->encrypt(http_build_query($params))));
		if ($print) {
			Debug::print("{$f} confirmation URI is \"{$uri}\"");
		}
		return $uri;
	}

	public function hasAdditionalData(): bool{
		return $this->hasColumnValue("data");
	}

	public function processDecryptedAdditionalDataArray($data_arr){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$confirm = base64_decode($data_arr['confirm_64']);
			$this->setHashedSecretCode($confirm);
			$xored_secret = base64_decode($data_arr['xored_secret_64']);
			$this->setXoredSecretCode($xored_secret);
			if (isset($data_arr['emailAddress']) && method_exists($this, "setEmailAddress")) {
				$email = $data_arr['emailAddress'];
				$this->setEmailAddress($email);
			} elseif ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getSecretCode(){
		$f = __METHOD__;
		try {
			if ($this->hasSecretCode()) {
				return $this->getColumnValue("secretCode");
			}
			Debug::error("{$f} secret code is undefined");
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setHashedSecretCode($code){
		return $this->setColumnValue("hashedSecretCode", $code);
	}

	public function setSecretCodeXorKey($key){
		$f = __METHOD__;
		$print = false;
		if (empty($key)) {
			Debug::error("{$f} key is null or empty string");
		} elseif ($print) {
			Debug::print("{$f} returning normally");
		}
		return $this->setColumnValue("secretCodeXorKey", $key);
	}

	public function getSecretCodeXorKey(){
		return $this->getColumnValue("secretCodeXorKey");
	}

	public function setXoredSecretCode($code){
		return $this->setColumnValue("xoredSecretCode", $code);
	}

	public function hasHashedSecretCode(): bool{
		return $this->hasColumnValue("hashedSecretCode");
	}

	public function getHashedSecretCode(){
		$f = __METHOD__;
		try {
			if ($this->hasHashedSecretCode()) {
				return $this->getColumnValue("hashedSecretCode");
			}
			return null;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateFraudReportUriSuffix(){
		return null;
	}

	public function reload(mysqli $mysqli, $foreign = true): int{
		$reloaded = parent::reload($mysqli, $foreign);
		$this->setSecretCodeXorKey($this->getSecretCodeXorKey());
		return $reloaded;
	}

	protected function generateSecretCode(){
		$f = __METHOD__;
		try {
			$print = false;
			iF ($print) {
				Debug::print("{$f} entered");
			}
			if ($this->hasSecretCode()) {
				if ($print) {
					Debug::print("{$f} already generated");
				}
				return $this->getSecretCode();
			} elseif ($print) {
				Debug::print("{$f} generating a new secret code");
			}
			$secret = $this->setSecretCode(random_bytes(32)); // destroyed
			$x1 = $this->setSecretCodeXorKey(random_bytes(32)); // in the confirmation URL
			$xored_secret = $secret ^ $x1; // gets stored in the request record
			$this->setXoredSecretCode($xored_secret);
			$nonce = $this->generateNonce();
			$confirm = argon_hash($secret, $nonce);
			$this->setHashedSecretCode($confirm);
			return $this->getSecretCode();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function generateSecretCodeXorKey(){
		$f = __METHOD__;
		try {
			$this->generateSecretCode();
			$x1 = $this->getSecretCodeXorKey();
			return $x1;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasSecretCodeXorKey(): bool{
		return $this->hasColumnValue("secretCodeXorKey");
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$this->generateAdditionalData();
			if ($print) {
				Debug::print("{$f} about to call generateSecretCode()");
			}
			$this->generateSecretCode();
			if ($print) {
				Debug::print("{$f} about to call parent function");
			}
			return parent::afterGenerateInitialValuesHook();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function parseAdditionalData(){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::error("{$f} entered");
			}
			$data_clear = $this->getDecryptedAdditionalData();
			if (! isset($data_clear) || $data_clear == "") {
				if ($print) {
					Debug::warning("{$f} data cleartext is undefined");
				}
				return $this->setObjectStatus(FAILURE);
			} elseif ($print) {
				Debug::print("{$f} decrypted additional data is \"{$data_clear}\"");
			}
			$parsed = [];
			parse_str($data_clear, $parsed);
			if (empty($parsed)) {
				Debug::error("{$f} parsed additional data is null");
			}
			$this->processDecryptedAdditionalDataArray($parsed);
			if (! $this->hasHashedSecretCode()) {
				Debug::error("{$f} missing confirmation code");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getXoredSecretCode(){
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->hasXoredSecretCode()) { // for records being loaded
				if ($print) {
					Debug::print("{$f} xored secret code is undefined -- need to get it from the additionalData");
				}
				$this->parseAdditionalData();
				if (! $this->hasXoredSecretCode()) {
					Debug::error("{$f} xoredSecretCode is undefined");
				}
			}
			return $this->getColumnValue("xoredSecretCode");
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function generateXoredSecretCode(){
		$f = __METHOD__;
		try {
			if (! $this->hasXoredSecretCode()) {
				$this->generateSecretCode();
			}
			return $this->getXoredSecretCode();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateHashedSecretCode(){
		$f = __METHOD__;
		try {
			if (! $this->hasHashedSecretCode()) {
				$this->generateSecretCode();
			}
			return $this->getHashedSecretCode();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getAdditionalDataArray(): array{
		$xored_secret = $this->generateXoredSecretCode();
		$confirm = $this->generateHashedSecretCode();
		return [
			'xored_secret_64' => base64_encode($xored_secret),
			'confirm_64' => base64_encode($confirm)
		];
	}

	public function generateAdditionalData(){
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->hasAdditionalData()) {
				if ($print) {
					Debug::print("{$f} already generated");
				}
				return $this->getAdditionalData();
			}
			$arr = $this->getAdditionalDataArray();
			$data = http_build_query($arr);
			$ret = $this->encodeAdditionalData($data);
			if ($print) {
				Debug::print("{$f} additional data is \"{$ret}\"");
			}
			return $this->setAdditionalData($ret);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setAdditionalData($data){
		return $this->setColumnValue('data', $data);
	}

	public function isEmailNotificationWarranted($recipient): bool{
		return true;
	}

	public function getRequestTimeoutDuration(){
		$f = __METHOD__;
		try {
			if (! isset(static::$requestTimeoutDuration) || static::$requestTimeoutDuration < 0) {
				static::debugErrorStatic($f, ERROR_NULL_DURATION);
			}
			return static::$requestTimeoutDuration;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Write an email confirmation code record to the database for a particular user
	 *
	 * @param mysqli $mysqli
	 * @param AuthenticatedUser $user
	 * @return int
	 */
	public static function submitStatic(mysqli $mysqli, PlayableUser $user){
		$f = __METHOD__;
		try {
			$print = false;
			if ($user instanceof AnonymousUser) {
				Debug::error("{$f} this should never be passed an anonymous user");
			} elseif ($print) {
				Debug::print("{$f} user is authenticated");
			}
			// generate confirmation code object
			$confirmation_code = new static();
			$confirmation_code->setUserData($user);
			$status = $confirmation_code->extractAdditionalDataFromUser($user);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} processing post returned error status \"{$err}\"");
				$confirmation_code->setObjectStatus($status);
			}
			// insert confirmation code
			$status = $confirmation_code->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} error inserting email verification code: \"{$err}\"");
				return $user->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} about to send email");
			}
			// send email
			$email_class = $confirmation_code->getEmailNotificationClass();
			$spam = new $email_class();
			$spam->setSubjectData($confirmation_code);
			$email = $confirmation_code->getEmailAddress();
			$spam->setSenderEmailAddress("noreply@".DOMAIN_LOWERCASE);
			$spam->setRecipientEmailAddress($email);
			$spam->setRecipient($confirmation_code->getUserData());
			if(!$spam->hasRecipient()){
				Debug::error("{$f} immediately after assigning it, recipient is undefined");
			}
			$status = $spam->sendAndInsert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} sending email returned error status \"{$err}\"");
				return $status;
			} elseif ($print) {
				Debug::print("{$f} returning normally");
			}
			return $confirmation_code->setObjectStatus($confirmation_code->getSentEmailStatus());
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @param AuthenticatedUser $user
	 * @return int
	 */
	protected function extractAdditionalDataFromUser(PlayableUser $user){
		return SUCCESS;
	}

	public function getAdditionalData(){
		return $this->getColumnValue('data');
	}

	public static function getPhylumName(): string{
		return "confirmationCodes";
	}

	public static final function getDataType(): string{
		return DATATYPE_CONFIRMATION_CODE;
	}

	public final function hasSubtypeValue(): bool{
		return true;
	}

	public function hasXoredSecretCode(): bool{
		return $this->hasColumnValue("xoredSecretCode");
	}

	public function getReasonLogged(){
		return $this->getColumnValue('reasonLogged');
	}

	public static function getCidrNotation(){
		return $_SERVER['REMOTE_ADDR'] . "/" . ip_mask($_SERVER['REMOTE_ADDR']);
	}

	public function hasConfirmationCodeType(): bool{
		return $this->hasColumnValue('confirmationCodeType');
	}

	public function getConfirmationCodeType(){
		if ($this->hasConfirmationCodeType()) {
			return $this->getColumnValue('confirmationCodeType');
		}
		return $this->setConfirmationCodeType(static::getConfirmationCodeTypeStatic());
	}

	public function setConfirmationCodeType($value){
		return $this->setColumnValue("confirmationCodeType", $value);
	}

	public function generateNonce(){
		$f = __METHOD__;
		try {
			$nonce = $this->getColumnValue('nonce');
			if (isset($nonce) && $nonce != "") {
				return $this->getNonce();
			}
			return $this->setNonce(random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES));
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setNonce($n){
		$f = __METHOD__;
		$len = strlen($n);
		$mnl = SODIUM_CRYPTO_PWHASH_SALTBYTES;
		if ($len !== $mnl) {
			Debug::error("{$f} nonce is wrong length ({$len}, should be {$mnl})");
		}
		return $this->setColumnValue('nonce', $n);
	}

	public function hasNonce(): bool{
		return $this->hasColumnValue('nonce');
	}

	public function getNonce(){
		$f = __METHOD__;
		if (! $this->hasNonce()) {
			Debug::error("{$f} nonce is undefined");
			$this->setObjectStatus(ERROR_NULL_NONCE);
			return null;
		}
		return $this->getColumnValue('nonce');
	}

	public function setSecretCode($code){
		return $this->setColumnValue("secretCode", $code);
	}

	public function hasSecretCode(){
		return $this->hasColumnValue("secretCode");
	}

	public function validateCode(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			if (! $this->hasSecretCode()) {
				Debug::error("{$f} secret code is undefined");
			} elseif (! $this->hasHashedSecretCode()) {
				Debug::error("{$f} confirmation code is undefined");
			} elseif ($print) {
				Debug::print("{$f} about to validate confirmation code");
			}
			$secret = $this->getSecretCode($mysqli);
			$nonce = $this->getNonce();
			$alleged_code = argon_hash($secret, $nonce);
			$actual_code = $this->getHashedSecretCode();
			if ($print) {
				$ac64 = base64_encode($actual_code);
				Debug::print("{$f} actual code in base64 is \"{$ac64}\"");
			}
			if ($actual_code === $alleged_code) {
				if ($print) {
					Debug::print("{$f} code validated successfully");
				}
				return $this->setObjectStatus(SUCCESS);
			} elseif ($print) {
				Debug::warning("{$f} code validation failed");
			}
			return $this->setObjectStatus(ERROR_CONFIRMATION_CODE_UNDEFINED);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function loadFailureHook(): int{
		return $this->setObjectStatus(ERROR_LINK_EXPIRED);
	}

	public function getCommentRoot(){
		return $this;
	}

	public static function getPrettyClassName():string{
		return _("Confirmation code");
	}

	public static function getPrettyClassNames():string{
		return _("Confirmation codes");
	}

	public final function getDecryptedAdditionalData(){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			if (! $this->hasAdditionalData()) {
				Debug::error("{$f} additional data is null or empty string");
			}
			$data_64 = $this->getAdditionalData();
			if (! isset($data_64)) {
				Debug::error("{$f} additional data is undefined");
			} elseif (! is_base64($data_64)) {
				Debug::error("{$f} additional data is not base 64");
			}
			$data = base64_decode($data_64);
			$clear = $this->decrypt($data); // getKeypair()->decrypt($data, LOGIN_TYPE_FULL);
			if (! isset($clear) || $clear == "") {
				Debug::error("{$f} cleartext returned null");
			} elseif ($print) {
				Debug::print("{$f} cleartext is \"{$clear}\"");
			}
			$parsed = [];
			parse_str($clear, $parsed);
			if ($parsed == null) {
				Debug::warning("{$f} error parsing cleartext; about to try urldecoding it");
				$unescaped = rawurldecode($clear);
				Debug::print("{$f} unescaped cleartext is \"{$unescaped}\"");
				parse_str($unescaped, $parsed);
				if ($parsed == null) {
					Debug::error("{$f} parsed additional data array returned null");
				}
				Debug::print("{$f} successfully parsed additional data array the second time");
				$clear = $unescaped;
			} elseif ($print) {
				Debug::print("{$f} successfully parsed additional data array the first time");
			}
			return $clear;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public final function encodeAdditionalData($data){
		$f = __METHOD__;
		try {
			$print = false;
			if (is_array($data)) {
				Debug::error("{$f} additional data is an array");
			} elseif ($print) {
				Debug::print("{$f} about to encrypt additional data");
			}
			return base64_encode($this->encrypt($data));
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
