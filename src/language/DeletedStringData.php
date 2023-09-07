<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

class DeletedStringData extends MultilingualStringData{

	public static function getPrettyClassName():string{
		return _("Deleted string");
	}

	public static function getPrettyClassNames():string{
		return _("Deleted strings");
	}

	public function getStringIdentifier(){
		return "deleted";
	}

	public static function getDataType(): string{
		return DATATYPE_STRING_MULTILINGUAL;
	}

	public static function getStringTypeStatic(): string{
		return STRING_TYPE_DELETED;
	}
}
