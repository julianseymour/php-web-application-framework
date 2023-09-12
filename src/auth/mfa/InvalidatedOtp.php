<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class InvalidatedOtp extends UserFingerprint implements StaticTableNameInterface{

	use StaticTableNameTrait;
	
	public static function getTableNameStatic(): string{
		return "invalidated_otps";
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public function getName():string{
		return $this->getUserName();
	}

	public function getUserKey():string{
		$f = __METHOD__;
		if(!$this->hasUserData()) {
			Debug::error("{$f} client object is undefined");
		}
		return $this->getUserData()->getIdentifierValue();
	}

	public function setOTP(string $otp){
		return $this->setColumnValue('otp', $otp);
	}

	public static function getPermissionStatic(string $name, $data){
		$f = __METHOD__;
		$print = false;
		if($name === DIRECTIVE_INSERT && user() instanceof AnonymousUser) {
			if($print) {
				Debug::print("{$f} granting permission for insert (current user is unregistered)");
			}
			return SUCCESS;
		}
		return parent::getPermissionStatic($name, $data);
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::reconfigureColumns($columns, $ds);
			$fields = [
				// "userNormalizedName",
				'userAccountType',
				// 'userHardResetCount',
				// 'userMasterAccountKey',
				'userKey',
				'userName',
				'userTemporaryRole',
				'updatedTimestamp'
			];
			foreach($fields as $field) {
				$columns[$field]->volatilize();
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getOTP(){
		return $this->getColumnValue('otp');
	}

	public static function getPhylumName(): string{
		return "otps";
	}

	public static function getDataType(): string{
		return DATATYPE_USED_OTP;
	}

	public function preventDuplicateEntry(mysqli $mysqli): int{
		return SUCCESS;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$otp = new TextDatum("otp");
		$otp->setNullable(false);
		$otp->setSensitiveFlag(true);
		// $otp->setInitRequired(false);
		$otp->setHumanReadableName(_("One-time password"));
		array_push($columns, $otp);
	}

	public static function getPrettyClassName():string{
		return _("Used OTP");
	}

	public static function getPrettyClassNames():string{
		return _("Used OTPs");
	}

	public function insert(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			if(!$this->hasUserData()) {
				Debug::error("{$f} user data is undefined");
			}elseif(!$this->getUserData() instanceof AuthenticatedUser) {
				Debug::error("{$f} mfa is only for authenticated users");
			}
			return parent::insert($mysqli);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
