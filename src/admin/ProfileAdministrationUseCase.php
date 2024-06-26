<?php

namespace JulianSeymour\PHPWebApplicationFramework\admin;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getRequestURISegment;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\UpdateResponder;
use mysqli;

abstract class ProfileAdministrationUseCase extends InteractiveUseCase{

	public function getLoadoutGeneratorClass(?PlayableUser $object=null):?string{
		$f = __METHOD__;
		$print = false;
		if(user() instanceof Administrator && request()->hasInputParameter("correspondentKey", $this)){
			if($print){
				Debug::print("{$f} user is an administrator, and correspondent key input parameter exists");
			}
			return ProfileAdministrationLoadoutGenerator::class;
		}elseif($print){
			Debug::print("{$f} correspondentKey does not exist, or user is not an administrator");
		}
		return null;
	}

	public static function getCorrespondentClass():string{
		$f = __METHOD__;
		$segment = getRequestURISegment(1);
		switch($segment){
			case ACCOUNT_TYPE_USER:
				return config()->getNormalUserClass();
			case ACCOUNT_TYPE_SHADOW:
				return config()->getShadowUserClass();
			default:
				Debug::error("{$f} invalid request URI segment \"{$segment}\"");
		}
	}

	public function getUriSegmentParameterMap():?array{
		return [
			"action",
			"correspondentAccountType",
			"correspondentKey"
		];
	}

	public function getConditionalDataOperandClasses():?array{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalProcessedFormClasses():?array{
		return [
			$this->getProcessedFormClass()
		];
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object):?UserData{
		return $this->acquireCorrespondentObject($mysqli);
	}

	public function acquireCorrespondentObject(mysqli $mysqli):?UserData{
		$f = __METHOD__;
		$print = false;
		$user = user();
		if($user->hasCorrespondentObject()){
			if($print){
				Debug::print("{$f} correspondent was already assigned");
			}
			return $user->getCorrespondentObject();
		}elseif(!$user->hasForeignDataStructure("correspondent")){
			if($print){
				$uc = $user->getShortClass();
				$key = $user->hasIdentifierValue() ? $user->getIdentifierValue() : "[undefined]";
				$decl = $user->getDeclarationLine();
				$did = $user->getDebugId();
				Debug::print("{$f} user of class {$uc} with key \"{$key}\" and debug ID {$did}, instantated {$decl} does not have a correspondent object");
			}
			return null;
		}elseif($print){
			Debug::print("{$f} correspondent was loaded via loadout");
		}
		$correspondent = $user->getFirstRelationship("correspondent");
		/*if($correspondent instanceof Administrator){
			$key = $correspondent->getIdentifierValue();
			Debug::error("{$f} correspondent with key \"{$key}\" should never be an administrator");
		}*/
		$correspondent->setCorrespondentObject($user);
		return $user->setCorrespondentObject($correspondent);
	}

	public function afterLoadHook(mysqli $mysqli):int{
		$f = __METHOD__;
		$print = false;
		$status = parent::afterLoadHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} {$err}");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} about to call acqireCorrespondentObject");
		}
		$correspondent = $this->acquireCorrespondentObject($mysqli);
		if($correspondent === null){
			if($print){
				Debug::print("{$f} correspondent object returned null");
			}
			return $this->setObjectStatus(ERROR_FILE_NOT_FOUND);
		}elseif($print){
			Debug::print("{$f} returning normally");
		}
		return $status;
	}

	public function isPageUpdatedAfterLogin():bool{
		return true;
	}

	public function getConditionalElementClasses():?array{
		return [
			$this->getProcessedDataType() => $this->getProcessedFormClass()
		];
	}

	public function getPageContent():?array{
		$f = __METHOD__;
		$print = false;
		$insert_here = $this->getInsertHereElement(null);
		if($insert_here === null){
			return parent::getPageContent();
		}
		$content = [
			$insert_here
		];
		$correspondent = user()->getCorrespondentObject();
		$doc = $this->getDataOperandClass();
		$element_class = $this->getConditionalElementClass($doc::getDataType());
		$phylum = $doc::getPhylumName();
		if($correspondent->hasForeignDataStructureList($phylum)){
			$objects = $correspondent->getForeignDataStructureList($phylum);
			if($print){
				Debug::print("{$f} ".count($objects)." objects");
			}
			foreach($objects as $object){
				$element = new $element_class(ALLOCATION_MODE_LAZY, $object);
				array_push($content, $element);
			}
		}elseif($print){
			Debug::print("{$f} correspondent lacks any interesting data in phylum \"{$phylum}\"");
		}
		return $content;
	}

	public function getProcessedDataListClasses(): ?array{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getInsertHereElement(?DataStructure $ds=null){
		$f = __METHOD__;
		$print = false;
		$mysqli = db()->getConnection(PublicReadCredentials::class);
		$correspondent = $this->acquireCorrespondentObject($mysqli);
		if($correspondent === null){
			if($print){
				Debug::print("{$f} correspondent object returned null");
			}
			return null;
		}elseif($correspondent instanceof Administrator){
			Debug::error("{$f} this use case is for administrators to issue invoices to customers, not the other way around");
		}
		$doc = $this->getDataOperandClass();
		$new_data_operand = new $doc();
		if(!BACKWARDS_REFERENCES_ENABLED && $new_data_operand->hasColumn("userKey")){
			$new_data_operand->getColumn("userKey")->setRank(RANK_PARENT);
		}
		$new_data_operand->setUserData($correspondent);
		$form_class = $this->getProcessedFormClass();
		$form = new $form_class(ALLOCATION_MODE_LAZY);
		$form->bindContext($new_data_operand);
		return $form;
	}

	public function isCurrentUserDataOperand():bool{
		return false;
	}

	public function getResponder(int $status): ?Responder{
		if($status !== SUCCESS){
			return parent::getResponder($status);
		}
		switch(directive()){
			case DIRECTIVE_DELETE_FOREIGN:
				return new UpdateResponder();
			case DIRECTIVE_INSERT:
				return new InsertAfterResponder();
			default:
		}
		return parent::getResponder($status);
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}
}
