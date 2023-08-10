<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\content;

class MultipartMixedEmailContent extends MultipartEmailContent
{

	public static function getMultipartType()
	{
		return "mixed";
	}

	public static function getPrefix()
	{
		return "np";
	}
}
