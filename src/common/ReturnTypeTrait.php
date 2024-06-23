<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ReturnTypeTrait{

	/**
	 * needed in AjaxForm->subindexNestedInputs() to distinguish VirtualDatum standing in for foreign key datums
	 *
	 * @var string
	 */
	protected $returnType;

	public function hasReturnType(): bool{
		return isset($this->returnType);
	}

	public function getReturnType(): string{
		return $this->hasReturnType() ? $this->returnType : TYPE_UNKNOWN;
		$f = __METHOD__;
		if(!$this->hasReturnType()){
			Debug::error("{$f} return type is undefined");
		}
		return $this->returnType;
	}

	public function setReturnType(?string $type): ?string{
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} return type name must be a string");
		}elseif($this->hasReturnType()){
			$this->release($this->returnType);
		}
		//$type = strtolower($type);
		// XXX TODO confirm it's a valid datatype
		return $this->returnType = $this->claim($type);
	}
}
