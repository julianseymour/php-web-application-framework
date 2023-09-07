<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\StaticHumanReadableNameInterface;

class NameDatum extends TextDatum implements StaticHumanReadableNameInterface{

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"translatable"
		]);
	}

	public static function normalize(string $name): string{
		return preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
	}

	public static function getHumanReadableNameStatic(?StaticHumanReadableNameInterface $that = null){
		return _("Name");
	}
}
