<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;

class AdminAuthenticationData extends FullAuthenticationData{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$full = new BooleanDatum("full_admin_login");
		$admintime = new BooleanDatum("admintime");
		parent::declareColumns($columns, $ds);
		array_push($columns, $full, $admintime);
	}

	public function getAdminSessionFlag(){
		return $this->getColumnValue("admintime");
	}

	public function setAdminSessionFlag($value){
		return $this->setColumnValue("admintime", $value);
	}

	public function ejectAdminSessionFlag(){
		return $this->ejectColumnValue("admintime");
	}

	public function handSessionToUser(PlayableUser $user, ?int $mode = null):PlayableUser{
		$f = __METHOD__;
		$print = false;
		$ret = parent::handSessionToUser($user, $mode);
		if(! user() instanceof Administrator) {
			Debug::error("{$f} current user should be administrator by now");
		}elseif($print) {
			Debug::print("{$f} OK");
		}
		return $ret;
	}
}
