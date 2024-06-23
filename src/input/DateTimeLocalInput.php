<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;

class DateTimeLocalInput extends DateInput implements StaticValueNegotiationInterface{

	public function getTypeAttribute(): string{
		return "datetime-local";
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_DATETIME_LOCAL;
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}

	public static function getHumanReadableValue($timestamp, $timezone){
		return getDateTimeStringFromTimestamp($timestamp, $timezone);
	}
}
