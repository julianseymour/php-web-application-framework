<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ParentNodeTrait
{

	protected $parentNode;

	public function hasParentNode(): bool
	{
		return isset($this->parentNode);
	}

	/**
	 *
	 * @return object
	 */
	public function getParentNode()
	{
		$f = __METHOD__; //"ParentNodeTrait(".static::getShortClass().")->getParentNode()";
		if (! $this->hasParentNode()) {
			Debug::error("{$f} parent node is undefined");
		}
		return $this->parentNode;
	}

	public function setParentNode($node)
	{
		if ($node === null) {
			unset($this->parentNode);
			return null;
		}
		return $this->parentNode = $node;
	}
}