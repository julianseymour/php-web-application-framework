<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\correspondent;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use Exception;
use mysqli;

trait CorrespondentKeyColumnTrait{

	use MultipleColumnDefiningTrait;

	public abstract function getIdentifierValue();

	public function getCorrespondentClass():string{
		return mods()->getUserClass($this->getCorrespondentAccountType());
	}

	public function getCorrespondentAccountTypeString(){
		return UserData::getAccountTypeStringStatic($this->getCorrespondentAccountType());
	}

	public function getCorrespondentDisplayName():string{
		$f = __METHOD__;
		$correspondent = $this->getCorrespondentObject();
		if($correspondent == null) {
			Debug::error("{$f} correspondent object is undefined");
		}
		return $correspondent->getDisplayName();
	}

	public function hasCorrespondentAccountType():bool{
		return $this->hasColumnValue("correspondentAccountType");
	}

	public function setCorrespondentKey($key){
		return $this->setColumnValue('correspondentKey', $key);
	}

	public function hasCorrespondentTemporaryRole():bool{
		return $this->hasColumnValue("correspondentTemporaryRole");
	}

	public function getCorrespondentTemporaryRole(){
		return $this->getColumnValue("correspondentTemporaryRole");
	}

	public function setCorrespondentTemporaryRole($role){
		return $this->setColumnValue("correspondentTemporaryRole", $role);
	}

	public function getCorrespondentKey():string{
		return $this->getColumnValue('correspondentKey');
	}

	public function hasCorrespondentNormalizedName():bool{
		return $this->hasColumnValue("correspondentNormalizedName");
	}

	public function getCorrespondentNormalizedName():string{
		return $this->getColumnValue("correspondentNormalizedName");
	}

	public function setCorrespondentNormalizedName(string $name):string{
		return $this->setColumnValue("correspondentNormalizedName", $name);
	}

	/**
	 *
	 * @param UserData $correspondent
	 * @return NULL|UserData
	 */
	public function setCorrespondentObject($correspondent){
		return $this->setForeignDataStructure("correspondentKey", $correspondent);
	}

	public function setCorrespondentName(string $cn):string{
		return $this->setColumnValue("correspondentName", $cn);
	}

	public function hasCorrespondentName():bool{
		return $this->hasColumnValue("correspondentName");
	}

	public function getCorrespondentName():string{
		return $this->getColumnValue("correspondentName");
	}

	public function getCorrespondentHardResetCount():int{
		return $this->getColumnValue("correspondentHardResetCount");
	}

	public function hasCorrespondentHardResetCount():bool{
		return $this->hasColumnValue("correspondentHardResetCount");
	}

	public function setCorrespondentHardResetCount(int $cmak):int{
		return $this->setColumnValue("correspondentHardResetCount", $cmak);
	}

	public function hasCorrespondentObject():bool{
		return $this->hasForeignDataStructure('correspondentKey');
	}

	/**
	 *
	 * @return UserData
	 */
	public function getCorrespondentObject():UserData{
		$f = __METHOD__;
		if(!$this->hasCorrespondentObject()) {
			$correspondentKey = $this->getCorrespondentKey();
			$key = $this->getIdentifierValue();
			Debug::error("{$f} correspondent object with key \"{$correspondentKey}\" is undefined for object with key \"{$key}\"");
		}
		return $this->getForeignDataStructure('correspondentKey');
	}

	public function acquireCorrespondentObject(mysqli $mysqli){
		$f = __METHOD__;
		if($this->hasCorrespondentObject()) {
			return $this->getCorrespondentObject();
		}
		$correspondent = $this->acquireForeignDataStructure($mysqli, 'correspondentKey');
		if($correspondent === null) {
			Debug::error("{$f} acquireForeignDataStructure returned null");
		}
		return $this->setCorrespondentObject($correspondent);
	}

	public function hasCorrespondentKey():bool{
		return $this->hasColumnValue("correspondentKey");
	}

	public function getCorrespondentAccountType():string{
		return $this->getColumnValue("correspondentAccountType");
	}

	public function setCorrespondentAccountType(string $type):string{
		return $this->setColumnValue('correspondentAccountType', $type);
	}

	public function getCorrespondentLanguagePreference():string{
		return $this->getCorrespondentObject()->getLanguagePreference();
	}

	public function setCorrespondentPublicKey(string $key):string{
		return $this->setColumnValue("correspondentPublicKey", $key);
	}

	public function getCorrespondentPublicKey():string{
		return $this->getColumnValue("correspondentPublicKey");
	}
	
	public function getCorrespondentSerialNumber():int{
		$f = __METHOD__;
		try{
			if(!$this->hasCorrespondentObject()) {
				Debug::error("{$f} correspondent object is undefined");
				$this->setObjectStatus(ERROR_NULL_CORRESPONDENT_OBJECT);
			}
			return $this->getCorrespondentObject()->getSerialNumber();
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
