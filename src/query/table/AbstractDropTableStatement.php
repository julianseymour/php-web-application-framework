<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

abstract class AbstractDropTableStatement extends QueryStatement
{

	use IfExistsFlagBearingTrait;
	use MultipleTableNamesTrait;

	protected $referenceOption;

	public function setReferenceOption($refopt)
	{
		$f = __METHOD__; //AbstractDropTableStatement::getShortClass()."(".static::getShortClass().")->setReferenceOption()";
		if($refopt == null) {
			unset($this->referenceOption);
			return null;
		}elseif(!is_string($refopt)) {
			Debug::error("{$f} reference option must be a string");
		}
		$refopt = strtolower($refopt);
		switch ($refopt) {
			case REFERENCE_OPTION_CASCADE:
			case REFERENCE_OPTION_RESTRICT:
				break;
			default:
				Debug::error("{$f} invalid reference option \"{$refopt}\"");
				return $this->setReferenceOption(null);
		}
		return $this->referenceOption = $refopt;
	}

	public function hasReferenceOption()
	{
		return isset($this->referenceOption);
	}

	public function getReferenceOption()
	{
		$f = __METHOD__; //AbstractDropTableStatement::getShortClass()."(".static::getShortClass().")->getReferenceOption()";
		if(!$this->hasReferenceOption()) {
			Debug::error("{$f} reference option is undefined");
		}
		return $this->referenceOption;
	}

	public function restrict()
	{
		$this->setReferenceOption(REFERENCE_OPTION_RESTRICT);
		return $this;
	}

	public function cascade()
	{
		$this->setReferenceOption(REFERENCE_OPTION_CASCADE);
		return $this;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->referenceOption);
	}
}
