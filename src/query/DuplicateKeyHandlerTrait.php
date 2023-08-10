<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DuplicateKeyHandlerTrait
{

	protected $duplicateKeyHandler;

	public function hasDuplicateKeyHandler()
	{
		return isset($this->duplicateKeyHandler);
	}

	public function getDuplicateKeyHandler()
	{
		$f = __METHOD__; //"DuplicateKeyHandlerTrait(".static::getShortClass().")->getDuplicateKeyHandler()";
		if (! $this->hasDuplicateKeyHandler()) {
			Debug::error("{$f} duplicate key handler is undefined");
		}
		return $this->duplicateKeyHandler;
	}

	public function setDuplicateKeyHandler($s)
	{
		$f = __METHOD__; //"DuplicateKeyHandlerTrait(".static::getShortClass().")->setDuplicateKeyHandler()";
		if ($s == null) {
			unset($this->duplicateKeyHandler);
			return null;
		} elseif (! is_string($s)) {
			Debug::error("{$f} duplicate key handler must be a string");
		}
		$s = strtolower($s);
		switch ($s) {
			case DIRECTIVE_IGNORE:
			case DIRECTIVE_REPLACE:
				return $this->duplicateKeyHandler = $s;
			default:
				Debug::error("{$f} invalid duplicate key handler \"{$s}\"");
		}
	}

	public function ignore()
	{
		$this->setDuplicateKeyHandler(DIRECTIVE_IGNORE);
		return $this;
	}

	public function replace()
	{
		$this->setDuplicateKeyHandler(DIRECTIVE_REPLACE);
		return $this;
	}
}