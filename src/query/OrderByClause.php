<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class OrderByClause extends Basic implements SQLInterface
{

	use ColumnNameTrait;
	use DirectionalityTrait;

	public function __construct($column_name, $directionality = DIRECTION_ASCENDING)
	{
		parent::__construct();
		$this->setColumnName($column_name);
		$this->setDirectionality($directionality);
	}

	public function toSQL(): string
	{
		return $this->getColumnName() . " " . $this->getDirectionality();
	}
}
