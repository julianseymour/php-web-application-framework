<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;

class SetColumnDefault extends AlterColumnOption
{

	protected $default;

	public function __construct($columnName, $default)
	{
		parent::__construct($columnName);
		$this->setDefault($default);
	}

	public function setDefault($default)
	{
		return $this->default = $default;
	}

	public function hasDefault()
	{
		return isset($this->default) && $this->default !== null;
	}

	public function getDefault()
	{
		if($this->hasDefault()) {
			if(is_string($this->default)) {
				return escape_quotes($this->default, QUOTE_STYLE_SINGLE);
			}
			return $this->default;
		}
		return null;
	}

	public function toSQL(): string
	{
		return parent::toSQL() . "set default " . $this->getDefault();
	}
}
