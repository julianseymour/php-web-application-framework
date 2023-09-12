<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\TypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class RoutineParameter extends Basic implements SQLInterface
{

	use NamedTrait;
	use TypeTrait;

	public function __construct($name, $type)
	{
		parent::__construct();
		if($name !== null) {
			$this->setName($name);
		}
		if($type !== null) {
			$this->setType($type);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"in",
			"out"
		]);
	}

	public function setInFlag(bool $value = true): bool
	{
		return $this->setFlag("in", $value);
	}

	public function getInFlag(): bool
	{
		return $this->getFlag("in");
	}

	public function in(): RoutineParameter
	{
		$this->setInFlag(true);
		return $this;
	}

	public function setOutFlag(bool $value = true): bool
	{
		return $this->setFlag("out", $value);
	}

	public function getOutFlag(): bool
	{
		return $this->getFlag("out");
	}

	public function out(): RoutineParameter
	{
		$this->setOutFlag(true);
		return $this;
	}

	public function inOut(): RoutineParameter
	{
		return $this->in()->out();
	}

	public function toSQL(): string
	{
		$string = "";
		if(
		// $this->getRoutine()->getRoutineType() === ROUTINE_TYPE_PROCEDURE && (
		$this->getInFlag() || $this->getOutFlag()) 
		// )
		{
			if($this->getInFlag()) {
				$string .= "in";
			}
			if($this->getOutFlag()) {
				$string .= "out";
			}
			$string .= " ";
		}
		$string .= $this->getName() . " " . $this->getType();
		return $string;
	}
}
