<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\content;

class HTMLEmailContent extends PlaintextEmailContent
{

	public static function getTextType(): string
	{
		return "html";
	}
}
