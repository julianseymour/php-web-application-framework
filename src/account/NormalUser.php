<?php
namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\IntegerEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use Exception;

class NormalUser extends AuthenticatedUser{

	public static function getAccountTypeStatic(){
		return ACCOUNT_TYPE_USER;
	}

	public function getActivationTimestampString(){
		return getDateTimeStringFromTimestamp($this->getActivationTimestamp());
	}

	public static function getFullAuthenticationDataClass(){
		return FullAuthenticationData::class;
	}

	public function hasActivationTimestamp(){
		return $this->hasColumnValue("activationTimestamp");
	}

	public function getActivationTimestamp(){
		return $this->getColumnValue("activationTimestamp");
	}

	public function setActivationTimestamp($ts){
		return $this->setColumnValue("activationTimestamp", $ts);
	}

	public function isAccountActivated(){
		return $this->hasActivationTimestamp();
	}

	public function getMessagePermission(){
		return $this->getColumnValue('canMessage');
	}

	public function setMessagePermission($v){
		return $this->setColumnValue('canMessage', $v);
	}

	public function getStaticRoles(): ?array{
		$roles = parent::getStaticRoles();
		if ($this->hasActivationTimestamp()) {
			$roles["active"] = 'active';
		} else {
			$roles['inactive'] = 'inactive';
		}
		return $roles;
	}

	/**
	 *
	 * @return Administrator
	 */
	public static function getAdministratorClass():string{
		return config()->getAdministratorClass();
	}

	public function getKycStatus(){
		return $this->getColumnValue('kycStatus');
	}

	public function loadFailureHook(): int{
		$this->setObjectStatus(ERROR_NOT_FOUND);
		return SUCCESS;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::declareColumns($columns, $ds);
			$active_t = new TimestampDatum("activationTimestamp");
			$active_t->setUserWritableFlag(true);
			$active_t->setDefaultValue(null);
			$kyc = new IntegerEnumeratedDatum("kycStatus", 8);
			$kyc->setDefaultValue(KYC_INITIAL);
			$kyc->setUserWritableFlag(true);
			$map = [
				KYC_INITIAL,
				KYC_UNVERIFIED,
				KYC_SUBMITTED,
				KYC_REJECTED,
				KYC_REVISE,
				KYC_ACCEPTED,
				KYC_REVOKED
			];
			$kyc->setValidEnumerationMap($map);
			static::pushTemporaryColumnsStatic($columns, $kyc, $active_t);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getTableNameStatic(): string{
		return "users";
	}

	public function getAccountType():string{
		return static::getAccountTypeStatic();
		$f = __METHOD__;
		$type = $this->getColumnValue("accountType");
		if (! is_string($type)) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} invalid non-string account type \"{$type}\"; this object was created on {$decl}");
		}
		return $this->getColumnValue("accountType");
	}

	public static function getPrettyClassName(?string $lang = null):string{
		return _("User");
	}

	public static function getPrettyClassNames(?string $lang = null):string{
		return _("Users");
	}

	public static function checkUserExists(mysqli $mysqli, string $key):bool{
		$f = __METHOD__; 
		$user = static::getObjectFromKey($mysqli, $key);
		if ($user === null || $user->getObjectStats() !== SUCCESS) {
			Debug::warning("{$f} user with key {$key} doesn't exist");
			return false;
		} else {
			// Debug::print("{$f} user with key DOES exist; his name is ".$user->getName());
			return true;
		}
	}
}
