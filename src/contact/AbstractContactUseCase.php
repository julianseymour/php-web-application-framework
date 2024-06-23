<?php

namespace JulianSeymour\PHPWebApplicationFramework\contact;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

abstract class AbstractContactUseCase extends UseCase{
	
	public abstract function getEmailClass():string;
	
	public abstract function getFormClass():string;
	
	public function afterLoadHook(mysqli $mysqli):int{
		$f = __METHOD__;
		$ret = parent::afterLoadHook($mysqli);
		$admin_class = config()->getAdministratorClass();
		$admin = new $admin_class();
		$select = $admin->select()->where(
			new WhereCondition("num", OPERATOR_EQUALS)
			)->withParameters(1)->withTypeSpecifier('i');
			if($result = $select->executeGetResult($mysqli)){
				if($result->num_rows !== 1){
					Debug::warning("{$f} {$result->num_rows} rows in result");
					return $this->setObjectStatus(ERROR_NOT_FOUND);
				}
				$results = $result->fetch_all(MYSQLI_ASSOC);
				$status = $admin->processQueryResultArray($mysqli, $results[0]);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}else{
				Debug::warning("{$f} failed to get result of query \"{$select}\"");
				return $this->setObjectStatus(ERROR_MYSQL_RESULT);
			}
			$admin->loadIntersectionTableKeys($mysqli);
			$admin->loadForeignDataStructures($mysqli, false, 3);
			user()->setCorrespondentObject($admin);
			$admin->setCorrespondentObject(user());
			return $ret;
	}
	
	public function execute():int{
		$f = __METHOD__;
		try{
			$print = false;
			$directive = directive();
			if($directive !== DIRECTIVE_VALIDATE){
				if($print){
					Debug::print("{$f} nothing was submitted");
				}
				return parent::execute();
			}elseif($print){
				Debug::print("{$f} about to validate form submission");
			}
			$emc = $this->getEmailClass();
			$email = new $emc();
			$email->setSender(user());
			$email->setRecipient(user()->getCorrespondentObject());
			$fc = $this->getFormClass();
			$form = new $fc(ALLOCATION_MODE_FORM, $email);
			$status = $form->validate($_POST);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} validating form returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} form validation successful");
			}
			$status = $email->processForm($form, $_POST);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processing form returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully processed form");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $email->sendAndInsert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} sendAndInsert returned error status \"{$err}\"");
			}elseif($print){
				Debug::warning("{$f} sendAndInsert executed successfully");
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public function getResponder(int $status):?Responder{
		$f = __METHOD__;
		$print = false;
		$directive = directive();
		if($directive !== DIRECTIVE_VALIDATE || $status !== SUCCESS){
			if($print){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} error status \"{$err}\", returning parent function");
			}
			return parent::getResponder($status);
		}elseif($print){
			Debug::print("{$f} returning InfoBoxResponder");
		}
		return new InfoBoxResponder(DivElement::wrap("Your inquiry has been submitted. Please give the administrator up to 2 business days to reply before trying again."));
	}
	
	public function getPageContent():?array{
		$f = __METHOD__;
		$print = false;
		$directive = directive();
		if($directive !== DIRECTIVE_VALIDATE){
			if($print){
				Debug::print("{$f} form was not submitted, or something went wrong");
			}
			$emc = $this->getEmailClass();
			$email = new $emc();
			$fc = $this->getFormClass();
			return [new $fc(ALLOCATION_MODE_ULTRA_LAZY, $email)];
		}
		$status = $this->getObjectStatus();
		if($status !== SUCCESS){
			if($print){
				Debug::print("{$f} something went wrong");
			}
			return [ErrorMessage::getVisualError($status)];
		}
		return [
			ErrorMessage::getVisualNotice("Your inquiry has been submitted. Please give the administrator up to 2 business days to reply before trying again.")
		];
	}
}