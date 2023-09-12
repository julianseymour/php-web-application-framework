<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TypeSpecificTrait
{

	protected $typeSpecifier;

	public function setTypeSpecifier(?string $typedef): ?string
	{
		$f = __METHOD__; //"TypeSpecificTrait(".static::getShortClass().")->setTypeDefinitionString()";
		if($typedef == null) {
			unset($this->typeSpecifier);
			return null;
			// }elseif(!is_string($typedef)){
			// Debug::error("{$f} type definition string must be a string");
		}elseif(! preg_match('/[dis]+/', $typedef)) {
			Debug::error("{$f} invalid type definition string \"{$typedef}\"");
		}
		return $this->typeSpecifier = $typedef;
	}

	public function hasTypeSpecifier()
	{
		return isset($this->typeSpecifier) && is_string($this->typeSpecifier) && ! empty($this->typeSpecifier);
	}

	public function getTypeSpecifier()
	{
		$f = __METHOD__; //"TypeSpecificTrait(".static::getShortClass().")->getTypeSpecifier()";
		if(!$this->hasTypeSpecifier()) {
			Debug::error("{$f} type definition string is undefined");
		}
		return $this->typeSpecifier;
	}

	public function withTypeSpecifier(?string $ts)
	{
		$this->setTypeSpecifier($ts);
		return $this;
	}

	public function appendTypeSpecifier(string $s)
	{
		return $this->setTypeSpecifier($this->getTypeSpecifier() . $s);
	}
}
