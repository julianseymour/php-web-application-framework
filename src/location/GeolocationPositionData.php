<?php

namespace JulianSeymour\PHPWebApplicationFramework\location;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DoubleDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;

class GeolocationPositionData extends UserOwned implements TemplateContextInterface{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$accuracy = new DoubleDatum("accuracy");
		$altitude = new DoubleDatum("altitude");
		$alt_accuracy = new DoubleDatum("altitudeAccuracy");
		$heading = new DoubleDatum("heading");
		$latitude = new DoubleDatum("latitude");
		$longitude = new DoubleDatum("longitude");
		$speed = new DoubleDatum("speed");
		$timestamp = new TimestampDatum("timestamp");
		static::pushTemporaryColumnsStatic(
			$columns, 
			$accuracy, 
			$altitude, 
			$alt_accuracy, 
			$heading, 
			$latitude, 
			$longitude, 
			$speed, 
			$timestamp
		);
	}

	public static function getPrettyClassName():string{
		return _("Geolocation position");
	}

	public static function getTableNameStatic(): string{
		return "geolocation_positions";
	}

	public static function getDataType(): string{
		return DATATYPE_GEOLOCATION_POSITION;
	}

	public static function getPrettyClassNames():string{
		return _("Geolocation positions");
	}

	public static function getPhylumName(): string{
		return "geolocationPositions";
	}

	public function template(){
		return;
	}
	
	public static function getDefaultPersistenceModeStatic():int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
}
