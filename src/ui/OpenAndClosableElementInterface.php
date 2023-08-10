<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

interface OpenAndClosableElementInterface
{

	public static function getClosedDisplayProperties(): ?array;

	public static function getOpenDisplayProperties(): ?array;
}

