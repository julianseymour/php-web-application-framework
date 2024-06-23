<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\control\ElseBlockTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

/**
 * class for building SQL case statements and case functions
 *
 * @author j
 */
class CaseStatement extends Command implements SQLInterface{

	use ElseBlockTrait;

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"endCase"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"endCase"
		]);
	}
	
	public function setEndCaseFlag(bool $value = true): bool{
		return $this->setFlag("endCase", $value);
	}

	public function getEndCaseFlag(): bool{
		return $this->getFlag("endCase");
	}

	public function endCase(bool $value = true): CaseStatement{
		return $this->withFlag("endCase", $value);
	}

	protected function pushWhenCondition($condition): int{
		return $this->pushArrayProperty("when", $condition);
	}

	public function hasWhenConditions(): bool{
		return $this->hasArrayProperty("when");
	}

	public function getWhenConditions(): ?array{
		$f = __METHOD__;
		if(!$this->hasWhenConditions()){
			Debug::error("{$f} when conditions are undefined");
		}
		return $this->getProperty("when");
	}

	protected function pushThenStatements(array $then): int{
		return $this->pushArrayProperty("then", $then);
	}

	public function hasThenStatements(?int $i = null): bool{
		if($i === null){
			return $this->hasArrayProperty("then");
		}
		return $this->hasArrayPropertyKey("when", $i);
	}

	public function getThenStatements(?int $i = null): array{
		$f = __METHOD__;
		if(!$this->hasThenStatements()){
			Debug::error("{$f} then statements do not exist");
		}elseif($i === null){
			return $this->getProperty("then");
		}elseif(!$this->hasArrayPropertyKey("then", $i)){
			Debug::error("{$f} then command at offset \"{$i}\" does not exist");
		}
		return $this->getArrayPropertyValueAtOffset("then", $i);
	}

	public function when($condition, ...$then): CaseStatement{
		$this->pushWhenCondition($condition);
		$this->pushThenStatements($then);
		return $this;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			$expr = $this->getExpression();
			if($expr instanceof SQLInterface){
				$expr = $expr->toSQL();
			}
			$string = "case";
			if($this->hasExpression()){
				$expr = $this->getExpression();
				if($expr instanceof SQLInterface){
					$expr = $expr->toSQL();
				}
				$string .= " {$expr}";
			}
			foreach($this->getWhenConditions() as $i => $when){
				if($when instanceof SQLInterface){
					$escaped = $when->toSQL();
				}elseif(is_string($when) || $when instanceof StringifiableInterface){
					$escaped = single_quote($when);
				}else{
					$escaped = $when;
				}
				$string .= "\twhen {$escaped} then\n";
				foreach($this->getThenStatements($i) as $then){
					$string .= "\t\t" . $then->toSQL() . "\n";
				}
			}
			if($this->hasElseCommands()){
				$string .= "\else\n";
				foreach($this->getElseCommands() as $else){
					if($else instanceof SQLInterface){
						$else = $else->toSQL();
					}elseif(is_string($else) || $else instanceof StringifiableInterface){
						$else = single_quote($else);
					}
					$string .= "\t\t{$else}\n";
				}
			}
			$string .= "end";
			if($this->getEndCaseFlag()){
				$string .= " case";
			}
			$string .= "\n";
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getCommandId(): string{
		return "case";
	}
}
