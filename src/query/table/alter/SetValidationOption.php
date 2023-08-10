<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

class SetValidationOption extends AlterOption
{

	use ValidationTrait;

	public function __construct($validate)
	{
		parent::__construct();
		$this->setValidation($validate);
	}

	public function toSQL(): string
	{
		return "with" . (! $this->getValidation() ? "out" : "") . " validation";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->validate);
	}
}