<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ElseBlockTrait
{

	use ArrayPropertyTrait;

	public function getElseCommands()
	{
		$f = __METHOD__; //"ElseBlockTrait(".static::getShortClass().")->getElseCommands()";
		if (! $this->hasElseCommands()) {
			Debug::error("{$f} else commands are undefined");
		}
		return $this->getProperty("else");
	}

	public function setElseCommands($else): array
	{
		if (! is_array($else)) {
			$else = [
				$else
			];
		}
		return $this->setArrayProperty("else", $else);
	}

	public function else(...$else): object
	{
		$f = __METHOD__; //"ElseBlockTrait(".static::getShortClass().")->else()";
		if (isset($else) && count($else) === 1 && is_array($else[0])) {
			if (! is_associative($else[0])) {
				Debug::error("{$f} this function only accepts non-associative arrays");
			}
			return $this->else(...$else[0]);
		}
		return $this->withProperty("else", $else);
	}

	public function hasElseCommands(): bool
	{
		return $this->hasArrayProperty("else");
	}

	public function pushElseCommands(...$else): int
	{
		return $this->pushArrayProperty("else", $else);
	}

	public function mergeElseCommands(?array $else): ?array
	{
		return $this->mergeArrayProperty("else", $else);
	}

	public function getElseCommandCount(): int
	{
		return $this->getArrayPropertyCount("else");
	}
}
