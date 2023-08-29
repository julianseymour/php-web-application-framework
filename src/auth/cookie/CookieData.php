<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\cookie;

use JulianSeymour\PHPWebApplicationFramework\data\SuperGlobalData;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

abstract class CookieData extends SuperGlobalData{

	public static function getProgramStorageStatic(){
		return PROGRAM_STORAGE_COOKIE;
	}

	public static function getPrettyClassName():string{
		return _("Cookie");
	}

	public static function getPrettyClassNames():string{
		return _("Cookies");
	}

	protected function beforeDeleteHook($mysqli = null){
		foreach ($this->getColumns() as $datum) {
			$datum->setValue(null);
		}
		return SUCCESS;
	}

	public static function getPhylumName(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
}
