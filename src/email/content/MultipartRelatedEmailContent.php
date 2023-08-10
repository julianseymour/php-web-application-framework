<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\content;

class MultipartRelatedEmailContent extends MultipartEmailContent
{

	public static function getMultipartType()
	{
		return "related";
	}

	public static function getPrefix()
	{
		return "nr";
	}
}
