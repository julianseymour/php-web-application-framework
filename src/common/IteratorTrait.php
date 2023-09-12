<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IteratorTrait
{

	public abstract function getDeclarationLine();

	protected $iterator;

	public function hasIterator()
	{
		return isset($this->iterator) && $this->iterator !== null;
	}

	public function getIterator()
	{
		$f = __METHOD__; //"IteratorTrait(".static::getShortClass().")->getIterator()";
		if(!$this->hasIterator()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} iterator is undefined. This object was declared {$decl}");
		}
		return $this->iterator;
	}

	public function setIterator($i)
	{
		$f = __METHOD__; //"IteratorTrait(".static::getShortClass().")->setiterator()";
		if($i === null) {
			unset($this->iterator);
			return null;
		}
		return $this->iterator = $i;
	}
}
