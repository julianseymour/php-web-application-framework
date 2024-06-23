<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\shadow;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\MessageEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use mysqli;

class ShadowUser extends UserData{

	public function getHardResetCount(): int{
		return 0;
	}

	public function getHasEverAuthenticated(): bool{
		return false;
	}

	public static function getSubtypeStatic():string{
		return ACCOUNT_TYPE_SHADOW;
	}

	public function getProfileImageData(){
		return null;
	}

	public static function getPrettyClassName():string{
		return _("Shadow profile");
	}

	public function filterIpAddress(mysqli $mysqli, ?string $ip_address = null, bool $skip_insert = false): int{
		return SUCCESS;
	}

	public static function getTableNameStatic(): string{
		return "shadow_profiles";
	}

	public static function getPrettyClassNames():string{
		return _("Shadow profiles");
	}

	public function getAccountType():string{
		return static::getSubtypeStatic();
	}

	public function getFirstName():string{
		return $this->getColumnValue("firstName");
	}

	public function setFirstName(string $value):string{
		return $this->setColumnValue("firstName", $value);
	}

	public function hasFirstName():bool{
		return $this->hasColumnValue("firstName");
	}

	public function getLastName():string{
		return $this->getColumnValue("lastName");
	}

	public function setLastName(string $value):string{
		return $this->setColumnValue("lastName", $value);
	}

	public function hasLastName():bool{
		return $this->hasColumnValue("lastName");
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null):void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$first = new NameDatum("firstName");
		$first->setHumanReadableName(_("First name"));
		$last = new NameDatum("lastName");
		$last->setHumanReadableName(_("Last name"));
		$last->setNullable(true);
		$last->setDefaultValue(null);
		$full = new VirtualDatum("fullName");
		$email = new EmailAddressDatum("emailAddress");
		$email->setNullable(true);
		$email->setDefaultValue(null);
		$email->setEncryptionScheme(MessageEncryptionScheme::class);
		$email->setHumanReadableName(_("Email address"));
		$name = new VirtualDatum("name");
		array_push($columns, $first, $last, $full, $email, $name);
	}

	public function getFullName():string{
		$first = $this->getFirstName();
		if(!$this->hasLastName()){
			return $first;
		}
		$session = new LanguageSettingsData();
		$lang = $session->getLanguageCode();
		deallocate($session);
		$last = $this->getLastName();
		return Internationalization::lastNameFirst($lang) ? "{$last} {$first}" : "{$first} {$last}";
	}

	public function getVirtualColumnValue(string $column_name){
		switch($column_name){
			case "fullName":
			case "name":
				return $this->getFullName();
			case "normalizedName":
				return NameDatum::normalize($this->getFullName());
			default:
				return parent::getVirtualColumnValue($column_name);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch($column_name){
			case "fullName":
			case "name":
			case "normalizedName":
				return $this->hasFirstName() || $this->hasLastName();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}
	
	public static function reconfigureColumns(array &$columns, ?DataStructure $ds=null):void{
		parent::reconfigureColumns($columns, $ds);
		$columns['regionCode']->setNullable(true);
	}
}
