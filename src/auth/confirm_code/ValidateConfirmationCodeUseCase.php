<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\base64url_decode;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\XorEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

abstract class ValidateConfirmationCodeUseCase extends UseCase{

	protected $confirmationCodeObject;

	public abstract static function getConfirmationCodeClass();

	public abstract static function requireAnonymousConfirmation();

	public abstract static function validateOnFormSubmission();

	public abstract function getFormClass(): ?string;

	protected abstract function decrypt(string $data): ?string;

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"skipValidation"
		]);
	}

	public static final function getSuccessfulResultCode():int{
		return static::getBruteforceAttemptClass()::getSuccessfulResultCode();
	}

	public static function getRequestTimeoutDuration(){
		$f = __METHOD__;
		try{
			return static::getConfirmationCodeClass()::getRequestTimeoutDuration();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @return ConfirmationCode
	 */
	public function createConfirmationCodeObject(){
		$class = static::getConfirmationCodeClass();
		return new $class();
	}

	public function hasConfirmationCodeObject():bool{
		return isset($this->confirmationCodeObject);
	}

	public function setConfirmationCodeObject($cc){
		return $this->confirmationCodeObject = $cc;
	}

	public function getConfirmationCodeObject(){
		$f = __METHOD__;
		if(!$this->hasConfirmationCodeObject()) {
			Debug::error("{$f} confirmation code object is undefined");
		}
		return $this->confirmationCodeObject;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getUriSegmentParameterMap(): ?array{
		return [
			"action",
			"blob_64"
		];
	}

	private final function acquireCleartextSuperGlobalParameters(mysqli $mysqli): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$blob_64 = getInputParameter('blob_64', $this);
			if($print){
				Debug::print("{$f} base 64 blob is \"{$blob_64}\"");
			}
			$blob = base64url_decode($blob_64);
			if($print) {
				Debug::print("{$f} blob \"{$blob}\" has hash " . sha1($blob));
			}
			$decrypted = $this->decrypt($blob);
			if(empty($decrypted)) {
				if($print) {
					Debug::warning("{$f} cleartext returned null; going to try urldecode again");
				}
				$blob = rawurldecode($blob);
				$decrypted = $this->decrypt($blob);
				if(empty($decrypted)) {
					if($print) {
						Debug::warning("{$f} cleartext returned null twice");
					}
					return null;
				}elseif($print) {
					Debug::print("{$f} it worked the 2nd time");
				}
			}elseif($print) {
				Debug::print("{$f} worked the first time -- blob is \"{$decrypted}\"");
			}
			$get = [];
			parse_str($decrypted, $get);
			if(! isset($get)) {
				Debug::warning("{$f} parsed array is null or empty string");
				$decrypted = rawurldecode($decrypted);
				parse_str($decrypted, $get);
				if(! isset($get)) {
					Debug::warning("{$f} parsed array is null twice");
					return null;
				}elseif($print) {
					Debug::print("{$f} parsed correctly the second time");
				}
			}elseif($print) {
				Debug::print("{$f} parsed correctly the first time");
			}
			return $get;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @return ConfirmationCode
	 */
	public function acquireConfirmationCodeObject(mysqli $mysqli){
		$f = __METHOD__;
		try{
			$print = false;
			$params = $this->acquireCleartextSuperGlobalParameters($mysqli);
			if($print){
				Debug::printArray($params);
			}
			if(! array_key_exists("confirmationCodeKey", $params)) {
				Debug::error("{$f} confirmation code key is not part of input parameters");
			}
			$ccc = $this->getConfirmationCodeClass();
			$confirmation_code = new $ccc();
			$status = $confirmation_code->load($mysqli, new WhereCondition($confirmation_code->getIdentifierName()), [
				$params['confirmationCodeKey']
			]);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading confirmation code returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}elseif($print) {
				Debug::print("{$f} successfully loaded confirmation code");
			}
			$confirmation_code->setAutoloadFlags(true);
			$status = $confirmation_code->loadForeignDataStructures($mysqli, false, 3);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading foreign data structures returned error status \"{$err}\"");
				$confirmation_code->setObjectStatus($status);
				return $confirmation_code;
			}elseif($print) {
				Debug::print("{$f} successfulyl loaded confirmation code's foreign data structures");
			}
			if(!$confirmation_code->hasUserData()) {
				Debug::error("{$f} confirmation cose lacks user data");
			}
			if(user() instanceof AnonymousUser) {
				$user = $confirmation_code->getUserData();
				$status = $user->loadForeignDataStructures($mysqli);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return null;
				}elseif($print) {
					Debug::print("{$f} successfully loaded user foreign data structures");
				}
			}
			$confirmation_code->setSecretCodeXorKey(base64_decode($params['x1_64']));
			// formerly ConfirmationCode->acquireSecretCode
			$additional_data = $confirmation_code->getDecryptedAdditionalData($mysqli);
			if(! isset($additional_data) || $additional_data == "") {
				if($print) {
					Debug::warning("{$f} data cleartext is undefined");
				}
				$confirmation_code->setObjectStatus(FAILURE);
				return null;
			}elseif($print) {
				Debug::print("{$f} decrypted additional data is \"{$additional_data}\"");
			}
			$parsed = [];
			parse_str($additional_data, $parsed);
			if(empty($parsed)) {
				Debug::error("{$f} parsed additional data is null");
			}
			$confirmation_code->processDecryptedAdditionalDataArray($parsed);
			if(!$confirmation_code->hasHashedSecretCode()) {
				Debug::error("{$f} missing confirmation code");
			}
			$xored_secret = $confirmation_code->getXoredSecretCode();
			$x1 = $confirmation_code->getSecretCodeXorKey();
			if(empty($x1)) {
				if($print) {
					Debug::warning("{$f} secret code XOR key returned null");
				}
				$this->setObjectStatus(FAILURE);
				return null;
			}
			$scheme = new XorEncryptionScheme();
			$secret_code = $scheme->decrypt($xored_secret, $x1);
			$confirmation_code->setSecretCode($secret_code);
			if($print) {
				Debug::print("{$f} returning normally");
			}
			if(empty($secret_code)) {
				Debug::warning("{$f} acquireSecretCode returned null");
				return null;
			}elseif(!$confirmation_code->hasUserData()) {
				Debug::error("{$f} confirmation code lacks a client object");
				return null;
			}
			$this->setConfirmationCodeObject($confirmation_code);
			if(!$this->hasConfirmationCodeObject()) {
				Debug::error("{$f} confirmation code is undefined immediately after setting it");
			}
			return $confirmation_code;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setSkipValidationFlag(bool $skip): bool{
		return $this->setFlag("skipValidation", $skip);
	}

	public static function getConfirmationCodeAlreadyUsedStatus():int{
		$f = __METHOD__;
		try{
			$bfac = static::getBruteforceAttemptClass();
			$rauc = $bfac::getConfirmationCodeAlreadyUsedStatus();
			unset($bfac);
			return $rauc;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getConfirmationCodeKey(){
		$f = __METHOD__;
		if(!$this->hasConfirmationCodeObject()) {
			Debug::error("{$f} confirmation code object is undefined");
		}
		$cc = $this->getConfirmationCodeObject();
		if(!$cc->hasIdentifierValue()) {
			Debug::error("{$f} confirmation code lacks a key");
		}
		return $cc->getIdentifierValue();
	}

	public function getExistingAttemptCount(mysqli $mysqli){
		$f = __METHOD__;
		try{
			$print = false;
			$confirmation_code_key = $this->getConfirmationCodeKey();
			if($confirmation_code_key == null) {
				Debug::error("{$f} confirmation code key is null");
			}
			Debug::print("{$f} confirmation code key is \"{$confirmation_code_key}\"");
			$bfac = static::getBruteforceAttemptClass();
			$db = $bfac::getDatabaseNameStatic();
			$table = $bfac::getTableNameStatic();
			$count = $bfac::selectStatic()->where(
				$bfac::whereIntersectionalHostKey($bfac::getConfirmationCodeClass(), 'confirmationCodeKey')
			)->orderBy(
				new OrderByClause("insertTimestamp", DIRECTION_DESCENDING)
			)->prepareBindExecuteGetResultCount($mysqli, 'ss', $confirmation_code_key, 'confirmationCodeKey');
			if($print) {
				Debug::print("{$f} the number of {$bfac} in table {$db}.{$table} with confirmation code key {$confirmation_code_key} is {$count}");
			}
			return $count;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getPageContent(): ?array{
		$f = __METHOD__;
		$print = false;
		$status = $this->getObjectStatus();
		switch ($status) {
			case STATUS_DISPLAY_PROMPT:
				if($print) {
					Debug::print("{$f} displaying form");
				}
				$form_class = $this->getFormClass();
				return [
					new $form_class(ALLOCATION_MODE_LAZY, $this->getDataOperandObject())
				];
			default:
				if($print) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} object status \"{$err}\"; returning parent function");
				}
				return parent::getPageContent();
		}
	}

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			// connect to database
			$mysqli = db()->reconnect(PublicWriteCredentials::class);
			if(! isset($mysqli)) {
				$status = ERROR_MYSQL_CONNECT;
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$err}");
				return $this->setObjectStatus($status);
			}
			// initialize CodeConfirmationAttempt
			$attempt_class = static::getBruteforceAttemptClass();
			$attempt = new $attempt_class();
			$this->setBruteForceAttemptObject($attempt);
			$attempt->initializeAccessAttempt($mysqli);
			// acquire confirmation code
			$confirmation_code = $this->acquireConfirmationCodeObject($mysqli);
			if($confirmation_code === null) {
				Debug::warning("{$f} confirmation code is null");
				$attempt->setUserData(user());
				$attempt->setLoginResult(ERROR_LINK_EXPIRED);
				$status = $attempt->insert($mysqli);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} inserting request attempt returned error status \"{$err}\"");
				}elseif($print) {
					Debug::print("{$f} successfully inserted failed request attempt");
				}
				return $this->setObjectStatus($status);
			}
			$status = $confirmation_code->getObjectStatus();
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} confirmation code object has error status \"{$err}\"");
				$attempt->setUserData(user());
				$attempt->setLoginResult($status);
				$status = $attempt->insert($mysqli);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} inserting request attempt returned error status \"{$err}\"");
				}elseif($print) {
					Debug::print("{$f} successfully inserted failed request attempt");
				}
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully acquired confirmation code object");
			}
			// get the user who this confirmation attempt applies to
			$true_user = $confirmation_code->getUserData();
			$attempt->setUserData($true_user);
			if($true_user instanceof AuthenticatedUser && ! $attempt->hasUserNameData()) {
				$name = $true_user->getName();
				$did = $true_user->getDebugId();
				$decl = $true_user->getDeclarationLine();
				Debug::error("{$f} username data is undefined after setting user data {$name} with deb ug ID {$did} declared {$decl}");
			}elseif(user() instanceof AnonymousUser) {
				user()->setCorrespondentObject($true_user);
				if($attempt->hasColumn("correspondentKey")) {
					$attempt->setCorrespondentObject(user());
				}elseif($print) {
					Debug::print("{$f} {$attempt_class} does not have a column for storing correspondent key");
				}
			}elseif($print) {
				Debug::print("{$f} user is authenticated");
			}
			// if validation requires a form to be submitted,
			if(static::validateOnFormSubmission()) {
				if($print) {
					Debug::print("{$f} validation occurs on form submission for this use case");
				}
				$directive = directive();
				if($print) {
					Debug::print("{$f} directive is \"{$directive}\"");
				}
				if($directive !== DIRECTIVE_VALIDATE) { // if the user has not submitted a form, display the form
					return $this->setObjectStatus(STATUS_DISPLAY_PROMPT);
				}
				$form_class = $this->getFormClass();
				$context = $this->getDataOperandObject();
				$form = new $form_class(ALLOCATION_MODE_FORM, $context);
				$post = getInputParameters();
				$valid = $form->validate($post);
				if($valid !== SUCCESS) {
					// we don't need to write the attempt for failed form invalidation because that is mostly a UI thing
					$err = ErrorMessage::getResultMessage($valid);
					Debug::warning("{$f} form validation returned error status \"{$err}\"");
					return $this->setObjectStatus($valid);
				}
			}elseif($print) {
				Debug::print("{$f} validation happens with GET parameters for this use case");
			}
			// bruteforce protection
			$result = $attempt->bruteforceProtection($mysqli);
			if($result !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($result);
				Debug::warning("{$f} bruteforce protection returned error status \"{$err}\"");
				$attempt->insert($mysqli);
				return $this->setObjectStatus($result);
			}elseif($print) {
				Debug::print("{$f} successfully passed bruteforce protection");
			}
			// make sure this code has not been used already
			$count = $this->getExistingAttemptCount($mysqli);
			if($count > 0) {
				if($print) {
					Debug::print("{$f} this confirmation code has already been used");
				}
				return $this->setObjectStatus(static::getConfirmationCodeAlreadyUsedStatus()); // don't write BFP
			}elseif($print) {
				Debug::print("{$f} existing attempt count is \"{$count}\"");
			}
			// validate code
			$result = $confirmation_code->validateCode($mysqli);
			if($result !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($result);
				Debug::warning("{$f} validating confirmation code returned error status \"{$err}\"");
			}elseif($print) {
				Debug::print("{$f} successfully validated confirmation code");
			}
			// write bruteforce attempt
			$attempt->setConfirmationCodeObject($confirmation_code);
			$attempt->setLoginResult($result);
			$status = $attempt->insert($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting reset password attempt returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully inserted access attempt");
			}
			if($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} returning with object status \"{$err}\"");
			}
			return $this->setObjectStatus($result);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
