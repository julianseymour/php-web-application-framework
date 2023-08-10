<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris;

use JulianSeymour\PHPWebApplicationFramework\security\AccessControlPanelElement;
use JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris\NonexistentUriData;
use JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris\NonexistentUriForm;

class NonexistentUriListElement extends AccessControlPanelElement{
	
	public static function getFormClass(): string{
		return NonexistentUriForm::class;
	}

	public static function allowNewEntry(): bool{
		return false;
	}

	public static function getDataStructureClass(): string{
		return NonexistentUriData::class;
	}
}

