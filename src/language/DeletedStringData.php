<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class DeletedStringData extends MultilingualStringData
{

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Deleted string");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Deleted strings");
	}

	public static function getPhylumName(): string
	{
		return "strings";
	}

	public function getStringIdentifier()
	{
		return "deleted";
	}

	public static function getDataType(): string
	{
		return DATATYPE_STRING;
	}

	public static function getTableNameStatic(): string
	{
		$f = __METHOD__; //DeletedStringData::getShortClass()."(".static::getShortClass().")::getTableNameStatic()";
		ErrorMessage::unimplemented($f);
	}

	public static function getStringTypeStatic(): string
	{
		return STRING_TYPE_DELETED;
	}
}
