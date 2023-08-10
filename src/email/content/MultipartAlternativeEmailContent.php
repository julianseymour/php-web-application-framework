<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\content;

class MultipartAlternativeEmailContent extends MultipartEmailContent
{

	public static function getPrefix()
	{
		return "nq";
	}

	public static function getMultipartType()
	{
		return "alternative";
	}
}
