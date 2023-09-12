<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class CheckboxValidator extends Validator
{

	use NamedTrait;

	public function __construct($name = null)
	{
		parent::__construct();
		if(!empty($name)) {
			$this->setName($name);
		}
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //CheckboxValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$checked = $validate_me[$this->getName()];
		if($checked === "on") {
			if($print) {
				Debug::print("{$f} box is checked");
			}
			return SUCCESS;
		}
		Debug::warning("{$f} box is not checked");
		return $this->getSpecialFailureStatus();
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->name);
	}
}
