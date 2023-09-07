<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\EnabledAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SavePushSubscriptionUseCase extends UseCase{

	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($print) {
				Debug::print("{$f} entered");
			}
			$user = user();
			if ($user == null) {
				Debug::error("{$f} user data returned null");
				return $this->setObjectStatus(ERROR_NULL_USER_OBJECT);
			}
			$sub = new PushSubscriptionData();
			$sub->setUserData($user);
			// $sub->setSignatoryData($user);
			$arr = getInputParameters();
			if (! isset($arr['subscription']['endpoint'])) {
				Debug::error("{$f} Endpoint is undefined");
				if (! is_array($arr['subscription'])) {
					Debug::error("{$f} Subscription is not an array");
				}
			} elseif (! isset($arr['subscription']['keys'])) {
				Debug::error("{$f} Keys are undefined");
			}
			$keys = $arr['subscription']['keys'];
			$endpoint = $sub->setPushApiEndpoint($arr['subscription']['endpoint']);
			$auth = $sub->setAuthPushApiKey($keys['auth']);
			$p256dh = $sub->setP256dhPushApiKey($keys['p256dh']);
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if ($mysqli == null) {
				Debug::error("{$f} mysqli connection failed");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$response = app()->getResponse($this);
			$status = $sub->preventDuplicateEntry($mysqli);
			if ($status !== SUCCESS) {
				if ($print) {
					Debug::print("{$f} subscription already saved");
				}
				$sub = new PushSubscriptionData();
				$status = $sub->load($mysqli, new WhereCondition("endpoint", OPERATOR_EQUALS), [
					$endpoint
				]);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} updating subscription client data returned error status \"{$err}\"");
				}
				if ($user->select()->where(new WhereCondition($user->getIdentifierName(), OPERATOR_EQUALS))->withTypeSpecifier('s')->withParameters($user->getIdentifierValue())->executeGetResultCount($mysqli) === 1) {
					if ($print) {
						Debug::print("{$f} yes, this user exists");
					}
					$sub->setUserData($user);
					$status = $sub->update($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating subscription client data returned error status \"{$err}\"");
					} elseif ($print) {
						Debug::print("{$f} successfully updated endpoint");
					}
					$response->setProperty("pushSubscriptionKey", $sub->getIdentifierValue());
				} else {
					if ($print) {
						Debug::print("{$f} this user does not exist, skipping push subscription owner update");
					}
					$status = FAILURE;
				}
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} about to write to database");
			}
			if(!$sub->hasUserKey()) {
				Debug::error("{$f} user key is undefined");
			} elseif ($print) {
				Debug::print("{$f} user key is \"" . $sub->getUserKey() . "\"");
				if (QueryBuilder::select()->from($user->getDatabaseName(), $user->getTableName())->where(new WhereCondition($user->getIdentifierName(), OPERATOR_EQUALS))->withParameters($user->getIdentifierValue())->withTypeSpecifier('s')->executeGetResultCount($mysqli) !== 1) {
					Debug::error("{$f} user does not have an entry in the database");
				}
			}
			$status = $sub->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} writing to database returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$response->setProperty("pushSubscriptionKey", $sub->getIdentifierValue());
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return $this->setObjectStatus(SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getExecutePermissionClass(){
		return EnabledAccountTypePermission::class;
	}

	public function getDataOperandClass(): ?string{
		return PushSubscriptionData::class;
	}

	public function getConditionalDataOperandClasses(): ?array{
		return [
			$this->getProcessedDataType() => PushSubscriptionData::class
		];
	}

	public function getProcessedDataType(): ?string{
		return DATATYPE_PUSH_SUBSCRIPTION;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/subscribe";
	}

	public function getExecutePermission(){
		return SUCCESS;
	}
}
