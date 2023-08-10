<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

interface JavaScriptCounterpartInterface
{

	public static function getJavaScriptClassPath(): ?string;

	public static function getJavaScriptClassIdentifier(): string;
}

