<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\routine\CreateRoutineStatement;

trait ReturnTypeTrait
{

	/**
	 * needed in AjaxForm->subindexNestedInputs() to distinguish VirtualDatum standing in for foreign key datums
	 *
	 * @var string
	 */
	protected $returnType;

	public function hasReturnType(): bool
	{
		return isset($this->returnType);
	}

	public function getReturnType(): string
	{
		return $this->hasReturnType() ? $this->returnType : TYPE_UNKNOWN;
		$f = __METHOD__; //CreateRoutineStatement::getShortClass()."(".static::getShortClass().")->getReturnType()";
		if(!$this->hasReturnType()) {
			Debug::error("{$f} return type is undefined");
		}
		return $this->returnType;
	}

	public function setReturnType(?string $type): ?string
	{
		$f = __METHOD__; //CreateRoutineStatement::getShortClass()."(".static::getShortClass().")->setReturnType()";
		if($type === null) {
			unset($this->returnType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} return type name must be a string");
		}
		$type = strtolower($type);
		// XXX confirm it's a valid datatype
		return $this->returnType = $type;
		switch ($type) {
			case TYPE_ARRAY:
				return $this->returnType = $type;
			default:
				Debug::error("{$f} invalid return type \"{$type}\"");
		}
	}
}
