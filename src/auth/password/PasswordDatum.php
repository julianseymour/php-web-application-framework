<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class PasswordDatum extends BlobDatum{

	public function getHumanReadableValue(){
		return null;
	}

	public function getHumanWritableValue(){
		return null;
	}

	public final function getSensitiveFlag():bool{
		return true;
	}

	public function hasMinimumLength():bool{
		return true;
	}

	public function getMinimumLength():int{
		return MINIMUM_PASSWORD_LENGTH;
	}

	public function regenerate(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasRegenerationClosure()){
				if($print){
					Debug::print("{$f} this column has a closure for generating its default value");
				}
				return parent::regenerate();
			}
			$use_case = app()->getUseCase();
			$user = user();
			$storage_keypair = $user->getKeypair();
			$crypto_sign_seed = $user->getSignatureSeed(); // PrivateKey();
			$length = strlen($crypto_sign_seed);
			if($length !== SODIUM_CRYPTO_SIGN_SEEDBYTES){
				$shoodbi = SODIUM_CRYPTO_SIGN_SEEDBYTES;
				Debug::error("{$f} incorrect seed length ({$length}, should be {$shoodbi}");
			}elseif($print){
				Debug::print("{$f} signature seed ".base64_encode($crypto_sign_seed)." has correct length");
			}
			$form = $use_case->getProcessedFormObject();
			if(!$form instanceof PasswordGeneratingFormInterface){
				Debug::error("{$f} form is not an instanceof PasswordGeneratingFormInterface");
			}
			$data = PasswordData::generate(
				getInputParameter($form->getPasswordInputName()),
				$storage_keypair,
				$crypto_sign_seed
			);
			$user->unsetColumnValues(
				"privateKeyCipher", 
				"privateKeyAesNonce", 
				"signaturePrivateKeyCipher",
				"sessionRecoveryNonceCipher",
				...$data->getColumnNames()
			);
			$user->setReceptivity(DATA_MODE_RECEPTIVE);
			$status = $user->processPasswordData($data);
			deallocate($data);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processPasswordData returned error status \"{$err}\"");
				return $user->setObjectStatus($status);
			}
			$user->addEventListener(
				EVENT_AFTER_UPDATE, 
				function (AfterUpdateEvent $event, $target) use ($user, $use_case, $f, $print){
					if($print){
						Debug::print("{$f} inside this hideous event handler");
					}
					$target->removeEventListener($event);
					if(db()->hasPendingTransactionId()){
						$txid = db()->getPendingTransactionId();
						if(user() instanceof Administrator){
							$cc = AdminWriteCredentials::class;
						}else{
							$cc = PublicWriteCredentials::class;
						}
						$mysqli = db()->getConnection($cc);
						db()->commitTransaction($mysqli, $txid);
					}
					$select = $user->select()->where(
						new WhereCondition($user->getIdentifierName(), OPERATOR_EQUALS)
					)->withParameters($user->getIdentifierValue())->withTypeSpecifier('s');
					$result = $select->executeGetResult($mysqli);
					if($result->num_rows !== 1){
						Debug::error("{$f} {$result->num_rows} rows");
					}
					$results = $result->fetch_all(MYSQLI_ASSOC);
					$user->setCacheValue($results[0]);
					$auth = new FullAuthenticationData();
					$auth->handSessionToUser($user, LOGIN_TYPE_FULL);
				}, sha1(random_bytes(32))
			);
			return $status;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
