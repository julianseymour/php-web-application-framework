<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;

trait JavaScriptCounterpartTrait
{

	public static function getJavaScriptClassPath(): ?string
	{
		$fn = get_class_filename(static::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public static function getJavaScriptClassIdentifier(): string
	{
		return get_short_class(static::class);
	}
}

