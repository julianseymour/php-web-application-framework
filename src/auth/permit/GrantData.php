<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use JulianSeymour\PHPWebApplicationFramework\account\correspondent\CorrespondentKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubjectiveTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class GrantData extends DataStructure implements StaticTableNameInterface{

	use AccessControlListKeyColumnTrait;
	use CorrespondentKeyColumnTrait;
	use StaticTableNameTrait;
	use SubjectiveTrait;

	public static function getDatabaseNameStatic():string{
		return "security";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		// example scenario: alice (subject) has edit+delete (acl) privileges on bob's (correspondent) posts in channel X (scope) until tomorrow (expiration)
		// subject, aka what user/group gets priviileges granted/revoked. for example
		$subject_key = new ForeignMetadataBundle("subject", $ds);
		$subject_key->setForeignDataStructureClassResolver(GrantSubjectClassResolver::class);
		$subject_key->constrain();
		// scope, aka in what context (group/channel) is the grant considered
		$scope_key = new ForeignMetadataBundle("scope", $ds);
		$scope_key->setForeignDataStructureClassResolver(GrantScopeClassResolver::class);
		if(static::getScopeNullability()){
			$scope_key->setNullable(true);
		}
		$scope_key->constrain();
		// acl, which lists the individual rules on a per-permission basis
		$acl_key = new ForeignKeyDatum("accessControlList");
		$acl_key->setForeignDataStructureClass(AccessControlListData::class);
		$acl_key->constrain();
		// correspondent, aka who (third party user/group) will be affected by the grant should the recipient choose to act on them
		$correspondent = new ForeignMetadataBundle("correspondent", $ds);
		$correspondent->setForeignDataStructureClassResolves(GrantSubjectClassResolver::class);
		$correspondent->setNullable(true);
		$correspondent->constrain();
		// expiration
		$expiration = new TimestampDatum("expirationTimestamp");
		$expiration->setNullable(true);
		array_push($columns, $subject_key, $scope_key, $acl_key, $correspondent, $expiration);
	}

	public function setScopeData(?DataStructure $struct): ?DataStructure{
		return $this->setForeignDataStructure("scopeKey", $struct);
	}

	public function hasScopeData(): bool{
		return $this->hasForeignDataStructure("scopeKey");
	}

	public function getScopeData(): ?DataStructure{
		return $this->getForeignDataStructure("scopeKey");
	}

	public function setScopeKey(?string $value): ?string{
		return $this->getColumnValue("scopeKey");
	}

	public function hasScopeKey(): bool{
		return $this->hasColumnValue("scopeKey");
	}

	public function getScopeKey(): ?string{
		return $this->getColumnValue("scopeKey");
	}

	protected static function getScopeNullability(): bool{
		return false;
	}

	public static function getPrettyClassName():string{
		return _("Grant");
	}

	public static function getTableNameStatic(): string{
		return "grants";
	}

	public static function getDataType(): string{
		return DATATYPE_GRANT;
	}

	public static function getPrettyClassNames():string{
		return _("Grants");
	}

	public static function getPhylumName(): string{
		return "grants";
	}

	public function getExpirationTimestamp(){
		return $this->getColumnValue("expirationTimestamp");
	}

	public function hasExpirationTimestamp(){
		return $this->hasColumnValue("expirationTimestamp");
	}

	public function setExpirationTimestamp($value){
		return $this->setColumnValue("expirationTimestamp", $value);
	}

	public function ejectExpirationTimestamp(){
		return $this->ejectColumnValue("expirationTimestamp");
	}
}
