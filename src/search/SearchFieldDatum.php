<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class SearchFieldDatum extends BooleanDatum{

	use ColumnNameTrait;
	use SearchClassTrait;

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->searchClass, $deallocate);
	}
}
