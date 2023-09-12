<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Routine;
use JulianSeymour\PHPWebApplicationFramework\common\DelimiterTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReturnTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\user\DefinerTrait;
use Exception;

class CreateRoutineStatement extends RoutineStatement implements StaticPropertyTypeInterface{

	use DefinerTrait;
	use DelimiterTrait;
	use ReturnTypeTrait;
	use StaticPropertyTypeTrait;

	protected $routine;

	public function __construct(?Routine $routine = null){
		parent::__construct();
		if($routine !== null) {
			$this->setRoutine($routine);
		}
	}

	public function getDelimiter(): string{
		if(!$this->hasDelimiter()) {
			return "//";
		}
		return $this->delimiter;
	}

	public function hasRoutine(): bool{
		return isset($this->routine);
	}

	public function setRoutine(?Routine $routine): ?Routine{
		if($routine == null) {
			unset($this->routine);
			return null;
		}elseif($routine instanceof Routine) {
			$this->setName($routine->getName());
			if($routine->hasParameters()) {
				$this->setParameters($routine->getParameters());
			}
			if($routine->hasReturnType()) {
				$this->setReturnType($routine->getReturnType());
			}
		}
		return $this->routine = $routine;
	}

	public function getRoutine(){
		$f = __METHOD__;
		if(!$this->hasRoutine()) {
			Debug::error("{$f} routine body is undefined");
		}
		return $this->routine;
	}

	protected function getCharacteristics(): string{
		$string = parent::getCharacteristics();
		// [NOT] DETERMINISTIC
		if(!$this->getDeterministicFlag()) {
			$string .= "not ";
		}
		$string .= "deterministic ";
		return $string;
	}

	public function getQueryStatementString(){
		$f = __METHOD__;
		try{
			$print = false;
			$string = "";
			// $string .= "delimiter ".$this->getDelimiter()."\n";
			// CREATE
			$string .= "create ";
			// [DEFINER = user]
			if($this->hasDefiner()) {
				$definer = $this->getDefiner();
				if($definer instanceof SQLInterface) {
					$definer = $definer->toSQL();
				}
				$string .= "{$definer} ";
			}
			// {FUNCTION | PROCEDURE} sp_name ([parameter[,...]])
			if($this->hasReturnType()) {
				$string .= "function ";
			}else{
				$string .= "procedure ";
			}
			$string .= $this->getName() . " (";
			if($this->hasParameters()) {
				$i = 0;
				foreach($this->getParameters() as $p) {
					if($i ++ > 0) {
						$string .= ",";
					}
					if($p instanceof SQLInterface) {
						$p = $p->toSQL();
					}
					$string .= $p;
				}
			}
			$string .= ") ";
			// RETURNS type
			if($this->hasReturnType()) {
				$string .= "returns " . $this->getReturnType() . " ";
			}elseif($print) {
				Debug::print("{$f} return type is undefined");
			}
			$string .= $this->getCharacteristics();
			// routine_body
			$routine = $this->getRoutine();
			if($routine instanceof SQLInterface) {
				$routine = $routine->toSQL();
			}
			$string .= $routine;
			// $string .= $this->getDelimiter();
			if($print) {
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->delimiter);
		unset($this->routine);
		unset($this->sqlSecurityType);
		unset($this->userDefiner);
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"parameters" => RoutineParameter::class
		];
	}
}
