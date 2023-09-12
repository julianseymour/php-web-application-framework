<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

trait ValidationTrait
{

	protected $validate;

	public function setValidation($validate)
	{
		if(!is_bool($validate)) {
			$validate = boolval($validate);
		}
		return $this->validate = $validate;
	}

	public function getValidation()
	{
		return $this->validate === true;
	}
}
