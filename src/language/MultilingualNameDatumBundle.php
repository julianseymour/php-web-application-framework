<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;

class MultilingualNameDatumBundle extends MultilingualStringDatumBundle{
	
	public function __construct(?string $name=null, ?DataStructure $ds=null){
		parent::__construct($name, $ds);
		$this->setHumanReadableName(_("Name"));
	}
	
	public static function getStringDatumClassStatic():string{
		return NameDatum::class;
	}
}
